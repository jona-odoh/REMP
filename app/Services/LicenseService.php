<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Enums\HotelLicenseStatus;
use App\Enums\LicenseApplicationStatus;
use App\Models\Hotel;
use App\Models\Inspection;
use App\Models\LicenseApplication;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LicenseService
{
    public function __construct(
        private readonly AuditService $auditService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDraft(array $data, User $actor): LicenseApplication
    {
        return DB::transaction(function () use ($data, $actor): LicenseApplication {
            $hotel = Hotel::findOrFail($data['hotel_id']);

            $application = LicenseApplication::create([
                'application_number' => $this->generateApplicationNumber(),
                'hotel_id' => $hotel->id,
                'license_type_id' => $data['license_type_id'],
                'application_type' => $data['application_type'] ?? 'NEW',
                'status' => LicenseApplicationStatus::DRAFT,
                'assigned_officer_id' => $data['assigned_officer_id'] ?? null,
                'created_by' => $actor->getKey(),
            ]);

            if ($hotel->license_status === HotelLicenseStatus::UNLICENSED) {
                $hotel->update(['license_status' => HotelLicenseStatus::PENDING]);
            }

            return $application;
        });
    }

    public function submit(LicenseApplication $application, User $actor): LicenseApplication
    {
        $this->assertTransition($application, LicenseApplicationStatus::SUBMITTED);

        $application->update([
            'status' => LicenseApplicationStatus::SUBMITTED,
            'submitted_at' => now(),
        ]);

        if ($application->hotel->license_status === HotelLicenseStatus::UNLICENSED) {
            $application->hotel->update(['license_status' => HotelLicenseStatus::PENDING]);
        }

        return $application->fresh();
    }

    public function startReview(LicenseApplication $application, User $officer): LicenseApplication
    {
        $this->assertTransition($application, LicenseApplicationStatus::UNDER_REVIEW);

        $application->update([
            'status' => LicenseApplicationStatus::UNDER_REVIEW,
            'assigned_officer_id' => $application->assigned_officer_id ?? $officer->getKey(),
            'reviewed_at' => now(),
        ]);

        return $application->fresh();
    }

    public function markInspectionScheduled(LicenseApplication $application, Inspection $inspection): LicenseApplication
    {
        $this->assertTransition($application, LicenseApplicationStatus::INSPECTION_SCHEDULED);

        $application->update([
            'status' => LicenseApplicationStatus::INSPECTION_SCHEDULED,
            'assigned_officer_id' => $inspection->assigned_officer_id,
        ]);

        return $application->fresh();
    }

    public function markInspectionCompleted(LicenseApplication $application): LicenseApplication
    {
        $this->assertTransition($application, LicenseApplicationStatus::INSPECTION_COMPLETED);

        $application->update([
            'status' => LicenseApplicationStatus::INSPECTION_COMPLETED,
        ]);

        return $application->fresh();
    }

    public function approve(LicenseApplication $application, User $approver, string $reason): LicenseApplication
    {
        if (trim($reason) === '') {
            throw new DomainException('A mandatory reason is required to approve a license application.');
        }

        $this->assertTransition($application, LicenseApplicationStatus::APPROVED);

        if (! $application->hasCompletedInspection()) {
            throw new DomainException('License approval is blocked: at least one completed inspection is required before approval.');
        }

        return DB::transaction(function () use ($application, $approver, $reason): LicenseApplication {
            $before = ['status' => $application->status->value];
            $validityMonths = max(1, (int) ($application->licenseType?->validity_months ?? 12));
            $issuedAt = today();
            $expiresAt = today()->addMonths($validityMonths);

            $application->update([
                'status' => LicenseApplicationStatus::APPROVED,
                'decided_at' => now(),
                'decided_by' => $approver->getKey(),
                'decision_reason' => trim($reason),
                'license_number' => $application->license_number ?? $this->generateLicenseNumber(),
                'issued_at' => $issuedAt,
                'expires_at' => $expiresAt,
            ]);

            $application->hotel->update([
                'license_status' => HotelLicenseStatus::LICENSED,
            ]);

            $this->auditService->record(
                action: AuditAction::APPROVE,
                entity: $application,
                before: $before,
                after: [
                    'status' => LicenseApplicationStatus::APPROVED->value,
                    'license_number' => $application->license_number,
                    'expires_at' => $expiresAt->toDateString(),
                    'decision_reason' => trim($reason),
                ],
                actor: $approver,
            );

            return $application->fresh(['hotel', 'licenseType']);
        });
    }

    public function reject(LicenseApplication $application, User $rejector, string $reason): LicenseApplication
    {
        if (trim($reason) === '') {
            throw new DomainException('A mandatory reason is required to reject a license application.');
        }

        $this->assertTransition($application, LicenseApplicationStatus::REJECTED);

        return DB::transaction(function () use ($application, $rejector, $reason): LicenseApplication {
            $before = ['status' => $application->status->value];

            $application->update([
                'status' => LicenseApplicationStatus::REJECTED,
                'decided_at' => now(),
                'decided_by' => $rejector->getKey(),
                'decision_reason' => trim($reason),
            ]);

            if ($application->hotel->license_status === HotelLicenseStatus::PENDING) {
                $application->hotel->update(['license_status' => HotelLicenseStatus::UNLICENSED]);
            }

            $this->auditService->record(
                action: AuditAction::REJECT,
                entity: $application,
                before: $before,
                after: [
                    'status' => LicenseApplicationStatus::REJECTED->value,
                    'decision_reason' => trim($reason),
                ],
                actor: $rejector,
            );

            return $application->fresh(['hotel']);
        });
    }

    public function markExpired(LicenseApplication $application): LicenseApplication
    {
        $this->assertTransition($application, LicenseApplicationStatus::EXPIRED);

        return DB::transaction(function () use ($application): LicenseApplication {
            $application->update(['status' => LicenseApplicationStatus::EXPIRED]);
            $application->hotel->update(['license_status' => HotelLicenseStatus::EXPIRED]);

            return $application->fresh();
        });
    }

    public function initiateRenewal(LicenseApplication $previous, User $actor): LicenseApplication
    {
        $this->assertTransition($previous, LicenseApplicationStatus::RENEWED);

        return DB::transaction(function () use ($previous, $actor): LicenseApplication {
            $previous->update(['status' => LicenseApplicationStatus::RENEWED]);

            return $this->createDraft([
                'hotel_id' => $previous->hotel_id,
                'license_type_id' => $previous->license_type_id,
                'application_type' => 'RENEWAL',
                'assigned_officer_id' => $previous->assigned_officer_id,
            ], $actor);
        });
    }

    protected function assertTransition(LicenseApplication $application, LicenseApplicationStatus $target): void
    {
        if (! $application->status->canTransitionTo($target)) {
            throw new DomainException(
                "Invalid license state transition from [{$application->status->value}] to [{$target->value}]."
            );
        }
    }

    protected function generateApplicationNumber(): string
    {
        return 'KSMCT-APP-'.now()->format('Y').'-'.strtoupper(Str::random(6));
    }

    protected function generateLicenseNumber(): string
    {
        return 'KSMCT-LIC-'.now()->format('Y').'-'.strtoupper(Str::random(6));
    }
}
