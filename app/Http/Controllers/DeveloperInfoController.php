<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class DeveloperInfoController extends Controller
{
    public function index(): View
    {
        $developer = config('developer');
        $driver = DB::getDriverName();

        $dbLabel = match ($driver) {
            'mysql' => 'MySQL',
            'mariadb' => 'MariaDB',
            'sqlite' => 'SQLite',
            default => strtoupper($driver),
        };

        $dbVersion = '';
        try {
            $dbVersion = match ($driver) {
                'sqlite' => (string) (DB::selectOne('select sqlite_version() as v')?->v ?? ''),
                default => (string) (DB::selectOne('select version() as v')?->v ?? ''),
            };
            // Keep major.minor.patch concise (e.g., 8.0.36 or 10.4.32)
            if (preg_match('/^\d+\.\d+\.\d+/', $dbVersion, $m)) {
                $dbVersion = $m[0];
            }
        } catch (Throwable) {
            $dbVersion = '';
        }

        $tableCount = 0;
        try {
            $tableCount = count(Schema::getTables());
        } catch (Throwable) {
            $tableCount = 0;
        }

        $rawServer = (string) ($_SERVER['SERVER_SOFTWARE'] ?? 'PHP Built-in');
        $webServer = trim(explode('(', $rawServer)[0]);
        if (str_contains($webServer, ' ')) {
            $parts = explode(' ', $webServer);
            $webServer = str_replace('/', ' ', $parts[0]);
        } else {
            $webServer = str_replace('/', ' ', $webServer);
        }

        $diagnostics = [
            'php_version' => PHP_VERSION,
            'memory_limit' => (string) ini_get('memory_limit'),
            'db_engine' => trim($dbLabel.' '.$dbVersion),
            'table_count' => $tableCount,
            'web_server' => $webServer ?: 'Apache / Nginx',
            'max_upload' => (string) ini_get('upload_max_filesize'),
            'laravel_version' => app()->version(),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
        ];

        $modules = [
            ['phase' => 'Phase 1', 'name' => 'Foundation & Governance', 'details' => '8-Role RBAC (Spatie + Policies), User Lifecycle, Immutable Audit Trail, 21 Kogi LGAs & Config Tables'],
            ['phase' => 'Phase 2', 'name' => 'Cultural & Tourism Registry', 'details' => 'SRID 4326 Spatial Assets, Non-Destructive Version History, Intangible Heritage, Bulk Excel Import/Export, Leaflet Map'],
            ['phase' => 'Phase 3', 'name' => 'Hotel & Hospitality Licensing', 'details' => 'Establishment Registry, Enum State Machine + Inspection Gate, Compliance Dashboard, Renewal Alerts, NGN Fees'],
            ['phase' => 'Phase 4', 'name' => 'Project & Program Tracking', 'details' => 'Project Registry, Milestones, Budget vs. Actual Expenditure & Configurable Variance Alerts'],
            ['phase' => 'Phase 5', 'name' => 'Reporting & Decision-Support', 'details' => 'Executive KPI Dashboard (Chart.js v4), In-Browser / PDF / Excel Standard Reports, Ad-Hoc Query Builder'],
            ['phase' => 'Phase 6', 'name' => 'Document & Task Management', 'details' => 'MySQL FULLTEXT Document Repository, Task Assignment & Notification Center, Threaded Internal Notes'],
        ];

        return view('developer.index', compact('developer', 'diagnostics', 'modules'));
    }
}
