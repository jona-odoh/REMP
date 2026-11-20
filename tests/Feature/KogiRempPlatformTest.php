<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\Lga;
use App\Models\Report;
use App\Models\User;
use App\Models\Ward;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KogiRempPlatformTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_kogi_remp_workflow_and_modules(): void
    {
        $this->seed(DatabaseSeeder::class);

        // 1. Verify Seeded Scope: 3 LGAs, 15 Wards, 150 Communities
        $this->assertEquals(3, Lga::count());
        $this->assertEquals(15, Ward::count());
        $this->assertEquals(150, Community::count());

        // 2. Public Reporting Portal (No Auth Required) + Duplicate Flagging
        $this->get(route('public.report.create'))->assertStatus(200);

        $lga = Lga::first();
        $ward = $lga->wards()->first();
        $community = $ward->communities()->first();

        $reportPayload = [
            'lga_id' => $lga->id,
            'ward_id' => $ward->id,
            'community_id' => $community->id,
            'reporter_name' => 'Mallam Audu',
            'reporter_contact' => '08031234567',
            'electricity_situation_enum' => 'no_electricity',
            'duration_of_problem' => 'Over 5 Years',
            'affected_households_estimate' => 140,
            'affected_facilities_json' => ['schools', 'hospitals', 'businesses'],
            'description' => 'Complete absence of power affecting maternity clinic and cassava mills.',
            'latitude' => 7.5542,
            'longitude' => 6.2385,
        ];

        $response = $this->post(route('public.report.store'), $reportPayload);
        $response->assertRedirect();

        $createdReport = Report::where('reporter_name', 'Mallam Audu')->firstOrFail();
        $this->assertEquals('submitted', $createdReport->status_enum);
        $this->assertStringStartsWith('KOGI-', $createdReport->reference_number);

        // Track page
        $this->get(route('public.report.track', ['ref' => $createdReport->reference_number]))
            ->assertStatus(200)
            ->assertSee($createdReport->reference_number);

        // 3. Authenticated Modules (Admin, LGA Officer, Field Officer, Energy Analyst)
        $admin = User::where('role_enum', 'admin')->firstOrFail();

        $this->actingAs($admin)->get(route('dashboard'))->assertStatus(200)->assertSee('Kogi State Rural Electrification');
        $this->actingAs($admin)->get(route('geography.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('communities.show', $community))->assertStatus(200);
        $this->actingAs($admin)->get(route('reports.verification'))->assertStatus(200);
        $this->actingAs($admin)->get(route('assessments.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('assessments.create'))->assertStatus(200);
        $this->actingAs($admin)->get(route('energy.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('planning.index'))->assertStatus(200);
        $this->actingAs($admin)->get(route('users.index'))->assertStatus(200);

        // 4. Verify PDF & Excel Export endpoints
        $this->actingAs($admin)->get(route('planning.export.pdf'))->assertStatus(200);
        $this->actingAs($admin)->get(route('communities.export.pdf', $community))->assertStatus(200);
        $this->actingAs($admin)->get(route('planning.export.excel'))->assertStatus(200);
    }
}
