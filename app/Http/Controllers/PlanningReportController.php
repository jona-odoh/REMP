<?php

namespace App\Http\Controllers;

use App\Exports\PriorityCommunitiesExport;
use App\Models\Community;
use App\Models\Lga;
use App\Services\PrioritisationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class PlanningReportController extends Controller
{
    public function __construct(protected PrioritisationService $prioritisationService)
    {
    }

    public function index(Request $request): View
    {
        $lgaId = $request->query('lga_id');
        $priorityFilter = $request->query('priority');
        $solutionFilter = $request->query('solution');

        $communities = $this->getFilteredCommunities($lgaId, $priorityFilter, $solutionFilter);
        $lgas = Lga::withCount('communities')->orderBy('name')->get();

        $summary = [
            'total_communities' => $communities->count(),
            'high_priority' => $communities->filter(fn ($c) => $c->priorityRanking?->priority_level_enum === 'high')->count(),
            'medium_priority' => $communities->filter(fn ($c) => $c->priorityRanking?->priority_level_enum === 'medium')->count(),
            'low_priority' => $communities->filter(fn ($c) => ($c->priorityRanking?->priority_level_enum ?? 'low') === 'low')->count(),
            'total_households_affected' => $communities->sum(fn ($c) => $c->latestAssessment ? $c->latestAssessment->households_without_electricity : ($c->estimated_households ?? 0)),
            'total_daily_kwh' => round($communities->sum(fn ($c) => $c->latestEnergyDemand?->total_daily_kwh ?? 0), 2),
            'total_cost_ngn' => round($communities->sum(fn ($c) => $c->latestRecommendation?->costEstimate?->total_estimated_cost_ngn ?? 0), 2),
        ];

        return view('planning.index', compact('communities', 'lgas', 'summary', 'lgaId', 'priorityFilter', 'solutionFilter'));
    }

    public function recalculateRankings(): RedirectResponse
    {
        $count = $this->prioritisationService->rankAllCommunities();
        return back()->with('success', "Prioritisation rankings recalculated across {$count} communities using the 5-criteria weighted formula.");
    }

    public function exportExcel(Request $request): Response
    {
        $communities = $this->getFilteredCommunities(
            $request->query('lga_id'),
            $request->query('priority'),
            $request->query('solution')
        );

        $export = new PriorityCommunitiesExport($communities);
        $format = strtolower((string) $request->query('format', 'xlsx'));

        // Stream directly from memory via Maatwebsite\Excel::raw() to avoid Windows ZipArchive temp file locks
        try {
            if ($format !== 'csv' && class_exists(\ZipArchive::class)) {
                $rawXlsx = Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
                $filename = 'Kogi-REMP-Priority-Communities-' . date('Y-m-d') . '.xlsx';
                return response($rawXlsx, 200, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                ]);
            }
        } catch (\Throwable) {
            // Fall through to CSV export via Maatwebsite\Excel::raw()
        }

        $rawCsv = Excel::raw($export, \Maatwebsite\Excel\Excel::CSV);
        $filename = 'Kogi-REMP-Priority-Communities-' . date('Y-m-d') . '.csv';

        return response($rawCsv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportPriorityPdf(Request $request): Response
    {
        $lgaId = $request->query('lga_id');
        $priorityFilter = $request->query('priority');
        $communities = $this->getFilteredCommunities($lgaId, $priorityFilter, $request->query('solution'));
        $lga = $lgaId ? Lga::find($lgaId) : null;

        $pdf = Pdf::loadView('planning.pdf-priority', [
            'communities' => $communities,
            'lga' => $lga,
            'priorityFilter' => $priorityFilter,
            'generatedAt' => now()->format('d M Y, H:i'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Kogi-REMP-Planning-Report-' . date('Y-m-d') . '.pdf');
    }

    public function exportCommunityPdf(Community $community): Response
    {
        $community->load([
            'ward.lga',
            'latestAssessment.officer',
            'latestAssessment.householdAssessment',
            'latestAssessment.schools',
            'latestAssessment.healthFacilities',
            'latestAssessment.businesses',
            'latestAssessment.assessmentAppliances.appliance',
            'latestEnergyDemand',
            'latestRecommendation.costEstimate',
            'priorityRanking',
        ]);

        $pdf = Pdf::loadView('planning.pdf-community', [
            'community' => $community,
            'generatedAt' => now()->format('d M Y, H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Kogi-REMP-Community-' . str()->slug($community->name) . '.pdf');
    }

    protected function getFilteredCommunities(?string $lgaId, ?string $priorityFilter, ?string $solutionFilter): Collection
    {
        $query = Community::with([
            'ward.lga',
            'latestAssessment.schools',
            'latestAssessment.healthFacilities',
            'latestAssessment.businesses',
            'latestEnergyDemand',
            'latestRecommendation.costEstimate',
            'priorityRanking',
        ]);

        if ($lgaId) {
            $query->whereHas('ward', fn ($q) => $q->where('lga_id', $lgaId));
        }

        if ($priorityFilter) {
            $query->whereHas('priorityRanking', fn ($q) => $q->where('priority_level_enum', $priorityFilter));
        }

        if ($solutionFilter) {
            $query->whereHas('latestRecommendation', fn ($q) => $q->where('recommended_solution_enum', $solutionFilter));
        }

        return $query->get()->sortByDesc(fn ($c) => $c->priorityRanking?->score ?? 0)->values();
    }
}
