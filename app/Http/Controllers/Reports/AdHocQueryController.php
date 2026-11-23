<?php

namespace App\Http\Controllers\Reports;

use App\Enums\ConditionAssessment;
use App\Enums\HotelLicenseStatus;
use App\Enums\OwnershipStatus;
use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use App\Models\CulturalAsset;
use App\Models\Hotel;
use App\Models\Lga;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdHocQueryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $module = in_array($request->string('module')->toString(), ['hotels', 'assets', 'projects'], true)
            ? $request->string('module')->toString()
            : 'hotels';

        $results = collect();
        $executed = $request->boolean('run');

        if ($executed) {
            $results = match ($module) {
                'hotels' => Hotel::query()
                    ->with('lga')
                    ->when($request->filled('lga_id'), fn ($q) => $q->where('lga_id', $request->integer('lga_id')))
                    ->when($request->filled('license_status'), fn ($q) => $q->where('license_status', $request->string('license_status')->toString()))
                    ->when($request->filled('establishment_type'), fn ($q) => $q->where('establishment_type', $request->string('establishment_type')->toString()))
                    ->when($request->filled('min_rooms'), fn ($q) => $q->where('capacity_rooms', '>=', $request->integer('min_rooms')))
                    ->when($request->filled('max_rooms'), fn ($q) => $q->where('capacity_rooms', '<=', $request->integer('max_rooms')))
                    ->orderBy('name')
                    ->get(),

                'assets' => CulturalAsset::query()
                    ->with(['lga', 'category'])
                    ->when($request->filled('lga_id'), fn ($q) => $q->where('lga_id', $request->integer('lga_id')))
                    ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
                    ->when($request->filled('condition'), fn ($q) => $q->where('condition_assessment', $request->string('condition')->toString()))
                    ->when($request->filled('ownership'), fn ($q) => $q->where('ownership_status', $request->string('ownership')->toString()))
                    ->when($request->filled('min_capacity'), fn ($q) => $q->where('visitor_capacity', '>=', $request->integer('min_capacity')))
                    ->orderBy('name')
                    ->get(),

                'projects' => Project::query()
                    ->with(['lga', 'expenditures'])
                    ->when($request->filled('lga_id'), fn ($q) => $q->where('lga_id', $request->integer('lga_id')))
                    ->when($request->filled('project_status'), fn ($q) => $q->where('status', $request->string('project_status')->toString()))
                    ->when($request->filled('funding_source'), fn ($q) => $q->where('funding_source', 'like', '%'.$request->string('funding_source')->toString().'%'))
                    ->orderBy('title')
                    ->get(),
            };
        }

        return view('reports.query-builder', [
            'module' => $module,
            'executed' => $executed,
            'results' => $results,
            'lgas' => Lga::active()->get(),
            'categories' => AssetCategory::active()->get(),
            'hotelStatuses' => HotelLicenseStatus::cases(),
            'establishmentTypes' => ['Hotel', 'Guest House', 'Motel', 'Resort', 'Lodge'],
            'conditions' => ConditionAssessment::cases(),
            'ownerships' => OwnershipStatus::cases(),
            'projectStatuses' => ProjectStatus::cases(),
        ]);
    }
}
