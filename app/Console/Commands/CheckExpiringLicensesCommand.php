<?php

namespace App\Console\Commands;

use App\Enums\LicenseApplicationStatus;
use App\Enums\RoleName;
use App\Models\LicenseApplication;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\LicenseExpiringNotification;
use App\Services\LicenseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckExpiringLicensesCommand extends Command
{
    protected $signature = 'ksmct:check-expiring-licenses';

    protected $description = 'Mark expired hotel licenses and send 90/60/30-day renewal reminder notifications.';

    public function handle(LicenseService $licenseService): int
    {
        // 1. Mark any approved licenses past their expiry date as EXPIRED
        $expiredApps = LicenseApplication::query()
            ->with('hotel')
            ->where('status', LicenseApplicationStatus::APPROVED->value)
            ->whereDate('expires_at', '<', today())
            ->get();

        foreach ($expiredApps as $app) {
            $licenseService->markExpired($app);
        }

        // 2. Check upcoming expirations against configured thresholds (default: 90, 60, 30 days)
        $thresholds = SystemSetting::getValue('license_expiry_reminder_days', [90, 60, 30]);
        rsort($thresholds);

        $recipients = User::query()
            ->where('is_active', true)
            ->role([
                RoleName::DEPT_HEAD_HOTELS->value,
                RoleName::LICENSING_OFFICER->value,
                RoleName::DIRECTOR->value,
            ])
            ->get();

        $notificationsSent = 0;

        $activeApproved = LicenseApplication::query()
            ->with('hotel')
            ->where('status', LicenseApplicationStatus::APPROVED->value)
            ->whereNotNull('expires_at')
            ->whereDate('expires_at', '>=', today())
            ->whereDate('expires_at', '<=', today()->addDays((int) max($thresholds)))
            ->get();

        foreach ($activeApproved as $app) {
            $daysLeft = (int) today()->diffInDays($app->expires_at, false);

            // Match the tightest threshold the license falls within (e.g. 25 days -> 30-day threshold)
            $matchedThreshold = null;
            foreach ($thresholds as $t) {
                if ($daysLeft <= (int) $t) {
                    $matchedThreshold = (int) $t;
                }
            }

            if ($matchedThreshold === null) {
                continue;
            }

            foreach ($recipients as $recipient) {
                $alreadySent = DB::table('notifications')
                    ->where('notifiable_type', User::class)
                    ->where('notifiable_id', $recipient->id)
                    ->where('type', LicenseExpiringNotification::class)
                    ->where('data', 'like', '%"license_application_id":'.$app->id.'%')
                    ->where('data', 'like', '%"threshold_days":'.$matchedThreshold.'%')
                    ->exists();

                if (! $alreadySent) {
                    $recipient->notify(new LicenseExpiringNotification($app, $matchedThreshold));
                    $notificationsSent++;
                }
            }
        }

        $this->info("Expired marked: {$expiredApps->count()} | Renewal reminders sent: {$notificationsSent}");

        return self::SUCCESS;
    }
}
