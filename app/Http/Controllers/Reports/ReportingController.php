<?php

namespace App\Http\Controllers\Reports;

use App\Exports\StandardReportExport;
use App\Http\Controllers\Controller;
use App\Services\ReportingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportingController extends Controller
{
    public function __construct(
        private readonly ReportingService $reportingService,
    ) {}

    public function kpiDashboard(Request $request): View
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $kpis = $this->reportingService->kpiIndicators();

        return view('reports.kpi-dashboard', compact('kpis'));
    }

    public function standardReports(Request $request): View
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $reports = [
            ['key' => 'monthly-licensing', 'name' => 'Monthly Licensing Summary', 'description' => 'Complete hospitality licensing pipeline, license numbers, and expiry dates.'],
            ['key' => 'heritage-inventory', 'name' => 'Heritage Asset Inventory by LGA', 'description' => 'All registered cultural & tourism assets grouped across Kogi\'s 21 LGAs.'],
            ['key' => 'project-status', 'name' => 'Project Status Report', 'description' => 'Ministry projects, approved budgets vs. actual expenditure, utilization, and milestones.'],
            ['key' => 'inspection-rates', 'name' => 'Inspection Completion Rates', 'description' => 'Scheduled, completed, passed/failed, and overdue hotel inspections.'],
        ];

        return view('reports.standard', compact('reports'));
    }

    public function showStandardReport(Request $request, string $reportKey): View
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $report = $this->reportingService->buildStandardReport($reportKey);

        return view('reports.show', [
            'reportKey' => $reportKey,
            'report' => $report,
        ]);
    }

    public function downloadStandardReport(Request $request, string $reportKey, string $format): Response|BinaryFileResponse
    {
        abort_unless($request->user()->can('reports.export'), 403);

        $payload = $this->reportingService->buildStandardReport($reportKey);
        $baseFilename = 'ksmct_'.$reportKey.'_'.now()->format('Ymd_His');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.pdf.standard', $payload)
                ->setPaper('a4', 'landscape');

            return $pdf->download($baseFilename.'.pdf');
        }

        if ($format === 'xlsx') {
            return Excel::download(
                new StandardReportExport($payload['title'], $payload['headings'], $payload['rows']),
                $baseFilename.'.xlsx'
            );
        }

        abort(404);
    }
}
