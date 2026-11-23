<?php

namespace Tests\Feature\Phase2;

use App\Models\CulturalAsset;
use App\Models\Lga;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkImportExportAndMapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_bulk_csv_import_imports_valid_rows_and_reports_invalid_and_duplicate_rows(): void
    {
        $cultureHead = User::where('email', 'culture.head@ksmct.kogistate.gov.ng')->firstOrFail();

        $csvContent = implode("\n", [
            'name,category,lga,latitude,longitude,ownership_status,condition_assessment,visitor_capacity,description',
            'World War Cenotaph Lokoja,Historical Monument & Colonial Heritage,Lokoja,7.8012,6.7341,STATE_GOVERNMENT,GOOD,200,Valid new asset',
            'Mount Patti,Natural Landmark & Eco-Tourism Site,Lokoja,7.8167,6.7250,STATE_GOVERNMENT,GOOD,1200,Duplicate of existing Mount Patti',
            'Invalid Row Asset,Unknown Category,FakeLGA,7.80,6.73,BAD_OWNER,BAD_COND,50,Should fail validation',
        ]);

        $file = UploadedFile::fake()->createWithContent('assets_import.csv', $csvContent);

        $response = $this->actingAs($cultureHead)
            ->post(route('culture.assets.import'), ['file' => $file]);

        $response->assertRedirect(route('culture.assets.import.form'));
        $response->assertSessionHas('import_errors');

        $this->assertDatabaseHas('cultural_assets', ['name' => 'World War Cenotaph Lokoja']);
        $this->assertDatabaseMissing('cultural_assets', ['name' => 'Invalid Row Asset']);

        $errors = session('import_errors');
        $this->assertCount(2, $errors);
    }

    public function test_export_and_template_download_succeed(): void
    {
        $cultureHead = User::where('email', 'culture.head@ksmct.kogistate.gov.ng')->firstOrFail();

        $this->actingAs($cultureHead)
            ->get(route('culture.assets.template'))
            ->assertOk();

        $this->actingAs($cultureHead)
            ->get(route('culture.assets.export', ['format' => 'xlsx']))
            ->assertOk();

        $this->actingAs($cultureHead)
            ->get(route('culture.assets.export', ['format' => 'csv']))
            ->assertOk();
    }

    public function test_map_view_renders_and_filters_by_lga(): void
    {
        $viewer = User::where('email', 'viewer@ksmct.kogistate.gov.ng')->firstOrFail();
        $idah = Lga::where('name', 'Idah')->firstOrFail();

        $response = $this->actingAs($viewer)
            ->get(route('culture.assets.map', ['lga_id' => $idah->id]));

        $response->assertOk();
        $response->assertSee('Attah of Igala Royal Palace');
        $response->assertDontSee('Mount Patti');
    }
}
