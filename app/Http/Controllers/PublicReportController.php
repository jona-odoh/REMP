<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicReportRequest;
use App\Models\Community;
use App\Models\Lga;
use App\Models\Report;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PublicReportController extends Controller
{
    public function create(): View
    {
        $lgas = Lga::orderBy('name')->get();
        $wards = Ward::orderBy('name')->get();
        $communities = Community::select('id', 'ward_id', 'name', 'latitude', 'longitude')->orderBy('name')->get();

        return view('public.report-form', compact('lgas', 'wards', 'communities'));
    }

    public function store(StorePublicReportRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Check for duplicate submission in the last 24 hours (same community + same situation)
        $duplicateQuery = Report::where('lga_id', $data['lga_id'])
            ->where('ward_id', $data['ward_id'])
            ->where('electricity_situation_enum', $data['electricity_situation_enum'])
            ->where('created_at', '>=', now()->subHours(24));

        if (!empty($data['community_id'])) {
            $duplicateQuery->where('community_id', $data['community_id']);
        } elseif (!empty($data['custom_community_name'])) {
            $duplicateQuery->where('custom_community_name', $data['custom_community_name']);
        }

        $existingRecent = $duplicateQuery->first();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('report-photos', 'public');
        }

        $referenceNumber = Report::generateReferenceNumber();

        $report = Report::create([
            'reference_number' => $referenceNumber,
            'lga_id' => $data['lga_id'],
            'ward_id' => $data['ward_id'],
            'community_id' => $data['community_id'] ?? null,
            'custom_community_name' => $data['custom_community_name'] ?? null,
            'reporter_name' => $data['reporter_name'] ?? 'Anonymous Resident',
            'reporter_contact' => $data['reporter_contact'] ?? null,
            'electricity_situation_enum' => $data['electricity_situation_enum'],
            'duration_of_problem' => $data['duration_of_problem'],
            'affected_households_estimate' => $data['affected_households_estimate'] ?? null,
            'affected_facilities_json' => $data['affected_facilities_json'] ?? [],
            'description' => $data['description'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'photo_path' => $photoPath,
            'status_enum' => 'submitted',
            'is_potential_duplicate' => $existingRecent !== null,
        ]);

        return redirect()
            ->route('public.report.track', ['ref' => $report->reference_number])
            ->with('submitted_ref', $report->reference_number)
            ->with('duplicate_notice', $existingRecent ? "Note: A similar report ({$existingRecent->reference_number}) was logged for this community within the last 24 hours. Your submission has been flagged for consolidated review." : null);
    }

    public function track(Request $request): View
    {
        $ref = trim((string) $request->query('ref', ''));
        $report = null;

        if ($ref !== '') {
            $report = Report::with(['lga', 'ward', 'community', 'verifier', 'assignedOfficer'])
                ->where('reference_number', strtoupper($ref))
                ->first();
        }

        $sampleRefs = Report::latest()->limit(5)->pluck('reference_number');

        return view('public.track', compact('ref', 'report', 'sampleRefs'));
    }

    /**
     * LGA Officer & Admin Verification Queue (Category 4F).
     */
    public function verificationQueue(Request $request): View
    {
        $user = $request->user();
        $statusFilter = $request->query('status');
        $lgaFilter = $request->query('lga_id');

        $query = Report::with(['lga', 'ward', 'community', 'verifier', 'assignedOfficer'])->latest();

        // If user is an LGA Officer bound to a specific LGA, scope by default unless overridden
        if ($user->role_enum === 'lga_officer' && $user->lga_id && !$lgaFilter) {
            $lgaFilter = $user->lga_id;
        }

        if ($lgaFilter) {
            $query->where('lga_id', $lgaFilter);
        }

        if ($statusFilter) {
            $query->where('status_enum', $statusFilter);
        }

        $reports = $query->paginate(15)->withQueryString();
        $lgas = Lga::orderBy('name')->get();
        $fieldOfficers = User::whereIn('role_enum', ['field_officer', 'admin'])->orderBy('name')->get();

        return view('reports.verification-queue', compact('reports', 'lgas', 'fieldOfficers', 'statusFilter', 'lgaFilter'));
    }

    public function updateVerificationStatus(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate([
            'status_enum' => ['required', 'in:under_review,verified,rejected'],
            'verification_comments' => ['required', 'string', 'max:2000'],
            'assigned_officer_id' => ['nullable', 'exists:users,id'],
            'create_community_if_missing' => ['nullable', 'boolean'],
        ]);

        // If verifying a report for a newly reported custom community name, register the community
        $communityId = $report->community_id;
        if ($data['status_enum'] === 'verified') {
            if (!$communityId && $report->custom_community_name) {
                $newCommunity = Community::create([
                    'ward_id' => $report->ward_id,
                    'name' => $report->custom_community_name,
                    'description' => $report->description,
                    'latitude' => $report->latitude ?? 7.8000,
                    'longitude' => $report->longitude ?? 6.7300,
                    'estimated_households' => $report->affected_households_estimate ?? 50,
                    'estimated_population' => ($report->affected_households_estimate ?? 50) * 6,
                    'electricity_status_enum' => $report->electricity_situation_enum === 'no_electricity' ? 'no_electricity' : 'unreliable',
                    'is_verified' => true,
                ]);
                $communityId = $newCommunity->id;
            } elseif ($communityId) {
                Community::where('id', $communityId)->update(['is_verified' => true]);
            }
        }

        $report->update([
            'community_id' => $communityId,
            'status_enum' => $data['status_enum'],
            'verification_comments' => $data['verification_comments'],
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'assigned_officer_id' => $data['assigned_officer_id'] ?? $report->assigned_officer_id,
        ]);

        return back()->with('success', "Report {$report->reference_number} marked as " . strtoupper(str_replace('_', ' ', $data['status_enum'])) . ".");
    }
}
