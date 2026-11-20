<?php

namespace App\Http\Controllers;

use App\Models\AssessmentBusiness;
use App\Models\AssessmentHealthFacility;
use App\Models\AssessmentSchool;
use App\Models\Community;
use App\Models\CostEstimate;
use App\Models\EnergyDemand;
use App\Models\Lga;
use App\Models\PriorityRanking;
use App\Models\Recommendation;
use App\Models\Report;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $lgaId = $request->query('lga_id');
        $wardId = $request->query('ward_id');
        $communityId = $request->query('community_id');

        $lgas = Lga::orderBy('name')->get();
        $wards = $lgaId ? Ward::where('lga_id', $lgaId)->orderBy('name')->get() : Ward::orderBy('name')->get();

        $communityQuery = Community::with([
            'ward.lga',
            'latestAssessment',
            'latestEnergyDemand',
            'latestRecommendation.costEstimate',
            'priorityRanking',
        ]);

        if ($communityId) {
            $communityQuery->where('id', $communityId);
        } elseif ($wardId) {
            $communityQuery->where('ward_id', $wardId);
        } elseif ($lgaId) {
            $communityQuery->whereHas('ward', fn ($q) => $q->where('lga_id', $lgaId));
        }

        $communities = $communityQuery->get();
        $filteredCommunityIds = $communities->pluck('id');

        // Summary KPIs per FRD Category 1
        $totalLgas = $lgas->count();
        $totalCommunities = $communities->count();

        $reportQuery = Report::query();
        if ($communityId) {
            $reportQuery->where('community_id', $communityId);
        } elseif ($wardId) {
            $reportQuery->where('ward_id', $wardId);
        } elseif ($lgaId) {
            $reportQuery->where('lga_id', $lgaId);
        }
        $reportedChallengesCount = $reportQuery->count();

        $verifiedCommunitiesCount = $communities->where('is_verified', true)->count();
        $assessedCommunitiesCount = $communities->filter(fn ($c) => $c->latestAssessment !== null)->count();

        $affectedHouseholdsCount = $communities->sum(function ($c) {
            return $c->latestAssessment
                ? $c->latestAssessment->households_without_electricity
                : ($c->electricity_status_enum !== 'reliable' ? ($c->estimated_households ?? 0) : 0);
        });

        $assessmentIds = $communities->pluck('latestAssessment.id')->filter()->values();

        $schoolsAssessedCount = AssessmentSchool::whereIn('assessment_id', $assessmentIds)->count();
        $hospitalsAssessedCount = AssessmentHealthFacility::whereIn('assessment_id', $assessmentIds)->count();
        $businessesAssessedCount = AssessmentBusiness::whereIn('assessment_id', $assessmentIds)->count();

        // Electricity status distribution
        $statusCounts = [
            'no_electricity' => $communities->where('electricity_status_enum', 'no_electricity')->count(),
            'unreliable' => $communities->where('electricity_status_enum', 'unreliable')->count(),
            'inadequate' => $communities->where('electricity_status_enum', 'inadequate')->count(),
            'reliable' => $communities->where('electricity_status_enum', 'reliable')->count(),
        ];

        // Total Estimated Demand & Cost
        $totalDailyDemandKwh = $communities->sum(fn ($c) => $c->latestEnergyDemand?->total_daily_kwh ?? 0);
        $totalPeakLoadKw = $communities->sum(fn ($c) => $c->latestEnergyDemand?->peak_load_kw ?? 0);
        $totalEstimatedCostNgn = $communities->sum(fn ($c) => $c->latestRecommendation?->costEstimate?->total_estimated_cost_ngn ?? 0);

        // Demand per LGA for Bar Chart
        $demandByLga = $lgas->map(function ($lga) use ($communities) {
            $lgaCommunities = $communities->filter(fn ($c) => $c->ward?->lga_id === $lga->id);
            return [
                'lga_name' => $lga->name,
                'daily_kwh' => round($lgaCommunities->sum(fn ($c) => $c->latestEnergyDemand?->total_daily_kwh ?? 0), 2),
                'peak_kw' => round($lgaCommunities->sum(fn ($c) => $c->latestEnergyDemand?->peak_load_kw ?? 0), 2),
                'cost_ngn' => round($lgaCommunities->sum(fn ($c) => $c->latestRecommendation?->costEstimate?->total_estimated_cost_ngn ?? 0), 2),
                'communities_count' => $lgaCommunities->count(),
            ];
        });

        // Proposed Renewable Energy Interventions breakdown
        $interventionsBreakdown = $communities
            ->filter(fn ($c) => $c->latestRecommendation !== null)
            ->groupBy(fn ($c) => $c->latestRecommendation->recommended_solution_enum)
            ->map(function ($group, $solutionKey) {
                $firstRec = $group->first()->latestRecommendation;
                return [
                    'key' => $solutionKey,
                    'label' => $firstRec->solution_label,
                    'count' => $group->count(),
                    'total_capacity_kw' => round($group->sum(fn ($c) => $c->latestRecommendation->estimated_capacity_kw), 2),
                    'total_cost_ngn' => round($group->sum(fn ($c) => $c->latestRecommendation->costEstimate?->total_estimated_cost_ngn ?? 0), 2),
                ];
            })->values();

        // Priority breakdown & top priority communities
        $priorityCounts = [
            'high' => $communities->filter(fn ($c) => $c->priorityRanking?->priority_level_enum === 'high')->count(),
            'medium' => $communities->filter(fn ($c) => $c->priorityRanking?->priority_level_enum === 'medium')->count(),
            'low' => $communities->filter(fn ($c) => ($c->priorityRanking?->priority_level_enum ?? 'low') === 'low')->count(),
        ];

        $priorityCommunities = $communities
            ->sortByDesc(fn ($c) => $c->priorityRanking?->score ?? 0)
            ->take(12)
            ->values();

        $allSelectableCommunities = $wardId
            ? Community::where('ward_id', $wardId)->orderBy('name')->get()
            : ($lgaId
                ? Community::whereHas('ward', fn ($q) => $q->where('lga_id', $lgaId))->orderBy('name')->get()
                : Community::orderBy('name')->limit(150)->get());

        return view('dashboard.index', compact(
            'lgas',
            'wards',
            'allSelectableCommunities',
            'lgaId',
            'wardId',
            'communityId',
            'totalLgas',
            'totalCommunities',
            'reportedChallengesCount',
            'verifiedCommunitiesCount',
            'assessedCommunitiesCount',
            'affectedHouseholdsCount',
            'schoolsAssessedCount',
            'hospitalsAssessedCount',
            'businessesAssessedCount',
            'statusCounts',
            'totalDailyDemandKwh',
            'totalPeakLoadKw',
            'totalEstimatedCostNgn',
            'demandByLga',
            'interventionsBreakdown',
            'priorityCounts',
            'priorityCommunities'
        ));
    }
}
