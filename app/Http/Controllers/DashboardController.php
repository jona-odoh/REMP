<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use App\Models\AuditLog;
use App\Models\CulturalAsset;
use App\Models\FeeSchedule;
use App\Models\InspectionChecklistTemplate;
use App\Models\IntangibleHeritage;
use App\Models\Lga;
use App\Models\LicenseType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'active_users' => User::where('is_active', true)->count(),
            'total_users' => User::count(),
            'lgas' => Lga::where('is_active', true)->count(),
            'cultural_assets' => CulturalAsset::count(),
            'intangible_heritages' => IntangibleHeritage::count(),
            'asset_categories' => AssetCategory::where('is_active', true)->count(),
            'license_types' => LicenseType::where('is_active', true)->count(),
            'fee_schedules' => FeeSchedule::where('is_active', true)->count(),
            'checklist_items' => InspectionChecklistTemplate::where('is_active', true)->count(),
            'audit_events_today' => AuditLog::whereDate('created_at', today())->count(),
        ];

        $recentAudits = $user->can('viewAny', AuditLog::class)
            ? AuditLog::with('user')->latest('created_at')->limit(8)->get()
            : collect();

        return view('dashboard', compact('stats', 'recentAudits'));
    }
}
