<?php

namespace Database\Seeders;

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
use App\Models\User;
use App\Models\Ward;
use App\Services\EnergyCalculationService;
use App\Services\PrioritisationService;
use App\Services\RecommendationAndCostService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Spatie Roles & Permissions
        $roles = ['admin', 'field_officer', 'lga_officer', 'energy_analyst', 'stakeholder_viewer'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        $permissions = [
            'manage_geography',
            'verify_reports',
            'conduct_assessments',
            'analyze_energy_and_costs',
            'view_dashboard_and_reports',
            'manage_users',
        ];
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        // 2. Seed the 3 Selected LGAs in Kogi State (Okene, Idah, Kabba/Bunu) + 5 Wards per LGA + 10 Communities per Ward (150 Communities)
        $lgaDefinitions = [
            [
                'name' => 'Okene',
                'code' => 'KOG-OKN',
                'base_lat' => 7.5520,
                'base_lng' => 6.2350,
                'wards' => ['Bariki', 'Otutu', 'Orietesu', 'Obehira-Eba', 'Agassa'],
                'prefixes' => ['Obehira', 'Upogoro', 'Agassa', 'Idoji', 'Ozuwaya', 'Inike', 'Ohiana', 'Otutu', 'abuga', 'Okengwe'],
            ],
            [
                'name' => 'Idah',
                'code' => 'KOG-IDH',
                'base_lat' => 7.1135,
                'base_lng' => 6.7380,
                'wards' => ['Ega', 'Igalamela', 'Owoliapa', 'Sabon Gari', 'Ukwaja'],
                'prefixes' => ['Ichala', 'Ede-Alaba', 'Inachalo', 'Okenya', 'Alla', 'Ukwaja', 'Igalogba', 'Ogegele', 'Ojebe', 'Angwa'],
            ],
            [
                'name' => 'Kabba/Bunu',
                'code' => 'KOG-KBB',
                'base_lat' => 7.8280,
                'base_lng' => 6.0745,
                'wards' => ['Asuta', 'Odo-Ape', 'Oke-Offin', 'Egbeda', 'Aiyeteju'],
                'prefixes' => ['Odo-Ape', 'Kakun', 'Egunbe', 'Okedayo', 'Apaa-Bunu', 'Akpameta', 'Oke-Bukun', 'Iluke', 'Aiyegunle', 'Odolu'],
            ],
        ];

        $createdLgas = [];
        $allCommunities = [];

        foreach ($lgaDefinitions as $lgaDef) {
            $lga = Lga::create([
                'name' => $lgaDef['name'],
                'code' => $lgaDef['code'],
            ]);
            $createdLgas[$lga->name] = $lga;

            foreach ($lgaDef['wards'] as $wIndex => $wardName) {
                $ward = Ward::create([
                    'lga_id' => $lga->id,
                    'name' => $wardName,
                ]);

                // 10 Communities per Ward (total 50 per LGA = 150 communities)
                for ($cIndex = 1; $cIndex <= 10; $cIndex++) {
                    $prefix = $lgaDef['prefixes'][$cIndex - 1];
                    $commName = "{$prefix} ({$wardName} {$cIndex})";

                    // Deterministic geographic spread around LGA center in Kogi State
                    $latOffset = (($wIndex - 2) * 0.045) + (($cIndex - 5) * 0.012);
                    $lngOffset = (($wIndex - 2) * 0.038) + ((($cIndex * 3) % 7 - 3) * 0.014);

                    $statuses = ['no_electricity', 'no_electricity', 'unreliable', 'inadequate', 'reliable'];
                    $status = $statuses[($wIndex + $cIndex) % count($statuses)];

                    $households = 45 + (($wIndex * 23 + $cIndex * 19) % 210);
                    $isVerified = ($cIndex <= 7); // 70% verified, 30% reported leads

                    $community = Community::create([
                        'ward_id' => $ward->id,
                        'name' => $commName,
                        'description' => "Rural community in {$wardName} Ward, {$lga->name} LGA of Kogi State engaged in farming, agro-processing, and local trade.",
                        'latitude' => round($lgaDef['base_lat'] + $latOffset, 6),
                        'longitude' => round($lgaDef['base_lng'] + $lngOffset, 6),
                        'estimated_population' => $households * 6,
                        'estimated_households' => $households,
                        'electricity_status_enum' => $status,
                        'is_verified' => $isVerified,
                    ]);

                    $allCommunities[] = $community;
                }
            }
        }

        // 3. Create Official Users for Each Role
        $admin = User::create([
            'name' => 'Engr. Suleiman Adavize (System Admin)',
            'email' => 'admin@kogiremp.gov.ng',
            'password' => Hash::make('password'),
            'role_enum' => 'admin',
            'lga_id' => null,
        ]);
        $admin->assignRole('admin');

        $fieldOfficer = User::create([
            'name' => 'Yakubu Ojonugwa (Field Data Collector)',
            'email' => 'field@kogiremp.gov.ng',
            'password' => Hash::make('password'),
            'role_enum' => 'field_officer',
            'lga_id' => $createdLgas['Okene']->id,
        ]);
        $fieldOfficer->assignRole('field_officer');

        $lgaOfficer = User::create([
            'name' => 'Hon.Momohjimoh Bello (LGA Officer)',
            'email' => 'lga@kogiremp.gov.ng',
            'password' => Hash::make('password'),
            'role_enum' => 'lga_officer',
            'lga_id' => $createdLgas['Okene']->id,
        ]);
        $lgaOfficer->assignRole('lga_officer');

        $energyAnalyst = User::create([
            'name' => 'Dr. Funmilayo Olorunfemi (Energy Analyst)',
            'email' => 'analyst@kogiremp.gov.ng',
            'password' => Hash::make('password'),
            'role_enum' => 'energy_analyst',
            'lga_id' => null,
        ]);
        $energyAnalyst->assignRole('energy_analyst');

        $stakeholder = User::create([
            'name' => 'Permanent Secretary Ministry of Energy (Stakeholder Viewer)',
            'email' => 'stakeholder@kogiremp.gov.ng',
            'password' => Hash::make('password'),
            'role_enum' => 'stakeholder_viewer',
            'lga_id' => null,
        ]);
        $stakeholder->assignRole('stakeholder_viewer');

        // 4. Seed Common Electrical Appliances Library
        $applianceSpecs = [
            ['name' => 'LED Lighting Bulb (10W)', 'rated_power_watts' => 10, 'category' => 'household'],
            ['name' => 'Phone & Lantern Charging Socket', 'rated_power_watts' => 15, 'category' => 'household'],
            ['name' => 'Ceiling / Standing Fan', 'rated_power_watts' => 65, 'category' => 'household'],
            ['name' => 'LED Television (32-inch)', 'rated_power_watts' => 75, 'category' => 'household'],
            ['name' => 'Domestic Refrigerator / Freezer', 'rated_power_watts' => 180, 'category' => 'household'],
            ['name' => 'Classroom Lighting & Fan Unit', 'rated_power_watts' => 85, 'category' => 'school'],
            ['name' => 'School Desktop Computer Lab PC', 'rated_power_watts' => 150, 'category' => 'school'],
            ['name' => 'Solar Vaccine Refrigerator (WHO PQS)', 'rated_power_watts' => 140, 'category' => 'hospital'],
            ['name' => 'Maternity & Ward Medical Lighting', 'rated_power_watts' => 60, 'category' => 'hospital'],
            ['name' => 'Laboratory Microscope & Centrifuge', 'rated_power_watts' => 250, 'category' => 'hospital'],
            ['name' => 'Cassava / Grain Milling Machine (3.5kW)', 'rated_power_watts' => 3500, 'category' => 'business'],
            ['name' => 'Commercial Cold-Room / Deep Freezer', 'rated_power_watts' => 450, 'category' => 'business'],
            ['name' => 'Welding & Metal Fabrication Unit', 'rated_power_watts' => 2800, 'category' => 'business'],
            ['name' => 'Submersible Borehole Water Pump', 'rated_power_watts' => 1500, 'category' => 'business'],
        ];

        $appliances = [];
        foreach ($applianceSpecs as $spec) {
            $appliances[$spec['name']] = Appliance::create($spec);
        }

        // 5. Seed Public Electricity Reports across the 3 LGAs
        $reportStatuses = ['submitted', 'under_review', 'verified', 'assessed', 'rejected'];
        $situations = ['no_electricity', 'irregular', 'inadequate', 'partial'];

        foreach (array_slice($allCommunities, 0, 24) as $idx => $comm) {
            $ward = $comm->ward;
            $repStatus = $reportStatuses[$idx % count($reportStatuses)];
            $sit = $situations[$idx % count($situations)];

            Report::create([
                'reference_number' => sprintf('KOGI-2026-%04d', 1001 + $idx),
                'lga_id' => $ward->lga_id,
                'ward_id' => $ward->id,
                'community_id' => $comm->id,
                'reporter_name' => 'Community Development Association Rep #' . ($idx + 1),
                'reporter_contact' => '0803' . rand(1000000, 9999999),
                'electricity_situation_enum' => $sit,
                'duration_of_problem' => $sit === 'no_electricity' ? 'Never Electrified (10+ Years)' : 'Over 3 Years',
                'affected_households_estimate' => $comm->estimated_households,
                'affected_facilities_json' => ['schools', 'hospitals', 'businesses'],
                'description' => "Residents, primary health clinic, and agro-processing mills in {$comm->name} suffer from {$sit} supply, forcing reliance on expensive petrol and diesel generators.",
                'latitude' => $comm->latitude,
                'longitude' => $comm->longitude,
                'status_enum' => $repStatus,
                'verification_comments' => in_array($repStatus, ['verified', 'assessed'])
                    ? 'Verified by LGA Officer with Ward Head confirmation; assigned for detailed energy assessment.'
                    : ($repStatus === 'rejected' ? 'Duplicate report merged with primary ward submission.' : null),
                'verified_by' => in_array($repStatus, ['verified', 'assessed', 'rejected']) ? $lgaOfficer->id : null,
                'verified_at' => in_array($repStatus, ['verified', 'assessed', 'rejected']) ? now()->subDays(rand(1, 10)) : null,
                'assigned_officer_id' => $fieldOfficer->id,
            ]);
        }

        // 6. Seed Comprehensive Field Assessments, Energy Demands, Recommendations, Cost Estimates & Priority Rankings
        $energyService = app(EnergyCalculationService::class);
        $recService = app(RecommendationAndCostService::class);
        $prioService = app(PrioritisationService::class);

        // Conduct detailed assessments for 45 verified communities across the 3 LGAs (15 per LGA)
        $assessedSelection = collect($allCommunities)
            ->where('is_verified', true)
            ->groupBy(fn ($c) => $c->ward->lga_id)
            ->flatMap(fn ($group) => $group->take(15));

        foreach ($assessedSelection as $idx => $comm) {
            $hhTotal = $comm->estimated_households;
            $isOffGrid = $comm->electricity_status_enum === 'no_electricity';
            $hhWithout = $isOffGrid ? $hhTotal : (int) round($hhTotal * 0.78);
            $hhWith = max(0, $hhTotal - $hhWithout);

            $assessment = Assessment::create([
                'community_id' => $comm->id,
                'officer_id' => $fieldOfficer->id,
                'assessment_date' => now()->subDays(($idx % 20) + 1)->toDateString(),
                'grid_connected_boolean' => !$isOffGrid,
                'average_supply_hours' => $isOffGrid ? 0 : (($idx % 4) + 1.5),
                'interruption_frequency' => $isOffGrid ? 'No Grid Connection' : 'Daily Multi-Hour Blackouts',
                'alternative_energy_sources' => 'Petrol Generators, Kerosene Lamps, Dry-Cell Torches',
                'households_with_electricity' => $hhWith,
                'households_without_electricity' => $hhWithout,
                'gps_coordinates' => "{$comm->latitude}, {$comm->longitude}",
                'status_enum' => 'completed',
            ]);

            AssessmentHousehold::create([
                'assessment_id' => $assessment->id,
                'households_assessed_count' => $hhTotal,
                'households_without_electricity' => $hhWithout,
                'existing_sources' => 'Petrol Generators & Kerosene',
                'common_appliances_json' => ['LED Bulbs', 'Fans', 'Phone Chargers', 'TV/Radio'],
                'estimated_daily_usage_kwh' => 0,
            ]);

            // Add 1 or 2 Schools
            $schoolsToCreate = ($idx % 3 === 0) ? 2 : 1;
            for ($s = 1; $s <= $schoolsToCreate; $s++) {
                AssessmentSchool::create([
                    'assessment_id' => $assessment->id,
                    'name' => "{$comm->name} Public " . ($s === 1 ? 'Primary School' : 'Grammar School'),
                    'location' => $comm->name,
                    'school_type' => $s === 1 ? 'Public Primary' : 'Public Secondary',
                    'classrooms_count' => 6 + ($s * 2),
                    'offices_count' => 2,
                    'equipment_appliances_json' => ['Lighting', 'Fans', 'Computers'],
                    'existing_sources' => 'None',
                    'operating_hours' => 7,
                ]);
            }

            // Add 1 Health Facility for most communities
            if ($idx % 4 !== 3) {
                AssessmentHealthFacility::create([
                    'assessment_id' => $assessment->id,
                    'name' => "{$comm->name} Primary Health Care Centre (PHC)",
                    'location' => $comm->name,
                    'facility_type' => 'Primary Health Centre',
                    'medical_equipment_json' => ['Solar Vaccine Refrigerator', 'Maternity Delivery Lighting', 'Microscope'],
                    'refrigeration_requirements' => '24/7 Vaccine Cold-Chain Storage (2°C to 8°C)',
                    'lighting_needs' => '24-Hour Emergency Maternity & Ward Lighting',
                    'existing_sources' => 'Small Petrol Generator',
                ]);
            }

            // Add 1 to 3 Businesses (Grain Mills, Cold Storage, Welding)
            $bizCount = ($idx % 3) + 1;
            $bizTemplates = [
                ['name' => 'Agro-Allied Cassava & Grain Mill', 'cat' => 'Agricultural Processing', 'type' => 'Grain Mill', 'hrs' => 6],
                ['name' => 'Community Cold Storage & Fish Hub', 'cat' => 'Cold Chain / Retail', 'type' => 'Cold Storage', 'hrs' => 14],
                ['name' => 'Metal Welding & Agro-Fabrication Shop', 'cat' => 'Artisan / SME', 'type' => 'Welding Workshop', 'hrs' => 5],
            ];
            for ($b = 0; $b < $bizCount; $b++) {
                $tpl = $bizTemplates[$b];
                AssessmentBusiness::create([
                    'assessment_id' => $assessment->id,
                    'name' => "{$comm->name} {$tpl['name']}",
                    'category' => $tpl['cat'],
                    'business_type' => $tpl['type'],
                    'equipment_appliances_json' => ['Motor / Compressor', 'Security Lighting'],
                    'operating_hours' => $tpl['hrs'],
                    'estimated_requirement_kwh' => 15.0,
                    'existing_sources' => 'Diesel Generator',
                ]);
            }

            // Create Appliance Load Records for Energy Calculation Engine
            $loadRows = [
                ['type' => 'household', 'app' => $appliances['LED Lighting Bulb (10W)'], 'qty' => $hhWithout * 4, 'hrs' => 6],
                ['type' => 'household', 'app' => $appliances['Ceiling / Standing Fan'], 'qty' => (int) round($hhWithout * 1.2), 'hrs' => 6],
                ['type' => 'household', 'app' => $appliances['LED Television (32-inch)'], 'qty' => (int) round($hhWithout * 0.6), 'hrs' => 4],
                ['type' => 'school', 'app' => $appliances['Classroom Lighting & Fan Unit'], 'qty' => $schoolsToCreate * 8, 'hrs' => 6],
                ['type' => 'school', 'app' => $appliances['School Desktop Computer Lab PC'], 'qty' => $schoolsToCreate * 5, 'hrs' => 5],
            ];

            if ($idx % 4 !== 3) {
                $loadRows[] = ['type' => 'hospital', 'app' => $appliances['Solar Vaccine Refrigerator (WHO PQS)'], 'qty' => 2, 'hrs' => 24];
                $loadRows[] = ['type' => 'hospital', 'app' => $appliances['Maternity & Ward Medical Lighting'], 'qty' => 10, 'hrs' => 12];
            }

            if ($bizCount >= 1) {
                $loadRows[] = ['type' => 'business', 'app' => $appliances['Cassava / Grain Milling Machine (3.5kW)'], 'qty' => 1, 'hrs' => 5];
            }
            if ($bizCount >= 2) {
                $loadRows[] = ['type' => 'business', 'app' => $appliances['Commercial Cold-Room / Deep Freezer'], 'qty' => 3, 'hrs' => 14];
            }
            if ($bizCount >= 3) {
                $loadRows[] = ['type' => 'business', 'app' => $appliances['Welding & Metal Fabrication Unit'], 'qty' => 1, 'hrs' => 4];
            }

            foreach ($loadRows as $lr) {
                $w = $lr['app']->rated_power_watts;
                $q = max(1, (int) $lr['qty']);
                $h = (float) $lr['hrs'];

                AssessmentAppliance::create([
                    'assessment_id' => $assessment->id,
                    'facility_type' => $lr['type'],
                    'appliance_id' => $lr['app']->id,
                    'custom_appliance_name' => $lr['app']->name,
                    'rated_power_watts' => $w,
                    'quantity' => $q,
                    'operating_hours_per_day' => $h,
                    'calculated_daily_energy_wh' => $w * $q * $h,
                ]);
            }

            // Run Energy Calculation & Recommendation Pipeline
            $demand = $energyService->calculateForAssessment($assessment, 0.70, 0.10, $energyAnalyst->id);
            $recService->generateForEnergyDemand($demand, null, null, $energyAnalyst->id);
        }

        // Rank all 150 communities
        $prioService->rankAllCommunities();
    }
}
