<?php

namespace App\Http\Controllers;

use App\Models\Appliance;
use App\Models\CostEstimate;
use App\Models\EnergyDemand;
use App\Models\Lga;
use App\Models\Recommendation;
use App\Services\EnergyCalculationService;
use App\Services\PrioritisationService;
use App\Services\RecommendationAndCostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EnergyAnalysisController extends Controller
{
    public function __construct(
        protected EnergyCalculationService $energyService,
        protected RecommendationAndCostService $recommendationService,
        protected PrioritisationService $prioritisationService
    ) {
    }

    public function index(Request $request): View
    {
        $lgaId = $request->query('lga_id');
        $solutionFilter = $request->query('solution');

        $query = EnergyDemand::with([
            'community.ward.lga',
            'community.priorityRanking',
            'assessment.assessmentAppliances.appliance',
            'recommendation.costEstimate',
            'recommendation.reviewer',
            'calculator',
        ])->latest();

        if ($lgaId) {
            $query->whereHas('community.ward', fn ($q) => $q->where('lga_id', $lgaId));
        }

        if ($solutionFilter) {
            $query->whereHas('recommendation', fn ($q) => $q->where('recommended_solution_enum', $solutionFilter));
        }

        $demands = $query->paginate(12)->withQueryString();
        $lgas = Lga::orderBy('name')->get();
        $appliances = Appliance::orderBy('category')->orderBy('name')->get();

        return view('energy.index', compact('demands', 'lgas', 'appliances', 'lgaId', 'solutionFilter'));
    }

    public function storeAppliance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'rated_power_watts' => ['required', 'integer', 'min:1', 'max:500000'],
            'category' => ['required', 'string', 'max:100'],
        ]);

        Appliance::create($data);

        return back()->with('success', "Appliance '{$data['name']}' ({$data['rated_power_watts']} W) added to standard library.");
    }

    public function recalculateDemand(Request $request, EnergyDemand $energyDemand): RedirectResponse
    {
        $data = $request->validate([
            'simultaneous_usage_factor' => ['required', 'numeric', 'min:0.1', 'max:1.0'],
            'distribution_loss_margin' => ['required', 'numeric', 'min:0.0', 'max:0.5'],
            'override_note' => ['required', 'string', 'max:1000'],
        ]);

        $updatedDemand = $this->energyService->calculateForAssessment(
            $energyDemand->assessment,
            (float) $data['simultaneous_usage_factor'],
            (float) $data['distribution_loss_margin'],
            Auth::id(),
            $data['override_note']
        );

        $this->recommendationService->generateForEnergyDemand($updatedDemand);
        $this->prioritisationService->rankCommunity($updatedDemand->community);

        return back()->with('success', "Energy demand for {$updatedDemand->community->name} recalculated ({$updatedDemand->total_daily_kwh} kWh/day, {$updatedDemand->peak_load_kw} kW peak) with audit log entry.");
    }

    public function updateRecommendationAndCost(Request $request, Recommendation $recommendation): RedirectResponse
    {
        $data = $request->validate([
            'recommended_solution_enum' => ['required', 'in:solar_home_system,solar_mini_grid,institutional_solar,solar_water_pumping,productive_use_solar'],
            'estimated_capacity_kw' => ['required', 'numeric', 'min:0.5', 'max:50000'],
            'justification' => ['required', 'string', 'max:3000'],
            'analyst_notes' => ['nullable', 'string', 'max:2000'],
            'recalculate_costs_automatically' => ['nullable', 'boolean'],
            'solar_panels_cost' => ['nullable', 'numeric', 'min:0'],
            'batteries_cost' => ['nullable', 'numeric', 'min:0'],
            'inverters_cost' => ['nullable', 'numeric', 'min:0'],
            'charge_controllers_cost' => ['nullable', 'numeric', 'min:0'],
            'mounting_structures_cost' => ['nullable', 'numeric', 'min:0'],
            'distribution_cost' => ['nullable', 'numeric', 'min:0'],
            'protection_equipment_cost' => ['nullable', 'numeric', 'min:0'],
            'installation_cost' => ['nullable', 'numeric', 'min:0'],
            'transportation_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $equipment = $recommendation->equipment_requirements_json ?? [];
        $equipment['pv_array_capacity_kwp'] = (float) $data['estimated_capacity_kw'];
        $equipment['solar_panels_550w_mono_count'] = (int) ceil(((float) $data['estimated_capacity_kw'] * 1000) / 550);

        $recommendation->update([
            'recommended_solution_enum' => $data['recommended_solution_enum'],
            'estimated_capacity_kw' => $data['estimated_capacity_kw'],
            'justification' => $data['justification'],
            'analyst_notes' => $data['analyst_notes'],
            'equipment_requirements_json' => $equipment,
            'reviewed_by' => Auth::id(),
        ]);

        if ($request->boolean('recalculate_costs_automatically', false) || !$recommendation->costEstimate) {
            $this->recommendationService->generateCostEstimate($recommendation, $equipment);
        } else {
            $cost = $recommendation->costEstimate;
            $panels = (float) ($data['solar_panels_cost'] ?? $cost->solar_panels_cost);
            $batteries = (float) ($data['batteries_cost'] ?? $cost->batteries_cost);
            $inverters = (float) ($data['inverters_cost'] ?? $cost->inverters_cost);
            $controllers = (float) ($data['charge_controllers_cost'] ?? $cost->charge_controllers_cost);
            $mounting = (float) ($data['mounting_structures_cost'] ?? $cost->mounting_structures_cost);
            $distribution = (float) ($data['distribution_cost'] ?? $cost->distribution_cost);
            $protection = (float) ($data['protection_equipment_cost'] ?? $cost->protection_equipment_cost);
            $installation = (float) ($data['installation_cost'] ?? $cost->installation_cost);
            $transport = (float) ($data['transportation_cost'] ?? $cost->transportation_cost);

            $totalNgn = $panels + $batteries + $inverters + $controllers + $mounting + $distribution + $protection + $installation + $transport;

            $cost->update([
                'solar_panels_cost' => $panels,
                'batteries_cost' => $batteries,
                'inverters_cost' => $inverters,
                'charge_controllers_cost' => $controllers,
                'mounting_structures_cost' => $mounting,
                'distribution_cost' => $distribution,
                'protection_equipment_cost' => $protection,
                'installation_cost' => $installation,
                'transportation_cost' => $transport,
                'total_estimated_cost_ngn' => $totalNgn,
            ]);
        }

        return back()->with('success', "Recommendation & Indicative Cost Estimate for {$recommendation->community->name} updated by Energy Analyst.");
    }
}
