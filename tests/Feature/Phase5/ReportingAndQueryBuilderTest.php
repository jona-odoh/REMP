<?php

namespace Tests\Feature\Phase5;

use App\Enums\HotelLicenseStatus;
use App\Models\Lga;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportingAndQueryBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_director_can_view_kpi_dashboard_and_download_pdf_and_excel_standard_reports(): void
    {
        $director = User::where('email', 'director@ksmct.kogistate.gov.ng')->firstOrFail();

        $this->actingAs($director)
            ->get(route('reports.kpi'))
            ->assertOk()
            ->assertSee('Documented Heritage Assets')
            ->assertSee('Licensed Hotels');

        foreach (['monthly-licensing', 'heritage-inventory', 'project-status', 'inspection-rates'] as $key) {
            $this->actingAs($director)
                ->get(route('reports.standard.download', [$key, 'pdf']))
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf');

            $this->actingAs($director)
                ->get(route('reports.standard.download', [$key, 'xlsx']))
                ->assertOk();
        }
    }

    public function test_viewer_cannot_export_standard_reports(): void
    {
        $viewer = User::where('email', 'viewer@ksmct.kogistate.gov.ng')->firstOrFail();

        $this->actingAs($viewer)->get(route('reports.standard'))->assertOk();
        $this->actingAs($viewer)
            ->get(route('reports.standard.download', ['monthly-licensing', 'pdf']))
            ->assertForbidden();
    }

    public function test_ad_hoc_query_builder_finds_unlicensed_hotels_in_lokoja_with_capacity_over_20_rooms(): void
    {
        $director = User::where('email', 'director@ksmct.kogistate.gov.ng')->firstOrFail();
        $lokoja = Lga::where('name', 'Lokoja')->firstOrFail();

        $response = $this->actingAs($director)->get(route('reports.query-builder', [
            'module' => 'hotels',
            'run' => 1,
            'lga_id' => $lokoja->id,
            'license_status' => HotelLicenseStatus::UNLICENSED->value,
            'min_rooms' => 20,
        ]));

        $response->assertOk();
        $response->assertSee('Ganaja Executive Motel');
        $response->assertDontSee('Confluence Beach Hotel');
    }
}
