<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PriorityCommunitiesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(protected Collection $communities)
    {
    }

    public function collection(): Collection
    {
        return $this->communities;
    }

    public function headings(): array
    {
        return [
            'Rank #',
            'Community Name',
            'Ward',
            'LGA',
            'Electricity Status',
            'Verified',
            'Households Without Electricity',
            'Schools Assessed',
            'Health Facilities Assessed',
            'Businesses Assessed',
            'Estimated Daily Demand (kWh/day)',
            'Peak Load (kW)',
            'Recommended Solution',
            'Estimated Capacity (kWp)',
            'Indicative Total Cost (NGN)',
            'Priority Score',
            'Priority Level',
        ];
    }

    public function map($community): array
    {
        static $rank = 0;
        $rank++;

        $assessment = $community->latestAssessment;
        $demand = $community->latestEnergyDemand;
        $rec = $community->latestRecommendation;
        $cost = $rec?->costEstimate;
        $priority = $community->priorityRanking;

        return [
            $rank,
            $community->name,
            $community->ward?->name ?? 'N/A',
            $community->ward?->lga?->name ?? 'N/A',
            ucwords(str_replace('_', ' ', (string) $community->electricity_status_enum)),
            $community->is_verified ? 'Yes' : 'No',
            $assessment ? $assessment->households_without_electricity : ($community->estimated_households ?? 0),
            $assessment ? $assessment->schools->count() : 0,
            $assessment ? $assessment->healthFacilities->count() : 0,
            $assessment ? $assessment->businesses->count() : 0,
            $demand ? number_format($demand->total_daily_kwh, 2, '.', '') : '0.00',
            $demand ? number_format($demand->peak_load_kw, 2, '.', '') : '0.00',
            $rec ? $rec->solution_label : 'Pending Assessment',
            $rec ? number_format($rec->estimated_capacity_kw, 2, '.', '') : '0.00',
            $cost ? number_format($cost->total_estimated_cost_ngn, 2, '.', '') : '0.00',
            $priority ? number_format($priority->score, 2, '.', '') : '0.00',
            $priority ? strtoupper($priority->priority_level_enum) : 'LOW',
        ];
    }
}
