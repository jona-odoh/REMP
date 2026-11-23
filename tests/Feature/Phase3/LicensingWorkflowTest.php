<?php

namespace Tests\Feature\Phase3;

use App\Enums\AuditAction;
use App\Enums\HotelLicenseStatus;
use App\Enums\InspectionStatus;
use App\Enums\LicenseApplicationStatus;
use App\Models\Hotel;
use App\Models\Inspection;
use App\Models\InspectionChecklistTemplate;
use App\Models\LicenseApplication;
use App\Models\LicenseType;
use App\Models\User;
use App\Services\LicenseService;
use Database\Seeders\DatabaseSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LicensingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_full_license_state_machine_from_draft_to_approved_and_renewed(): void
    {
        $officer = User::where('email', 'licensing@ksmct.kogistate.gov.ng')->firstOrFail();
        $hotelsHead = User::where('email', 'hotels.head@ksmct.kogistate.gov.ng')->firstOrFail();
        $hotel = Hotel::where('name', 'Ganaja Executive Motel')->firstOrFail();
        $licenseType = LicenseType::where('code', 'LIC-GST')->firstOrFail();
        $checklistItem = InspectionChecklistTemplate::firstOrFail();

        // 1. Create Draft
        $this->actingAs($officer)
            ->post(route('licensing.applications.store'), [
                'hotel_id' => $hotel->id,
                'license_type_id' => $licenseType->id,
                'application_type' => 'NEW',
                'assigned_officer_id' => $officer->id,
            ])
            ->assertRedirect();

        $app = LicenseApplication::where('hotel_id', $hotel->id)->latest('id')->firstOrFail();
        $this->assertSame(LicenseApplicationStatus::DRAFT, $app->status);

        // 2. Submit
        $this->actingAs($officer)
            ->post(route('licensing.applications.submit', $app))
            ->assertRedirect();
        $this->assertSame(LicenseApplicationStatus::SUBMITTED, $app->fresh()->status);

        // 3. Start Review
        $this->actingAs($officer)
            ->post(route('licensing.applications.review', $app))
            ->assertRedirect();
        $this->assertSame(LicenseApplicationStatus::UNDER_REVIEW, $app->fresh()->status);

        // 4. Schedule Inspection (auto-transitions application to INSPECTION_SCHEDULED)
        $this->actingAs($officer)
            ->post(route('licensing.inspections.store'), [
                'hotel_id' => $hotel->id,
                'license_application_id' => $app->id,
                'assigned_officer_id' => $officer->id,
                'scheduled_date' => today()->toDateString(),
            ])
            ->assertRedirect();

        $app->refresh();
        $this->assertSame(LicenseApplicationStatus::INSPECTION_SCHEDULED, $app->status);
        $inspection = Inspection::where('license_application_id', $app->id)->firstOrFail();

        // 5. Complete Inspection (auto-transitions application to INSPECTION_COMPLETED)
        $this->actingAs($officer)
            ->post(route('licensing.inspections.complete', $inspection), [
                'checklist' => [
                    ['template_id' => $checklistItem->id, 'passed' => true, 'remark' => 'Verified on site'],
                ],
                'notes' => 'Facility meets minimum hospitality standards.',
            ])
            ->assertRedirect();

        $this->assertSame(InspectionStatus::COMPLETED, $inspection->fresh()->status);
        $this->assertSame(LicenseApplicationStatus::INSPECTION_COMPLETED, $app->fresh()->status);

        // 6. Licensing Officer CANNOT grant final approval (403)
        $this->actingAs($officer)
            ->post(route('licensing.applications.approve', $app), [
                'decision_reason' => 'Attempted approval by officer without authority.',
            ])
            ->assertForbidden();

        // 7. Dept Head Hotels approves with mandatory reason
        $this->actingAs($hotelsHead)
            ->post(route('licensing.applications.approve', $app), [
                'decision_reason' => 'All inspection criteria passed and fee verified.',
            ])
            ->assertRedirect();

        $app->refresh();
        $this->assertSame(LicenseApplicationStatus::APPROVED, $app->status);
        $this->assertNotNull($app->license_number);
        $this->assertSame(HotelLicenseStatus::LICENSED, $hotel->fresh()->license_status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::APPROVE->value,
            'entity_type' => LicenseApplication::class,
            'entity_id' => $app->id,
        ]);

        // 8. Initiate Renewal
        $this->actingAs($officer)
            ->post(route('licensing.applications.renew', $app))
            ->assertRedirect();

        $this->assertSame(LicenseApplicationStatus::RENEWED, $app->fresh()->status);
        $this->assertSame(2, LicenseApplication::where('hotel_id', $hotel->id)->count());
    }

    public function test_approval_is_blocked_without_completed_inspection_and_requires_reason(): void
    {
        $hotelsHead = User::where('email', 'hotels.head@ksmct.kogistate.gov.ng')->firstOrFail();
        $app = LicenseApplication::where('status', LicenseApplicationStatus::INSPECTION_SCHEDULED->value)->firstOrFail();

        // Missing reason -> validation error
        $this->actingAs($hotelsHead)
            ->post(route('licensing.applications.approve', $app), [
                'decision_reason' => '',
            ])
            ->assertSessionHasErrors('decision_reason');

        // Even if status were forced to INSPECTION_COMPLETED without an actual completed inspection row, LicenseService blocks it
        $app->update(['status' => LicenseApplicationStatus::INSPECTION_COMPLETED]);
        $this->expectException(DomainException::class);
        app(LicenseService::class)->approve($app->fresh(), $hotelsHead, 'Trying to bypass inspection gate');
    }

    public function test_rejection_requires_reason_and_logs_reject_audit_action(): void
    {
        $hotelsHead = User::where('email', 'hotels.head@ksmct.kogistate.gov.ng')->firstOrFail();
        $app = LicenseApplication::where('status', LicenseApplicationStatus::INSPECTION_COMPLETED->value)->firstOrFail();

        $this->actingAs($hotelsHead)
            ->post(route('licensing.applications.reject', $app), [
                'decision_reason' => 'Emergency exit signage and fire extinguishers failed inspection.',
            ])
            ->assertRedirect();

        $this->assertSame(LicenseApplicationStatus::REJECTED, $app->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::REJECT->value,
            'entity_type' => LicenseApplication::class,
            'entity_id' => $app->id,
        ]);
    }
}
