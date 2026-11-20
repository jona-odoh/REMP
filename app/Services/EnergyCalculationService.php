<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\EnergyDemand;
use Illuminate\Support\Facades\Auth;

class EnergyCalculationService
{
    /**
     * Calculate or recalculate energy demand for an assessment.
     *
     * Formula:
     *   Energy (Wh/day) = Power (W) × Quantity × Operating Hours
     *   Total Community Energy Demand (kWh/day) = Sum(Wh/day) / 1,000
     *   Peak Load (kW) = (Total Connected Load in W / 1,000) × Simultaneous Usage Factor
     */
    public function calculateForAssessment(
        Assessment $assessment,
        float $simultaneousUsageFactor = 0.70,
        float $distributionLossMargin = 0.10,
        ?int $userId = null,
        ?string $overrideNote = null
    ): EnergyDemand {
        $assessment->load(['assessmentAppliances.appliance', 'householdAssessment', 'schools', 'healthFacilities', 'businesses']);

        $totalWh = 0.0;
        $totalConnectedLoadWatts = 0.0;

        $breakdown = [
            'household' => ['wh' => 0.0, 'connected_watts' => 0.0, 'items' => 0],
            'school' => ['wh' => 0.0, 'connected_watts' => 0.0, 'items' => 0],
            'hospital' => ['wh' => 0.0, 'connected_watts' => 0.0, 'items' => 0],
            'business' => ['wh' => 0.0, 'connected_watts' => 0.0, 'items' => 0],
        ];

        foreach ($assessment->assessmentAppliances as $item) {
            $watts = $item->rated_power_watts ?: ($item->appliance?->rated_power_watts ?? 0);
            $qty = max(0, (int) $item->quantity);
            $hours = max(0, (float) $item->operating_hours_per_day);

            $dailyWh = $watts * $qty * $hours;
            $connectedW = $watts * $qty;

            if ((float) $item->calculated_daily_energy_wh !== (float) $dailyWh || (int) $item->rated_power_watts !== (int) $watts) {
                $item->update([
                    'rated_power_watts' => $watts,
                    'calculated_daily_energy_wh' => $dailyWh,
                ]);
            }

            $totalWh += $dailyWh;
            $totalConnectedLoadWatts += $connectedW;

            $type = $item->facility_type;
            if (isset($breakdown[$type])) {
                $breakdown[$type]['wh'] += $dailyWh;
                $breakdown[$type]['connected_watts'] += $connectedW;
                $breakdown[$type]['items'] += $qty;
            }
        }

        // Convert to kWh/day including distribution/inverter loss margin
        $baseDailyKwh = $totalWh / 1000.0;
        $totalDailyKwh = round($baseDailyKwh * (1 + $distributionLossMargin), 2);

        // Peak Load in kW
        $connectedLoadKw = $totalConnectedLoadWatts / 1000.0;
        $peakLoadKw = round($connectedLoadKw * $simultaneousUsageFactor, 2);

        // Update household summary record if present
        if ($assessment->householdAssessment) {
            $assessment->householdAssessment->update([
                'estimated_daily_usage_kwh' => round($breakdown['household']['wh'] / 1000.0, 2),
            ]);
        }

        $existingDemand = EnergyDemand::where('assessment_id', $assessment->id)->first();
        $auditLog = $existingDemand?->calculation_assumptions_json['audit_log'] ?? [];
        if ($overrideNote) {
            $auditLog[] = [
                'timestamp' => now()->toIso8601String(),
                'user_id' => $userId ?? Auth::id(),
                'note' => $overrideNote,
                'simultaneous_usage_factor' => $simultaneousUsageFactor,
                'distribution_loss_margin' => $distributionLossMargin,
            ];
        }

        $assumptions = [
            'simultaneous_usage_factor' => $simultaneousUsageFactor,
            'distribution_loss_margin' => $distributionLossMargin,
            'base_daily_kwh' => round($baseDailyKwh, 2),
            'connected_load_kw' => round($connectedLoadKw, 2),
            'facility_breakdown_kwh' => [
                'household' => round($breakdown['household']['wh'] / 1000.0, 2),
                'school' => round($breakdown['school']['wh'] / 1000.0, 2),
                'hospital' => round($breakdown['hospital']['wh'] / 1000.0, 2),
                'business' => round($breakdown['business']['wh'] / 1000.0, 2),
            ],
            'formula' => 'Energy (Wh/day) = Power (W) × Quantity × Operating Hours; Total kWh/day = (Sum Wh / 1000) × (1 + Loss Margin); Peak Load (kW) = Connected Load (kW) × Simultaneous Usage Factor',
            'audit_log' => $auditLog,
        ];

        return EnergyDemand::updateOrCreate(
            ['assessment_id' => $assessment->id],
            [
                'community_id' => $assessment->community_id,
                'total_daily_kwh' => $totalDailyKwh,
                'peak_load_kw' => $peakLoadKw,
                'calculation_assumptions_json' => $assumptions,
                'calculated_by' => $userId ?? Auth::id(),
            ]
        );
    }
}
