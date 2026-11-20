<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommunityRequest;
use App\Models\Community;
use App\Models\Lga;
use App\Models\Report;
use App\Models\Ward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GeographyController extends Controller
{
    public function map(Request $request): View
    {
        $lgaId = $request->query('lga_id');
        $status = $request->query('status');
        $verificationFilter = $request->query('verification');

        $lgas = Lga::withCount(['wards', 'communities'])->orderBy('name')->get();
        $wards = Ward::with('lga')->orderBy('name')->get();

        $query = Community::with([
            'ward.lga',
            'latestAssessment',
            'latestEnergyDemand',
            'latestRecommendation.costEstimate',
            'priorityRanking',
        ]);

        if ($lgaId) {
            $query->whereHas('ward', fn ($q) => $q->where('lga_id', $lgaId));
        }

        if ($status) {
            $query->where('electricity_status_enum', $status);
        }

        if ($verificationFilter === 'verified') {
            $query->where('is_verified', true);
        } elseif ($verificationFilter === 'unverified') {
            $query->where('is_verified', false);
        }

        $communities = $query->orderBy('name')->get();

        // Also fetch unverified public report locations with GPS coordinates
        $unverifiedReportsQuery = Report::with(['lga', 'ward', 'community'])
            ->whereIn('status_enum', ['submitted', 'under_review'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if ($lgaId) {
            $unverifiedReportsQuery->where('lga_id', $lgaId);
        }

        $unverifiedReports = $unverifiedReportsQuery->latest()->limit(50)->get();

        // Format GeoJSON / Marker payload for Leaflet
        $mapMarkers = $communities->map(function (Community $c) {
            $rec = $c->latestRecommendation;
            $demand = $c->latestEnergyDemand;
            $cost = $rec?->costEstimate;
            $priority = $c->priorityRanking;

            return [
                'id' => $c->id,
                'name' => $c->name,
                'ward' => $c->ward?->name,
                'lga' => $c->ward?->lga?->name,
                'lga_id' => $c->ward?->lga_id,
                'lat' => $c->latitude,
                'lng' => $c->longitude,
                'status' => $c->electricity_status_enum,
                'status_label' => ucwords(str_replace('_', ' ', $c->electricity_status_enum)),
                'is_verified' => (bool) $c->is_verified,
                'has_intervention' => $rec !== null,
                'population' => $c->estimated_population,
                'households' => $c->estimated_households,
                'daily_kwh' => $demand ? round($demand->total_daily_kwh, 2) : null,
                'recommended_solution' => $rec?->solution_label,
                'cost_ngn' => $cost ? number_format($cost->total_estimated_cost_ngn, 2) : null,
                'priority_level' => $priority?->priority_level_enum ?? 'low',
                'priority_score' => $priority?->score ?? 0,
                'profile_url' => route('communities.show', $c->id),
            ];
        });

        return view('geography.index', compact(
            'lgas',
            'wards',
            'communities',
            'unverifiedReports',
            'mapMarkers',
            'lgaId',
            'status',
            'verificationFilter'
        ));
    }

    public function showCommunity(Community $community): View
    {
        $community->load([
            'ward.lga',
            'reports.verifier',
            'assessments.officer',
            'assessments.householdAssessment',
            'assessments.schools',
            'assessments.healthFacilities',
            'assessments.businesses',
            'assessments.assessmentAppliances.appliance',
            'latestEnergyDemand',
            'latestRecommendation.costEstimate',
            'priorityRanking',
        ]);

        $wards = Ward::with('lga')->orderBy('name')->get();

        return view('geography.show', compact('community', 'wards'));
    }

    public function storeLga(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:lgas,name'],
            'code' => ['required', 'string', 'max:30', 'unique:lgas,code'],
        ]);

        Lga::create($data);

        return back()->with('success', "LGA '{$data['name']}' configured successfully.");
    }

    public function storeWard(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lga_id' => ['required', 'exists:lgas,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        Ward::create($data);

        return back()->with('success', "Ward '{$data['name']}' registered successfully.");
    }

    public function storeCommunity(StoreCommunityRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_verified'] = $request->boolean('is_verified', true);

        $community = Community::create($data);

        return redirect()->route('communities.show', $community)->with('success', "Community '{$community->name}' registered on the platform.");
    }

    public function updateCommunity(StoreCommunityRequest $request, Community $community): RedirectResponse
    {
        $data = $request->validated();
        $data['is_verified'] = $request->boolean('is_verified');

        $community->update($data);

        return back()->with('success', "Community '{$community->name}' updated successfully.");
    }
}
