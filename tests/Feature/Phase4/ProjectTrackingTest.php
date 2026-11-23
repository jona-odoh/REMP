<?php

namespace Tests\Feature\Phase4;

use App\Enums\ProjectStatus;
use App\Models\Lga;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectExpenditure;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_demo_seeder_populates_projects_and_flags_variance_above_threshold(): void
    {
        $this->assertGreaterThanOrEqual(3, Project::count());

        $flagged = Project::where('title', 'Kogi State Cultural Theatre & Crafts Centre Upgrade')
            ->with('expenditures')
            ->firstOrFail();

        $this->assertTrue($flagged->hasVarianceAlert());
        $this->assertTrue($flagged->expenditures->first()->variance_flag);

        $withinBudget = Project::where('title', 'Mount Patti Eco-Tourism & Viewing Deck Rehabilitation')
            ->with('expenditures')
            ->firstOrFail();

        $this->assertFalse($withinBudget->hasVarianceAlert());
    }

    public function test_tourism_head_can_create_project_add_milestone_and_record_expenditure(): void
    {
        $tourismHead = User::where('email', 'tourism.head@ksmct.kogistate.gov.ng')->firstOrFail();
        $lga = Lga::where('name', 'Dekina')->firstOrFail();

        $this->actingAs($tourismHead)
            ->post(route('projects.store'), [
                'title' => 'Ofejiji Waterfall Access Road',
                'lga_id' => $lga->id,
                'budget_line' => 'KG-MCT-CAP-2026-11',
                'funding_source' => 'Kogi State Capital Allocation',
                'implementing_partner' => 'Kogi Road Maintenance Agency',
                'start_date' => today()->toDateString(),
                'end_date' => today()->addMonths(6)->toDateString(),
                'status' => ProjectStatus::PLANNED->value,
            ])
            ->assertRedirect();

        $project = Project::where('title', 'Ofejiji Waterfall Access Road')->firstOrFail();

        // Add Milestone & toggle complete
        $this->actingAs($tourismHead)
            ->post(route('projects.milestones.store', $project), [
                'title' => 'Environmental Impact Assessment',
                'due_date' => today()->addMonth()->toDateString(),
                'responsible_officer_id' => $tourismHead->id,
            ])
            ->assertRedirect();

        $milestone = Milestone::where('project_id', $project->id)->firstOrFail();
        $this->assertFalse($milestone->isCompleted());

        $this->actingAs($tourismHead)
            ->post(route('projects.milestones.toggle', [$project, $milestone]))
            ->assertRedirect();

        $this->assertTrue($milestone->fresh()->isCompleted());

        // Record Expenditure with >15% overspend -> computed variance_flag = true
        $this->actingAs($tourismHead)
            ->post(route('projects.expenditures.store', $project), [
                'fiscal_year' => '2026',
                'approved_budget' => 10000000.00,
                'actual_expenditure' => 12500000.00,
            ])
            ->assertRedirect();

        $exp = ProjectExpenditure::where('project_id', $project->id)->firstOrFail();
        $this->assertTrue($exp->variance_flag);
        $this->assertSame(25.0, $exp->variance_percent);
    }

    public function test_viewer_cannot_create_projects_or_expenditures(): void
    {
        $viewer = User::where('email', 'viewer@ksmct.kogistate.gov.ng')->firstOrFail();
        $project = Project::firstOrFail();

        $this->actingAs($viewer)->get(route('projects.index'))->assertOk();
        $this->actingAs($viewer)->get(route('projects.create'))->assertForbidden();
        $this->actingAs($viewer)->post(route('projects.expenditures.store', $project), [
            'fiscal_year' => '2026',
            'approved_budget' => 1000,
            'actual_expenditure' => 1000,
        ])->assertForbidden();
    }
}
