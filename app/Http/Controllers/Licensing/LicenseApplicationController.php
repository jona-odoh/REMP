<?php

namespace App\Http\Controllers\Licensing;

use App\Enums\LicenseApplicationStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\Licensing\DecideLicenseApplicationRequest;
use App\Http\Requests\Licensing\StoreLicenseApplicationRequest;
use App\Models\Hotel;
use App\Models\Lga;
use App\Models\LicenseApplication;
use App\Models\LicenseType;
use App\Models\User;
use App\Services\LicenseService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LicenseApplicationController extends Controller
{
    public function __construct(
        private readonly LicenseService $licenseService,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', LicenseApplication::class);

        $filters = $request->only(['search', 'status', 'assigned_officer_id', 'lga_id']);

        $applications = LicenseApplication::query()
            ->with(['hotel.lga', 'licenseType', 'assignedOfficer'])
            ->filter($filters)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $officers = User::where('is_active', true)
            ->role([RoleName::LICENSING_OFFICER->value, RoleName::DEPT_HEAD_HOTELS->value, RoleName::SUPER_ADMIN->value])
            ->orderBy('name')
            ->get();

        return view('licensing.applications.index', [
            'applications' => $applications,
            'statuses' => LicenseApplicationStatus::cases(),
            'officers' => $officers,
            'lgas' => Lga::active()->get(),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', LicenseApplication::class);

        return view('licensing.applications.create', [
            'hotels' => Hotel::with('lga')->orderBy('name')->get(),
            'licenseTypes' => LicenseType::active()->get(),
            'officers' => User::where('is_active', true)
                ->role([RoleName::LICENSING_OFFICER->value, RoleName::DEPT_HEAD_HOTELS->value])
                ->orderBy('name')
                ->get(),
            'selectedHotelId' => $request->integer('hotel_id') ?: null,
        ]);
    }

    public function store(StoreLicenseApplicationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $application = $this->licenseService->createDraft($data, $request->user());

        if ($request->boolean('submit_immediately')) {
            $application = $this->licenseService->submit($application, $request->user());
        }

        return redirect()
            ->route('licensing.applications.show', $application)
            ->with('status', "License application {$application->application_number} created ({$application->status->label()}).");
    }

    public function show(LicenseApplication $application): View
    {
        $this->authorize('view', $application);

        $application->load([
            'hotel.lga',
            'licenseType',
            'assignedOfficer',
            'decidedBy',
            'inspections.assignedOfficer',
            'fees',
            'documents.uploader',
        ]);

        $officers = User::where('is_active', true)
            ->role([RoleName::LICENSING_OFFICER->value, RoleName::DEPT_HEAD_HOTELS->value])
            ->orderBy('name')
            ->get();

        return view('licensing.applications.show', compact('application', 'officers'));
    }

    public function submit(Request $request, LicenseApplication $application): RedirectResponse
    {
        $this->authorize('create', LicenseApplication::class);

        try {
            $this->licenseService->submit($application, $request->user());
        } catch (DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('status', 'Application submitted for licensing review.');
    }

    public function startReview(Request $request, LicenseApplication $application): RedirectResponse
    {
        $this->authorize('review', $application);

        try {
            $this->licenseService->startReview($application, $request->user());
        } catch (DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('status', 'Application moved to Under Review.');
    }

    public function approve(DecideLicenseApplicationRequest $request, LicenseApplication $application): RedirectResponse
    {
        try {
            $approved = $this->licenseService->approve($application, $request->user(), $request->validated('decision_reason'));
        } catch (DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('status', "License approved! License No: {$approved->license_number} (valid until {$approved->expires_at->format('d/m/Y')}).");
    }

    public function reject(DecideLicenseApplicationRequest $request, LicenseApplication $application): RedirectResponse
    {
        try {
            $this->licenseService->reject($application, $request->user(), $request->validated('decision_reason'));
        } catch (DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('status', 'Application rejected with documented reason.');
    }

    public function renew(Request $request, LicenseApplication $application): RedirectResponse
    {
        $this->authorize('create', LicenseApplication::class);

        try {
            $renewal = $this->licenseService->initiateRenewal($application, $request->user());
        } catch (DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return redirect()
            ->route('licensing.applications.show', $renewal)
            ->with('status', "Renewal application {$renewal->application_number} created.");
    }
}
