<?php

namespace App\Providers;

use App\Enums\RoleName;
use App\Listeners\LogAuthenticationEvents;
use App\Models\AssetCategory;
use App\Models\AuditLog;
use App\Models\CulturalAsset;
use App\Models\Document;
use App\Models\FeeSchedule;
use App\Models\Hotel;
use App\Models\Inspection;
use App\Models\InspectionChecklistTemplate;
use App\Models\IntangibleHeritage;
use App\Models\Lga;
use App\Models\LicenseApplication;
use App\Models\LicenseFee;
use App\Models\LicenseType;
use App\Models\User;
use App\Policies\AuditLogPolicy;
use App\Policies\CulturalAssetPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\HotelPolicy;
use App\Policies\InspectionPolicy;
use App\Policies\IntangibleHeritagePolicy;
use App\Policies\LicenseApplicationPolicy;
use App\Policies\LicenseFeePolicy;
use App\Policies\SystemConfigPolicy;
use App\Policies\UserPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(Lga::class, SystemConfigPolicy::class);
        Gate::policy(AssetCategory::class, SystemConfigPolicy::class);
        Gate::policy(LicenseType::class, SystemConfigPolicy::class);
        Gate::policy(FeeSchedule::class, SystemConfigPolicy::class);
        Gate::policy(InspectionChecklistTemplate::class, SystemConfigPolicy::class);
        Gate::policy(CulturalAsset::class, CulturalAssetPolicy::class);
        Gate::policy(IntangibleHeritage::class, IntangibleHeritagePolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(Hotel::class, HotelPolicy::class);
        Gate::policy(LicenseApplication::class, LicenseApplicationPolicy::class);
        Gate::policy(Inspection::class, InspectionPolicy::class);
        Gate::policy(LicenseFee::class, LicenseFeePolicy::class);

        // Super Admin bypasses standard checks except immutable AuditLog mutations & self-lockout actions
        Gate::before(function (User $user, string $ability, array $arguments = []) {
            if (! $user->hasRole(RoleName::SUPER_ADMIN->value)) {
                return null;
            }

            if (in_array($ability, ['create', 'update', 'delete'], true)) {
                $firstArg = $arguments[0] ?? null;
                if ($firstArg === AuditLog::class || $firstArg instanceof AuditLog) {
                    return false;
                }
            }

            if (in_array($ability, ['deactivate', 'forceLogout'], true)) {
                $target = $arguments[0] ?? null;
                if ($target instanceof User && $target->is($user)) {
                    return false;
                }
            }

            return true;
        });

        RateLimiter::for('login', function (Request $request) {
            $email = strtolower((string) $request->input('email'));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        Event::subscribe(LogAuthenticationEvents::class);
    }
}
