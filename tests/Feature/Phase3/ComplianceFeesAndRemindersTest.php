<?php

namespace Tests\Feature\Phase3;

use App\Enums\HotelLicenseStatus;
use App\Enums\LicenseApplicationStatus;
use App\Models\Hotel;
use App\Models\LicenseApplication;
use App\Models\User;
use App\Notifications\LicenseExpiringNotification;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComplianceFeesAndRemindersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_compliance_dashboard_displays_counts_and_overdue_inspections(): void
    {
        $hotelsHead = User::where('email', 'hotels.head@ksmct.kogistate.gov.ng')->firstOrFail();

        $response = $this->actingAs($hotelsHead)->get(route('licensing.compliance.index'));

        $response->assertOk();
        $response->assertSee('Overdue Inspections');
        $response->assertSee('Idah Royal Confluence Lodge');
    }

    public function test_expiring_license_command_sends_database_notifications_without_duplicates_and_marks_expired(): void
    {
        // Reverton Hotel in demo seeder expires in 25 days (within 30-day threshold)
        $this->artisan('ksmct:check-expiring-licenses')->assertSuccessful();

        $countAfterFirstRun = DB::table('notifications')
            ->where('type', LicenseExpiringNotification::class)
            ->count();

        $this->assertGreaterThanOrEqual(3, $countAfterFirstRun); // Dept Head, Licensing Officer, Director

        // Running again on the same day must NOT duplicate notifications for the same 30-day threshold
        $this->artisan('ksmct:check-expiring-licenses')->assertSuccessful();

        $this->assertSame(
            $countAfterFirstRun,
            DB::table('notifications')->where('type', LicenseExpiringNotification::class)->count()
        );

        // Past-due approved license should be marked EXPIRED
        $app = LicenseApplication::where('application_number', 'KSMCT-APP-2026-0001')->firstOrFail();
        $app->update(['expires_at' => today()->subDay()]);

        $this->artisan('ksmct:check-expiring-licenses')->assertSuccessful();
        $this->assertSame(LicenseApplicationStatus::EXPIRED, $app->fresh()->status);
        $this->assertSame(HotelLicenseStatus::EXPIRED, $app->hotel->fresh()->license_status);
    }

    public function test_license_fee_recording_and_excel_revenue_export(): void
    {
        $officer = User::where('email', 'licensing@ksmct.kogistate.gov.ng')->firstOrFail();
        $hotel = Hotel::firstOrFail();

        $this->actingAs($officer)
            ->post(route('licensing.fees.store'), [
                'hotel_id' => $hotel->id,
                'amount' => 120000.00,
                'receipt_reference' => 'KG-REV-2026-99901',
                'paid_at' => today()->toDateString(),
                'period' => '2026 Annual',
            ])
            ->assertRedirect(route('licensing.fees.index'));

        $this->assertDatabaseHas('license_fees', ['receipt_reference' => 'KG-REV-2026-99901']);

        $this->actingAs($officer)
            ->get(route('licensing.fees.export'))
            ->assertOk();
    }
}
