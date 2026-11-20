<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAssessmentRequest;
use App\Models\Appliance;
use App\Models\Assessment;
use App\Models\AssessmentAppliance;
use App\Models\AssessmentBusiness;
use App\Models\AssessmentHealthFacility;
use App\Models\AssessmentHousehold;
use App\Models\AssessmentSchool;
use App\Models\Community;
use App\Models\Lga;
use App\Models\Report;
use App\Services\EnergyCalculationService;
use App\Services\PrioritisationService;
use App\Services\RecommendationAndCostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssessmentController extends Controller
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
        $status = $request->query('status');

        $query = Assessment::with([
            'community.ward.lga',
            'officer',
            'schools',
            'healthFacilities',
            'businesses',
            'energyDemand.recommendation.costEstimate',
        ])->latest();

        if ($lgaId) {
            $query->whereHas('community.ward', fn ($q) => $q->where('lga_id', $lgaId));
        }

        if ($status) {
            $query->where('status_enum', $status);
        }

        $assessments = $query->paginate(15)->withQueryString();
        $lgas = Lga::orderBy('name')->get();

        // Verified communities awaiting assessment
        $verifiedCommunitiesPending = Community::with('ward.lga')
            ->where('is_verified', true)
            ->whereDoesntHave('assessments', fn ($q) => $q->where('status_enum', 'completed'))
            ->orderBy('name')
            ->limit(25)
            ->get();

        return view('assessments.index', compact('assessments', 'lgas', 'verifiedCommunitiesPending', 'lgaId', 'status'));
    }

    public function create(Request $request): View
    {
        $selectedCommunityId = $request->query('community_id');
        // FRD Phase 4: Only verified communities can be assigned for full official assessment (or all communities if admin chooses)
        $communities = Community::with('ward.lga')
            ->orderByDesc('is_verified')
            ->orderBy('name')
            ->get();

        $appliances = Appliance::orderBy('category')->orderBy('name')->get();

        return view('assessments.create', compact('communities', 'appliances', 'selectedCommunityId'));
    }

    public function store(StoreAssessmentRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('assessment-photos', 'public');
        }

        $assessment = DB::transaction(function () use ($data, $photoPath) {
            $assessment = Assessment::create([
                'community_id' => $data['community_id'],
                'officer_id' => Auth::id(),
                'assessment_date' => $data['assessment_date'],
                'grid_connected_boolean' => (bool) $data['grid_connected_boolean'],
                'average_supply_hours' => $data['average_supply_hours'],
                'interruption_frequency' => $data['interruption_frequency'] ?? null,
                'alternative_energy_sources' => $data['alternative_energy_sources'] ?? null,
                'households_with_electricity' => $data['households_with_electricity'],
                'households_without_electricity' => $data['households_without_electricity'],
                'gps_coordinates' => $data['gps_coordinates'] ?? null,
                'photo_path' => $photoPath,
                'status_enum' => $data['status_enum'],
            ]);

            // Step 2: Household Summary
            AssessmentHousehold::create([
                'assessment_id' => $assessment->id,
                'households_assessed_count' => $data['households_assessed_count'] ?? ($data['households_with_electricity'] + $data['households_without_electricity']),
                'households_without_electricity' => $data['households_without_electricity'],
                'existing_sources' => $data['household_existing_sources'] ?? ($data['alternative_energy_sources'] ?? 'Petrol Generators / Kerosene'),
                'common_appliances_json' => ['LED Bulbs', 'Ceiling Fans', 'Phone Chargers', 'Radio/TV'],
                'estimated_daily_usage_kwh' => 0,
            ]);

            // Step 3: Schools
            if (!empty($data['schools'])) {
                foreach ($data['schools'] as $school) {
                    if (empty($school['name'])) {
                        continue;
                    }
                    AssessmentSchool::create([
                        'assessment_id' => $assessment->id,
                        'name' => $school['name'],
                        'location' => $school['location'] ?? null,
                        'school_type' => $school['school_type'] ?? 'Public Primary/Secondary',
                        'classrooms_count' => $school['classrooms_count'] ?? 6,
                        'offices_count' => $school['offices_count'] ?? 2,
                        'equipment_appliances_json' => ['Lighting', 'Computers', 'Fans'],
                        'existing_sources' => $school['existing_sources'] ?? 'None',
                        'operating_hours' => $school['operating_hours'] ?? 8,
                    ]);
                }
            }

            // Step 4: Health Facilities
            if (!empty($data['health_facilities'])) {
                foreach ($data['health_facilities'] as $hf) {
                    if (empty($hf['name'])) {
                        continue;
                    }
                    AssessmentHealthFacility::create([
                        'assessment_id' => $assessment->id,
                        'name' => $hf['name'],
                        'location' => $hf['location'] ?? null,
                        'facility_type' => $hf['facility_type'] ?? 'Primary Health Care Centre (PHC)',
                        'medical_equipment_json' => ['Vaccine Refrigerator', 'Maternity Ward Lighting', 'Microscope'],
                        'refrigeration_requirements' => $hf['refrigeration_requirements'] ?? '24/7 Vaccine Cold Chain Required',
                        'lighting_needs' => $hf['lighting_needs'] ?? 'Emergency Night Maternity & Ward Lighting',
                        'existing_sources' => $hf['existing_sources'] ?? 'Small Petrol Generator',
                    ]);
                }
            }

            // Step 5: Businesses
            if (!empty($data['businesses'])) {
                foreach ($data['businesses'] as $biz) {
                    if (empty($biz['name'])) {
                        continue;
                    }
                    AssessmentBusiness::create([
                        'assessment_id' => $assessment->id,
                        'name' => $biz['name'],
                        'category' => $biz['category'] ?? 'Agro-Processing / SME',
                        'business_type' => $biz['business_type'] ?? 'Retail / Milling',
                        'equipment_appliances_json' => ['Milling Motor', 'Deep Freezer', 'Lighting'],
                        'operating_hours' => $biz['operating_hours'] ?? 8,
                        'estimated_requirement_kwh' => 12.0,
                        'existing_sources' => $biz['existing_sources'] ?? 'Diesel/Petrol Generator',
                    ]);
                }
            }

            // Step 6: Appliances & Equipment Matrix
            if (!empty($data['appliances'])) {
                foreach ($data['appliances'] as $appItem) {
                    $watts = (int) $appItem['rated_power_watts'];
                    $qty = (int) $appItem['quantity'];
                    $hours = (float) $appItem['operating_hours_per_day'];

                    AssessmentAppliance::create([
                        'assessment_id' => $assessment->id,
                        'facility_type' => $appItem['facility_type'],
                        'appliance_id' => $appItem['appliance_id'] ?: null,
                        'custom_appliance_name' => $appItem['custom_appliance_name'] ?? null,
                        'rated_power_watts' => $watts,
                        'quantity' => $qty,
                        'operating_hours_per_day' => $hours,
                        'calculated_daily_energy_wh' => $watts * $qty * $hours,
                    ]);
                }
            }

            // Mark community as verified and update linked public reports to 'assessed'
            $community = Community::findOrFail($data['community_id']);
            $community->update(['is_verified' => true]);

            Report::where('community_id', $community->id)
                ->where('status_enum', 'verified')
                ->update(['status_enum' => 'assessed']);

            return $assessment;
        });

        // Automatically run Energy Calculation, Recommendation, Costing, and Prioritisation pipeline
        $demand = $this->energyService->calculateForAssessment($assessment);
        $this->recommendationService->generateForEnergyDemand($demand);
        $this->prioritisationService->rankCommunity($assessment->community);

        return redirect()
            ->route('communities.show', $assessment->community_id)
            ->with('success', 'Field Assessment recorded and Energy Demand, Renewable Recommendation, Indicative Costing, and Priority Ranking automatically generated.');
    }

    public function show(Assessment $assessment): View
    {
        $assessment->load([
            'community.ward.lga',
            'officer',
            'householdAssessment',
            'schools',
            'healthFacilities',
            'businesses',
            'assessmentAppliances.appliance',
            'energyDemand.recommendation.costEstimate',
        ]);

        return view('assessments.show', compact('assessment'));
    }
}
