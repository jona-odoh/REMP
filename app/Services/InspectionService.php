<?php

namespace App\Services;

use App\Enums\InspectionStatus;
use App\Enums\LicenseApplicationStatus;
use App\Models\Inspection;
use App\Models\InspectionChecklistTemplate;
use App\Models\LicenseApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InspectionService
{
    public function __construct(
        private readonly LicenseService $licenseService,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function schedule(array $data, User $actor): Inspection
    {
        return DB::transaction(function () use ($data, $actor): Inspection {
            $inspection = Inspection::create([
                'hotel_id' => $data['hotel_id'],
                'license_application_id' => $data['license_application_id'] ?? null,
                'assigned_officer_id' => $data['assigned_officer_id'],
                'scheduled_date' => $data['scheduled_date'],
                'status' => InspectionStatus::SCHEDULED,
                'notes' => $data['notes'] ?? null,
                'created_by' => $actor->getKey(),
            ]);

            if ($inspection->license_application_id) {
                $application = LicenseApplication::find($inspection->license_application_id);
                if ($application && $application->status === LicenseApplicationStatus::UNDER_REVIEW) {
                    $this->licenseService->markInspectionScheduled($application, $inspection);
                }
            }

            return $inspection;
        });
    }

    /**
     * @param  array<int, array{template_id: int, passed: bool, remark?: string}>  $checklistInput
     */
    public function complete(Inspection $inspection, array $checklistInput, ?string $notes = null): Inspection
    {
        return DB::transaction(function () use ($inspection, $checklistInput, $notes): Inspection {
            $templates = InspectionChecklistTemplate::whereIn('id', array_column($checklistInput, 'template_id'))
                ->get()
                ->keyBy('id');

            $structured = [];
            $mandatoryFailed = false;

            foreach ($checklistInput as $entry) {
                $tpl = $templates->get($entry['template_id']);
                $passed = (bool) ($entry['passed'] ?? false);

                if ($tpl && $tpl->is_mandatory && ! $passed) {
                    $mandatoryFailed = true;
                }

                $structured[] = [
                    'template_id' => (int) $entry['template_id'],
                    'section' => $tpl?->section ?? 'General',
                    'item_text' => $tpl?->item_text ?? 'Checklist Item',
                    'is_mandatory' => $tpl?->is_mandatory ?? false,
                    'passed' => $passed,
                    'remark' => $entry['remark'] ?? null,
                ];
            }

            $inspection->update([
                'status' => InspectionStatus::COMPLETED,
                'completed_date' => today(),
                'checklist_results' => $structured,
                'overall_passed' => ! $mandatoryFailed,
                'notes' => $notes ?? $inspection->notes,
            ]);

            if ($inspection->licenseApplication && $inspection->licenseApplication->status === LicenseApplicationStatus::INSPECTION_SCHEDULED) {
                $this->licenseService->markInspectionCompleted($inspection->licenseApplication);
            }

            return $inspection->fresh(['hotel', 'licenseApplication']);
        });
    }
}
