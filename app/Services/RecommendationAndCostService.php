<?php

namespace App\Services;

use App\Models\CostEstimate;
use App\Models\EnergyDemand;
use App\Models\Recommendation;
use Illuminate\Support\Facades\Auth;

class RecommendationAndCostService
{
    /**
     * Evaluate energy demand and community context to generate a Renewable Energy Recommendation
     * and an Indicative Cost Estimate in Nigerian Naira (NGN).
     */
    public function generateForEnergyDemand(
        EnergyDemand $demand,
        ?string $overrideSolution = null,
        ?string $analystNotes = null,
        ?int $userId = null
    ): Recommendation {
        $demand->load(['community', 'assessment.schools', 'assessment.healthFacilities', 'assessment.businesses']);
        $assessment = $demand->assessment;
        $community = $demand->community;

        $totalDailyKwh = (float) $demand->total_daily_kwh;
        $peakLoadKw = (float) $demand->peak_load_kw;
        $householdsWithout = $assessment ? (int) $assessment->households_without_electricity : (int) ($community->estimated_households ?? 50);
        $schoolsCount = $assessment ? $assessment->schools->count() : 0;
        $healthCount = $assessment ? $assessment->healthFacilities->count() : 0;
        $businessCount = $assessment ? $assessment->businesses->count() : 0;

        $breakdown = $demand->calculation_assumptions_json['facility_breakdown_kwh'] ?? [
            'household' => $totalDailyKwh * 0.6,
            'school' => $totalDailyKwh * 0.1,
            'hospital' => $totalDailyKwh * 0.1,
            'business' => $totalDailyKwh * 0.2,
        ];

        $businessShare = $totalDailyKwh > 0 ? (($breakdown['business'] ?? 0) / $totalDailyKwh) : 0;
        $institutionalShare = $totalDailyKwh > 0 ? ((($breakdown['school'] ?? 0) + ($breakdown['hospital'] ?? 0)) / $totalDailyKwh) : 0;

        // Determine recommended solution if not overridden
        if ($overrideSolution) {
            $solution = $overrideSolution;
        } elseif ($totalDailyKwh >= 45 || ($householdsWithout >= 60 && $businessCount >= 2)) {
            $solution = 'solar_mini_grid';
        } elseif ($businessShare >= 0.40 && $businessCount >= 2) {
            $solution = 'productive_use_solar';
        } elseif ($institutionalShare >= 0.45 && ($schoolsCount + $healthCount) >= 1 && $householdsWithout < 40) {
            $solution = 'institutional_solar';
        } elseif ($totalDailyKwh <= 18 && $businessCount === 0 && ($schoolsCount + $healthCount) === 0 && $householdsWithout <= 25) {
            $solution = 'solar_water_pumping';
        } else {
            $solution = 'solar_home_system';
        }

        // Size PV Capacity (kWp) assuming 4.6 peak sun hours in Kogi State & 0.78 system efficiency
        $peakSunHours = 4.6;
        $systemEfficiency = 0.78;
        $requiredPvKw = $totalDailyKwh > 0
            ? round(max($peakLoadKw * 1.15, $totalDailyKwh / ($peakSunHours * $systemEfficiency)), 2)
            : 5.0;

        // Size Battery Storage (kWh) assuming 1 day autonomy, 60% evening/night fraction, 85% DoD (LiFePO4)
        $batteryStorageKwh = round(($totalDailyKwh * 0.65) / 0.85, 2);
        $inverterCapacityKva = round(max($peakLoadKw * 1.25, $requiredPvKw * 0.9), 2);
        $panelCount550W = (int) ceil(($requiredPvKw * 1000) / 550);
        $batteryModules5Kwh = (int) ceil($batteryStorageKwh / 5);

        $justification = $this->buildJustification(
            $solution,
            $community->name,
            $totalDailyKwh,
            $peakLoadKw,
            $householdsWithout,
            $schoolsCount,
            $healthCount,
            $businessCount
        );

        $equipment = [
            'pv_array_capacity_kwp' => $requiredPvKw,
            'solar_panels_550w_mono_count' => $panelCount550W,
            'battery_bank_capacity_kwh' => $batteryStorageKwh,
            'lifepo4_5kwh_modules_count' => $batteryModules5Kwh,
            'inverter_capacity_kva' => $inverterCapacityKva,
            'mppt_charge_controller_amps' => (int) ceil(($requiredPvKw * 1000) / 48 * 1.1),
            'smart_meters_or_shs_kits' => $solution === 'solar_home_system' ? max(1, $householdsWithout) : ($householdsWithout + $schoolsCount + $healthCount + $businessCount),
            'distribution_pole_km' => $solution === 'solar_mini_grid' ? round(max(1.2, $householdsWithout * 0.025), 2) : 0,
        ];

        $recommendation = Recommendation::updateOrCreate(
            ['energy_demand_id' => $demand->id],
            [
                'community_id' => $community->id,
                'recommended_solution_enum' => $solution,
                'justification' => $justification,
                'estimated_capacity_kw' => $requiredPvKw,
                'equipment_requirements_json' => $equipment,
                'analyst_notes' => $analystNotes ?? 'Generated via Kogi State REMP Decision-Support Engine (4.6 PSH Kogi insolation profile).',
                'reviewed_by' => $userId ?? Auth::id(),
            ]
        );

        $this->generateCostEstimate($recommendation, $equipment);

        return $recommendation->fresh(['costEstimate']);
    }

    /**
     * Generate Indicative Cost Estimate in Nigerian Naira (NGN).
     */
    public function generateCostEstimate(Recommendation $recommendation, array $equipment): CostEstimate
    {
        // Indicative unit benchmarks in Nigerian Naira (NGN)
        $unitCosts = [
            'panel_550w_ngn' => 195000,            // NGN per 550W Monocrystalline panel
            'battery_kwh_lifepo4_ngn' => 310000,   // NGN per kWh of LiFePO4 storage
            'inverter_kva_ngn' => 240000,          // NGN per kVA hybrid inverter capacity
            'charge_controller_kw_ngn' => 65000,   // NGN per kW MPPT capacity
            'mounting_per_kw_ngn' => 75000,        // Galvanized mounting structure per kWp
            'distribution_per_km_ngn' => 4500000,  // LV distribution lines & smart meters per km
            'shs_distribution_kit_ngn' => 45000,   // Wiring/accessories per SHS/connection
            'protection_per_kw_ngn' => 42000,      // DC/AC breakers, surge arrestors, earthing per kWp
            'installation_rate_pct' => 0.12,       // 12% of hardware cost
            'transportation_rate_pct' => 0.05,     // 5% of hardware cost to rural Kogi LGA site
        ];

        $pvKw = (float) ($equipment['pv_array_capacity_kwp'] ?? $recommendation->estimated_capacity_kw);
        $panelCount = (int) ($equipment['solar_panels_550w_mono_count'] ?? ceil(($pvKw * 1000) / 550));
        $batteryKwh = (float) ($equipment['battery_bank_capacity_kwh'] ?? ($pvKw * 2.2));
        $inverterKva = (float) ($equipment['inverter_capacity_kva'] ?? ($pvKw * 1.1));
        $connections = (int) ($equipment['smart_meters_or_shs_kits'] ?? 30);
        $distKm = (float) ($equipment['distribution_pole_km'] ?? 0);

        $solarPanelsCost = round($panelCount * $unitCosts['panel_550w_ngn'], 2);
        $batteriesCost = round($batteryKwh * $unitCosts['battery_kwh_lifepo4_ngn'], 2);
        $invertersCost = round($inverterKva * $unitCosts['inverter_kva_ngn'], 2);
        $chargeControllersCost = round($pvKw * $unitCosts['charge_controller_kw_ngn'], 2);
        $mountingCost = round($pvKw * $unitCosts['mounting_per_kw_ngn'], 2);

        if ($recommendation->recommended_solution_enum === 'solar_mini_grid') {
            $distributionCost = round(($distKm * $unitCosts['distribution_per_km_ngn']) + ($connections * 65000), 2);
        } elseif ($recommendation->recommended_solution_enum === 'productive_use_solar') {
            $distributionCost = round($connections * 85000, 2);
        } else {
            $distributionCost = round($connections * $unitCosts['shs_distribution_kit_ngn'], 2);
        }

        $protectionCost = round($pvKw * $unitCosts['protection_per_kw_ngn'], 2);

        $hardwareSubtotal = $solarPanelsCost + $batteriesCost + $invertersCost + $chargeControllersCost + $mountingCost + $distributionCost + $protectionCost;

        $installationCost = round($hardwareSubtotal * $unitCosts['installation_rate_pct'], 2);
        $transportationCost = round($hardwareSubtotal * $unitCosts['transportation_rate_pct'], 2);

        $totalEstimatedCost = round($hardwareSubtotal + $installationCost + $transportationCost, 2);

        return CostEstimate::updateOrCreate(
            ['recommendation_id' => $recommendation->id],
            [
                'solar_panels_cost' => $solarPanelsCost,
                'batteries_cost' => $batteriesCost,
                'inverters_cost' => $invertersCost,
                'charge_controllers_cost' => $chargeControllersCost,
                'mounting_structures_cost' => $mountingCost,
                'distribution_cost' => $distributionCost,
                'protection_equipment_cost' => $protectionCost,
                'installation_cost' => $installationCost,
                'transportation_cost' => $transportationCost,
                'total_estimated_cost_ngn' => $totalEstimatedCost,
                'assumptions_json' => [
                    'disclaimer' => 'INDICATIVE ESTIMATE ONLY — Not a final supplier quotation. Subject to detailed engineering procurement and site survey.',
                    'currency' => 'NGN',
                    'peak_sun_hours_kogi' => 4.6,
                    'system_efficiency_factor' => 0.78,
                    'battery_chemistry' => 'LiFePO4 (85% DoD)',
                    'unit_benchmarks_ngn' => $unitCosts,
                ],
            ]
        );
    }

    private function buildJustification(
        string $solution,
        string $communityName,
        float $dailyKwh,
        float $peakKw,
        int $householdsWithout,
        int $schoolsCount,
        int $healthCount,
        int $businessCount
    ): string {
        return match ($solution) {
            'solar_mini_grid' => "Community {$communityName} exhibits significant aggregated daily demand ({$dailyKwh} kWh/day, {$peakKw} kW peak) across {$householdsWithout} underserved households, {$schoolsCount} school(s), {$healthCount} health facility(ies), and {$businessCount} commercial enterprise(s). Clustered settlement density justifies a centralized Solar Hybrid Mini-Grid with LV distribution network.",
            'productive_use_solar' => "Commercial and agricultural processing loads ({$businessCount} active SMEs/mills) represent a major portion of {$communityName}'s {$dailyKwh} kWh/day demand. A Productive-Use Solar System (PUE) with high-surge three-phase inverter capacity is recommended to power agro-processing and stimulate rural livelihoods.",
            'institutional_solar' => "Critical public institutions ({$schoolsCount} school(s) and {$healthCount} healthcare facility(ies) requiring vaccine cold-chain and lighting) drive the priority load in {$communityName}. Dedicated Institutional Solar Systems with high-reliability LiFePO4 battery storage are recommended.",
            'solar_water_pumping' => "With low dispersed domestic demand ({$dailyKwh} kWh/day) and primary community need centered around water supply and basic utilities, a Solar Water Pumping & Community Charging Hub provides the highest immediate social return.",
            default => "Dispersed settlement pattern in {$communityName} with {$householdsWithout} underserved households and moderate total demand ({$dailyKwh} kWh/day) makes standalone Solar Home Systems (SHS) more cost-effective than constructing an LV distribution grid.",
        };
    }
}
