<?php

namespace Tests\Feature\Phase2;

use App\Enums\AuditAction;
use App\Enums\SafeguardingStatus;
use App\Models\CulturalAsset;
use App\Models\Document;
use App\Models\IntangibleHeritage;
use App\Models\Lga;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IntangibleHeritageAndDocumentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_culture_head_can_create_and_update_intangible_heritage(): void
    {
        $cultureHead = User::where('email', 'culture.head@ksmct.kogistate.gov.ng')->firstOrFail();
        $lga = Lga::where('name', 'Idah')->firstOrFail();

        $this->actingAs($cultureHead)
            ->post(route('culture.heritage.store'), [
                'name' => 'Ocho Festival',
                'lga_id' => $lga->id,
                'festival_season' => 'March (Pre-Planting)',
                'participating_communities' => 'Idah and Igala Kingdom communities',
                'historical_description' => 'Traditional Igala pre-planting ritual and royal blessing ceremony.',
                'safeguarding_status' => SafeguardingStatus::URGENT_SAFEGUARDING->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('intangible_heritages', [
            'name' => 'Ocho Festival',
            'safeguarding_status' => SafeguardingStatus::URGENT_SAFEGUARDING->value,
        ]);
    }

    public function test_polymorphic_document_upload_download_audit_and_delete_on_local_disk(): void
    {
        Storage::fake('local');

        $cultureHead = User::where('email', 'culture.head@ksmct.kogistate.gov.ng')->firstOrFail();
        $asset = CulturalAsset::firstOrFail();

        $file = UploadedFile::fake()->create('mount_patti_survey.pdf', 120, 'application/pdf');

        $this->actingAs($cultureHead)
            ->post(route('documents.store'), [
                'documentable_type' => 'cultural_asset',
                'documentable_id' => $asset->id,
                'title' => 'Mount Patti Conservation Survey 2026',
                'category' => 'Survey / Map',
                'file' => $file,
            ])
            ->assertRedirect();

        $document = Document::where('title', 'Mount Patti Conservation Survey 2026')->firstOrFail();
        $this->assertSame(CulturalAsset::class, $document->documentable_type);
        $this->assertSame($asset->id, $document->documentable_id);
        Storage::disk('local')->assertExists($document->file_path);

        // Download must log AuditAction::DOWNLOAD
        $this->actingAs($cultureHead)
            ->get(route('documents.download', $document))
            ->assertOk();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $cultureHead->id,
            'action' => AuditAction::DOWNLOAD->value,
            'entity_type' => Document::class,
            'entity_id' => $document->id,
        ]);

        // Delete must remove file from local disk
        $path = $document->file_path;
        $this->actingAs($cultureHead)
            ->delete(route('documents.destroy', $document))
            ->assertRedirect();

        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    }

    public function test_document_upload_rejects_disallowed_executable_file_types(): void
    {
        Storage::fake('local');
        $cultureHead = User::where('email', 'culture.head@ksmct.kogistate.gov.ng')->firstOrFail();
        $heritage = IntangibleHeritage::firstOrFail();

        $exe = UploadedFile::fake()->create('malware.exe', 50, 'application/x-msdownload');

        $this->actingAs($cultureHead)
            ->post(route('documents.store'), [
                'documentable_type' => 'intangible_heritage',
                'documentable_id' => $heritage->id,
                'title' => 'Disallowed File',
                'file' => $exe,
            ])
            ->assertSessionHasErrors('file');
    }
}
