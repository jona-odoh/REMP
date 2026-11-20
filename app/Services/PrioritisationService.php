<?php

namespace App\Services;

use App\Models\Community;
use App\Models\PriorityRanking;

class PrioritisationService
{
    /**
     * Calculate priority score and classification for a community based on FRD criteria:
     * - Number of affected households (Weight: 30%)
     * - Affected schools and healthcare facilities (Weight: 25%)
     * - Economic activities and livelihood impact (Weight: 20%)
     * - Electricity demand (Weight: 15%)
     * - Technical feasibility & cost efficiency (Weight: 10%)
     *
     * Classification:
     * - High: Score >= 75 (or > 80 per strict threshold; configurable, we support >= 75 / 80)
     * - Medium: Score 50 - 79.99
     * - Low: Score < 50
     */
    public function rankCommunity(Community $community): PriorityRanking
    {
        $community->load([
            'latestAssessment.schools',
            'latestAssessment.healthFacilities',
            'latestAssessment.businesses',
            'latestEnergyDemand',
            'latestRecommendation.costEstimate',
        ]);

        $assessment = $community->latestAssessment;
        $demand = $community->latestEnergyDemand;

        // 1. Affected Households Score (0 - 100) -> Weight 30%
        $householdsWithout = $assessment
            ? (int) $assessment->households_without_electricity
            : (int) ($community->estimated_households ?? 0);
        // Normalize against 180 households benchmark
        $householdRawScore = min(100, ($householdsWithout / 180) * 100);
        if ($community->electricity_status_enum === 'no_electricity') {
            $householdRawScore = min(100, $householdRawScore + 15);
        }
        $householdWeighted = round($householdRawScore * 0.30, 2);

        // 2. Affected Schools & Healthcare Facilities Score (0 - 100) -> Weight 25%
        $schoolsCount = $assessment ? $assessment->schools->count() : 0;
        $healthCount = $assessment ? $assessment->healthFacilities->count() : 0;
        // Health facilities carry slightly higher urgency due to vaccine storage & emergency lighting
        $facilityPoints = ($healthCount * 40) + ($schoolsCount * 25);
        $facilityRawScore = min(100, $facilityPoints);
        $facilityWeighted = round($facilityRawScore * 0.25, 2);

        // 3. Economic Activities & Livelihood Impact Score (0 - 100) -> Weight 20%
        $businessesCount = $assessment ? $assessment->businesses->count() : 0;
        $economicRawScore = min(100, $businessesCount * 28);
        $economicWeighted = round($economicRawScore * 0.20, 2);

        // 4. Electricity Demand Score (0 - 100) -> Weight 15%
        $dailyKwh = $demand ? (float) $demand->total_daily_kwh : 0.0;
        $demandRawScore = min(100, ($dailyKwh / 120.0) * 100);
        $demandWeighted = round($demandRawScore * 0.15, 2);

        // 5. Technical Feasibility & Cost-per-Household Readiness (0 - 100) -> Weight 10%
        $hasGps = !empty($community->latitude) && !empty($community->longitude);
        $isVerified = (bool) $community->is_verified;
        $feasibilityRawScore = 60 + ($hasGps ? 20 : 0) + ($isVerified ? 20 : 0);
        $feasibilityWeighted = round(min(100, $feasibilityRawScore) * 0.10, 2);

        $totalScore = round(
            $householdWeighted + $facilityWeighted + $economicWeighted + $demandWeighted + $feasibilityWeighted,
            2
        );

        // Classify per FRD Phase 2 Category 7: High (Score >= 80), Medium (50-79.99), Low (< 50)
        if ($totalScore >= 80) {
            $level = 'high';
        } elseif ($totalScore >= 50) {
            $level = 'medium';
        } else {
            $level = 'low';
        }

        $criteria = [
            'weights' => [
                'affected_households' => '30%',
                'schools_and_healthcare' => '25%',
                'economic_activity_impact' => '20%',
                'electricity_demand' => '15%',
                'technical_feasibility' => '10%',
            ],
            'metrics' => [
                'households_without_electricity' => $householdsWithout,
                'schools_assessed' => $schoolsCount,
                'health_facilities_assessed' => $healthCount,
                'businesses_assessed' => $businessesCount,
                'total_daily_kwh' => $dailyKwh,
            ],
            'component_scores' => [
                'households_weighted' => $householdWeighted,
                'facilities_weighted' => $facilityWeighted,
                'economic_weighted' => $economicWeighted,
                'demand_weighted' => $demandWeighted,
                'feasibility_weighted' => $feasibilityWeighted,
            ],
        ];

        return PriorityRanking::updateOrCreate(
            ['community_id' => $community->id],
            [
                'score' => $totalScore,
                'priority_level_enum' => $level,
                'ranking_criteria_json' => $criteria,
            ]
        );
    }

    /**
     * Rank all assessed or verified communities.
     */
    public function rankAllCommunities(): int
    {
        $communities = Community::with(['latestAssessment', 'latestEnergyDemand'])->get();
        $count = 0;
        foreach ($communities as $community) {
            $this->rankCommunity($community);
            $count++;
        }
        return $count;
    }
}
