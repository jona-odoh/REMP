<?php

namespace App\Notifications;

use App\Models\LicenseApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LicenseExpiringNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly LicenseApplication $application,
        public readonly int $thresholdDays,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $daysRemaining = max(0, (int) today()->diffInDays($this->application->expires_at, false));

        return [
            'title' => "Hotel License Expiring in {$daysRemaining} Day(s)",
            'message' => "{$this->application->hotel->name} (License {$this->application->license_number}) expires on {$this->application->expires_at->format('d/m/Y')}.",
            'license_application_id' => $this->application->id,
            'hotel_id' => $this->application->hotel_id,
            'threshold_days' => $this->thresholdDays,
            'url' => route('licensing.applications.show', $this->application),
        ];
    }
}
