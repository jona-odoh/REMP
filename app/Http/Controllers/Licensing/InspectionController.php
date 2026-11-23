<?php

namespace App\Http\Controllers\Licensing;

use App\Enums\InspectionStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Licensing\CompleteInspectionRequest;
use App\Http\Requests\Licensing\ScheduleInspectionRequest;
use App\Models\Hotel;
use App\Models\Inspection;
use App\Models\InspectionChecklistTemplate;
use App\Models\User;
use App\Services\InspectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InspectionController extends Controller
{
    public function __construct(
        private readonly InspectionService $inspectionService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Inspection::class);

        $inspections = Inspection::query()
            ->with(['hotel.lga', 'assignedOfficer', 'licenseApplication'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->when($request->boolean('overdue'), fn ($q) => $q->overdue())
            ->orderByDesc('scheduled_date')
            ->paginate(15)
            ->withQueryString();

        $hotels = Hotel::orderBy('name')->get();
        $officers = User::where('is_active', true)
            ->role([RoleName::LICENSING_OFFICER->value, RoleName::DEPT_HEAD_HOTELS->value])
            ->orderBy('name')
            ->get();

        return view('licensing.inspections.index', [
            'inspections' => $inspections,
            'statuses' => InspectionStatus::cases(),
            'hotels' => $hotels,
            'officers' => $officers,
        ]);
    }

    public function store(ScheduleInspectionRequest $request): RedirectResponse
    {
        $inspection = $this->inspectionService->schedule($request->validated(), $request->user());

        return redirect()
            ->route('licensing.inspections.show', $inspection)
            ->with('status', "Inspection scheduled for {$inspection->hotel->name} on {$inspection->scheduled_date->format('d/m/Y')}.");
    }

    public function show(Inspection $inspection): View
    {
        $this->authorize('view', $inspection);

        $inspection->load(['hotel.lga', 'licenseApplication.licenseType', 'assignedOfficer', 'documents.uploader']);

        $licenseTypeId = $inspection->licenseApplication?->license_type_id;
        $checklistTemplates = InspectionChecklistTemplate::active()
            ->where(function ($q) use ($licenseTypeId) {
                $q->whereNull('license_type_id');
                if ($licenseTypeId) {
                    $q->orWhere('license_type_id', $licenseTypeId);
                }
            })
            ->get();

        return view('licensing.inspections.show', compact('inspection', 'checklistTemplates'));
    }

    public function complete(CompleteInspectionRequest $request, Inspection $inspection): RedirectResponse
    {
        $completed = $this->inspectionService->complete(
            $inspection,
            $request->validated('checklist'),
            $request->validated('notes'),
        );

        $verdict = $completed->overall_passed ? 'PASSED' : 'FAILED (mandatory item(s) unmet)';

        return redirect()
            ->route('licensing.inspections.show', $completed)
            ->with('status', "Inspection completed — Verdict: {$verdict}.");
    }
}
