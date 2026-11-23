<?php

namespace App\Services;

use App\Enums\HotelLicenseStatus;
use App\Enums\InspectionStatus;
use App\Enums\ProjectStatus;
use App\Models\CulturalAsset;
use App\Models\Hotel;
use App\Models\Inspection;
use App\Models\IntangibleHeritage;
use App\Models\Lga;
use App\Models\LicenseApplication;
use App\Models\LicenseFee;
use App\Models\Project;
use App\Models\ProjectExpenditure;
use App\Support\Format;
use InvalidArgumentException;

class ReportingService
{
    /**
     * @return array<string, mixed>
     */
    public function kpiIndicators(): array
    {
        $totalHotels = Hotel::count();
        $licensedHotels = Hotel::where('license_status', HotelLicenseStatus::LICENSED->value)->count();
        $licensedPct = $totalHotels > 0 ? round(($licensedHotels / $totalHotels) * 100, 1) : 0.0;

        $activeProjects = Project::where('status', ProjectStatus::ONGOING->value)->count();
        $totalApprovedBudget = (float) ProjectExpenditure::sum('approved_budget');
        $totalActualSpend = (float) ProjectExpenditure::sum('actual_expenditure');
        $budgetUtilizationPct = $totalApprovedBudget > 0 ? round(($totalActualSpend / $totalApprovedBudget) * 100, 1) : 0.0;

        $inspectionsCompletedMonth = Inspection::where('status', InspectionStatus::COMPLETED->value)
            ->whereYear('completed_date', now()->year)
            ->whereMonth('completed_date', now()->month)
            ->count();

        // 6-month revenue trend
        $months = collect(range(5, 0))->map(fn (int $offset) => now()->subMonths($offset)->format('Y-m'));
        $fees = LicenseFee::whereDate('paid_at', '>=', now()->subMonths(5)->startOfMonth())->get();

        $revenueTrend = $months->mapWithKeys(function (string $ym) use ($fees) {
            $sum = $fees->filter(fn (LicenseFee $f) => $f->paid_at?->format('Y-m') === $ym)->sum('amount');

            return [$ym => (float) $sum];
        });

        // LGA breakdown (top LGAs with assets or hotels)
        $lgaStats = Lga::active()
            ->withCount(['culturalAssets', 'hotels'])
            ->get()
            ->filter(fn (Lga $lga) => $lga->cultural_assets_count > 0 || $lga->hotels_count > 0)
            ->values();

        return [
            'heritage_assets_count' => CulturalAsset::count(),
            'intangible_heritage_count' => IntangibleHeritage::count(),
            'total_hotels' => $totalHotels,
            'licensed_hotels' => $licensedHotels,
            'licensed_percentage' => $licensedPct,
            'active_projects' => $activeProjects,
            'total_projects' => Project::count(),
            'total_approved_budget' => $totalApprovedBudget,
            'total_actual_spend' => $totalActualSpend,
            'budget_utilization_pct' => $budgetUtilizationPct,
            'inspections_completed_month' => $inspectionsCompletedMonth,
            'overdue_inspections' => Inspection::overdue()->count(),
            'revenue_trend_labels' => $revenueTrend->keys()->values()->all(),
            'revenue_trend_values' => $revenueTrend->values()->all(),
            'lga_labels' => $lgaStats->pluck('name')->all(),
            'lga_assets' => $lgaStats->pluck('cultural_assets_count')->all(),
            'lga_hotels' => $lgaStats->pluck('hotels_count')->all(),
        ];
    }

    /**
     * Build a standardized report payload for both PDF and Excel exports.
     *
     * @return array{title: string, subtitle: string, headings: array<int, string>, rows: array<int, array<int, mixed>>, generated_at: string}
     */
    public function buildStandardReport(string $reportKey): array
    {
        return match ($reportKey) {
            'monthly-licensing' => $this->monthlyLicensingReport(),
            'heritage-inventory' => $this->heritageInventoryReport(),
            'project-status' => $this->projectStatusReport(),
            'inspection-rates' => $this->inspectionCompletionReport(),
            default => throw new InvalidArgumentException("Unknown standard report [{$reportKey}]."),
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function monthlyLicensingReport(): array
    {
        $apps = LicenseApplication::with(['hotel.lga', 'licenseType'])->latest()->get();

        return [
            'title' => 'Monthly Hospitality Licensing Summary',
            'subtitle' => 'Kogi State Ministry of Culture and Tourism — Licensing Pipeline & Status Report',
            'headings' => ['Application #', 'Establishment', 'LGA', 'License Type', 'Type', 'Stage', 'License #', 'Expires'],
            'rows' => $apps->map(fn (LicenseApplication $a) => [
                $a->application_number,
                $a->hotel?->name ?? '—',
                $a->hotel?->lga?->name ?? '—',
                $a->licenseType?->name ?? '—',
                $a->application_type,
                $a->status->label(),
                $a->license_number ?? '—',
                Format::date($a->expires_at),
            ])->all(),
            'generated_at' => Format::datetime(now()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function heritageInventoryReport(): array
    {
        $assets = CulturalAsset::with(['lga', 'category'])->orderBy('lga_id')->orderBy('name')->get();

        return [
            'title' => 'Heritage & Tourism Asset Inventory by LGA',
            'subtitle' => 'Kogi State Cultural & Tourism Resource Registry',
            'headings' => ['LGA', 'Senatorial District', 'Asset Name', 'Category', 'Ownership', 'Condition', 'Capacity', 'GPS (Lat, Lng)'],
            'rows' => $assets->map(fn (CulturalAsset $a) => [
                $a->lga?->name ?? '—',
                $a->lga?->senatorial_district ?? '—',
                $a->name,
                $a->category?->name ?? '—',
                $a->ownership_status?->label() ?? '—',
                $a->condition_assessment?->label() ?? '—',
                $a->visitor_capacity ?? '—',
                (string) ($a->location ?? '—'),
            ])->all(),
            'generated_at' => Format::datetime(now()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function projectStatusReport(): array
    {
        $projects = Project::with(['lga', 'expenditures', 'milestones'])->orderBy('status')->get();

        return [
            'title' => 'Ministry Project & Program Status Report',
            'subtitle' => 'Budget Utilization, Variance Flags & Milestone Delivery',
            'headings' => ['Project Title', 'LGA', 'Budget Line', 'Status', 'Approved Budget (₦)', 'Actual Spend (₦)', 'Utilization %', 'Milestones Done'],
            'rows' => $projects->map(fn (Project $p) => [
                $p->title,
                $p->lga?->name ?? 'Statewide',
                $p->budget_line,
                $p->status->label(),
                Format::naira($p->totalApprovedBudget()),
                Format::naira($p->totalActualExpenditure()),
                $p->budgetUtilizationPercent().'%',
                $p->milestones->whereNotNull('completed_at')->count().' / '.$p->milestones->count(),
            ])->all(),
            'generated_at' => Format::datetime(now()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function inspectionCompletionReport(): array
    {
        $inspections = Inspection::with(['hotel.lga', 'assignedOfficer'])->latest('scheduled_date')->get();

        return [
            'title' => 'Hospitality Inspection Completion Rates Report',
            'subtitle' => 'Scheduled, Completed & Overdue Inspection Summary',
            'headings' => ['Hotel', 'LGA', 'Officer', 'Scheduled Date', 'Completed Date', 'Status', 'Verdict', 'Overdue'],
            'rows' => $inspections->map(fn (Inspection $i) => [
                $i->hotel?->name ?? '—',
                $i->hotel?->lga?->name ?? '—',
                $i->assignedOfficer?->name ?? '—',
                Format::date($i->scheduled_date),
                Format::date($i->completed_date),
                $i->status->label(),
                $i->overall_passed === null ? 'Pending' : ($i->overall_passed ? 'Passed' : 'Failed'),
                $i->isOverdue() ? 'YES' : 'No',
            ])->all(),
            'generated_at' => Format::datetime(now()),
        ];
    }
}
