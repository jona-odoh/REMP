<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DeveloperInfoController extends Controller
{
    public function index(): View
    {
        $developer = config('developer');

        $system = [
            'app_name' => config('app.name'),
            'app_version' => $developer['app_version'] ?? '1.0.0',
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'database_driver' => strtoupper(DB::getDriverName()),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'storage_mode' => 'On-Premise Local Filesystem (Data Sovereign)',
        ];

        $modules = [
            ['phase' => 'Phase 1', 'name' => 'Foundation', 'details' => '8-Role RBAC, User Lifecycle, Immutable Audit Trail, 21 Kogi LGAs & System Config'],
            ['phase' => 'Phase 2', 'name' => 'Cultural & Tourism Registry', 'details' => 'SRID 4326 Spatial Assets, Version History, Intangible Heritage, Bulk Excel Import/Export, Leaflet Map'],
            ['phase' => 'Phase 3', 'name' => 'Hotel & Hospitality Licensing', 'details' => 'Establishment Registry, Enum State Machine, Inspections, Compliance Dashboard, Renewal Reminders, NGN Fees'],
            ['phase' => 'Phase 4', 'name' => 'Project & Program Tracking', 'details' => 'Project Registry, Milestones, Budget vs. Actual Expenditure & Variance Alerts'],
            ['phase' => 'Phase 5', 'name' => 'Reporting & Decision-Support', 'details' => 'Executive KPI Dashboard (Chart.js), View/PDF/Excel Standard Reports, Ad-Hoc Query Builder'],
            ['phase' => 'Phase 6', 'name' => 'Document & Task Management', 'details' => 'FULLTEXT Document Repository, Task Assignment & Notification Center, Threaded Internal Notes'],
        ];

        return view('developer.index', compact('developer', 'system', 'modules'));
    }
}
