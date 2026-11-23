<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Models\Lga;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectExpenditure;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tourismHead = User::where('email', 'tourism.head@ksmct.kogistate.gov.ng')->first() ?? User::first();
        $cultureHead = User::where('email', 'culture.head@ksmct.kogistate.gov.ng')->first() ?? $tourismHead;
        $lgaIds = Lga::pluck('id', 'name');

        // 1. Ongoing Eco-Tourism Project (Within Budget)
        $p1 = Project::firstOrCreate(
            ['title' => 'Mount Patti Eco-Tourism & Viewing Deck Rehabilitation'],
            [
                'description' => 'Rehabilitation of the Mount Patti access road, viewing deck, visitor signage, and solar-powered heritage gallery.',
                'lga_id' => $lgaIds['Lokoja'],
                'budget_line' => 'KG-MCT-CAP-2026-01',
                'funding_source' => 'Kogi State Capital Allocation',
                'implementing_partner' => 'Kogi State Ministry of Works & Local Contractors',
                'start_date' => today()->subMonths(4),
                'end_date' => today()->addMonths(8),
                'status' => ProjectStatus::ONGOING,
                'created_by' => $tourismHead->id,
            ]
        );

        Milestone::firstOrCreate(
            ['project_id' => $p1->id, 'title' => 'Site Survey & Architectural Drawings Approved'],
            [
                'due_date' => today()->subMonths(2),
                'responsible_officer_id' => $tourismHead->id,
                'completed_at' => now()->subMonths(2),
                'notes' => 'Signed off by Permanent Secretary.',
            ]
        );

        Milestone::firstOrCreate(
            ['project_id' => $p1->id, 'title' => 'Viewing Deck Structural Reinforcement'],
            [
                'due_date' => today()->addMonths(2),
                'responsible_officer_id' => $tourismHead->id,
                'notes' => 'Contractor mobilized to site.',
            ]
        );

        ProjectExpenditure::firstOrCreate(
            ['project_id' => $p1->id, 'fiscal_year' => '2026'],
            [
                'approved_budget' => 85000000.00,
                'actual_expenditure' => 76500000.00, // 90% utilization, -10% variance (within 15% threshold)
                'notes' => 'Q1–Q3 certified valuation certificates paid.',
                'recorded_by' => $tourismHead->id,
            ]
        );

        // 2. Ongoing Project with Variance Flag (>15% overspend!)
        $p2 = Project::firstOrCreate(
            ['title' => 'Kogi State Cultural Theatre & Crafts Centre Upgrade'],
            [
                'description' => 'Acoustic modernization, stage lighting, and artisan exhibition stalls in Lokoja.',
                'lga_id' => $lgaIds['Lokoja'],
                'budget_line' => 'KG-MCT-CAP-2026-04',
                'funding_source' => 'State Capital + Federal Tourism Grant',
                'implementing_partner' => 'Confluence Civic Infrastructure Ltd',
                'start_date' => today()->subMonths(6),
                'end_date' => today()->addMonths(3),
                'status' => ProjectStatus::ONGOING,
                'created_by' => $cultureHead->id,
            ]
        );

        Milestone::firstOrCreate(
            ['project_id' => $p2->id, 'title' => 'Main Auditorium Roof & HVAC Overhaul'],
            [
                'due_date' => today()->subDays(10), // Overdue
                'responsible_officer_id' => $cultureHead->id,
                'notes' => 'Delayed due to imported acoustic panel customs clearance.',
            ]
        );

        ProjectExpenditure::firstOrCreate(
            ['project_id' => $p2->id, 'fiscal_year' => '2026'],
            [
                'approved_budget' => 45000000.00,
                'actual_expenditure' => 54900000.00, // +22% variance -> FLAGGED!
                'notes' => 'Price variation caused by foreign exchange adjustments on lighting equipment.',
                'recorded_by' => $cultureHead->id,
            ]
        );

        // 3. Planned Heritage Digitization Program
        $p3 = Project::firstOrCreate(
            ['title' => 'Ovia-Osese & Ebiraland Oral Heritage Digitization Program'],
            [
                'description' => 'Audio-visual archival documentation of Kogi Central intangible heritage festivals.',
                'lga_id' => $lgaIds['Ogori/Magongo'],
                'budget_line' => 'KG-MCT-REC-2026-09',
                'funding_source' => 'Kogi State Ministry Recurrent / UNESCO Technical Support',
                'implementing_partner' => 'Kogi State Council for Arts & Culture',
                'start_date' => today()->addMonth(),
                'end_date' => today()->addMonths(10),
                'status' => ProjectStatus::PLANNED,
                'created_by' => $cultureHead->id,
            ]
        );

        ProjectExpenditure::firstOrCreate(
            ['project_id' => $p3->id, 'fiscal_year' => '2026'],
            [
                'approved_budget' => 18000000.00,
                'actual_expenditure' => 0.00,
                'notes' => 'Awaiting Q4 release.',
                'recorded_by' => $cultureHead->id,
            ]
        );
    }
}
