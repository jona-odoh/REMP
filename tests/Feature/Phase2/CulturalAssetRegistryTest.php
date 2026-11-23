<?php

namespace Tests\Feature\Phase2;

use App\Enums\ConditionAssessment;
use App\Enums\OwnershipStatus;
use App\Models\AssetCategory;
use App\Models\CulturalAsset;
use App\Models\IntangibleHeritage;
use App\Models\Lga;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CulturalAssetRegistryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_demo_seeder_populates_kogi_cultural_assets_and_target_festivals(): void
    {
        $this->assertGreaterThanOrEqual(6, CulturalAsset::count());
        $this->assertDatabaseHas('cultural_assets', ['name' => 'Lord Lugard House & Colonial Office']);
        $this->assertDatabaseHas('cultural_assets', ['name' => 'Mount Patti']);

        $this->assertDatabaseHas('intangible_heritages', ['name' => 'Ovia-Osese Festival']);
        $this->assertDatabaseHas('intangible_heritages', ['name' => 'New Yam Festival (Ogidi Day)']);
        $this->assertDatabaseHas('intangible_heritages', ['name' => 'Ekuechi Festival']);
        $this->assertGreaterThanOrEqual(3, IntangibleHeritage::count());
    }

    public function test_creating_and_updating_asset_preserves_version_history_without_overwriting(): void
    {
        $cultureHead = User::where('email', 'culture.head@ksmct.kogistate.gov.ng')->firstOrFail();
        $category = AssetCategory::firstOrFail();
        $lga = Lga::where('name', 'Kabba/Bunu')->firstOrFail();

        $this->actingAs($cultureHead)
            ->post(route('culture.assets.store'), [
                'name' => 'Sacred Hill of Owe',
                'category_id' => $category->id,
                'lga_id' => $lga->id,
                'latitude' => 7.8290000,
                'longitude' => 6.0780000,
                'ownership_status' => OwnershipStatus::COMMUNITY_TRADITIONAL->value,
                'condition_assessment' => ConditionAssessment::GOOD->value,
                'visitor_capacity' => 250,
                'description' => 'Initial heritage survey.',
            ])
            ->assertRedirect();

        $asset = CulturalAsset::where('name', 'Sacred Hill of Owe')->firstOrFail();
        $this->assertSame(1, $asset->current_version);
        $this->assertFalse($asset->is_approximate_location);
        $this->assertEqualsWithDelta(7.829, $asset->location->latitude, 0.0001);
        $this->assertSame(1, $asset->versions()->count());

        // Edit asset — should append v2 without overwriting v1
        $this->actingAs($cultureHead)
            ->put(route('culture.assets.update', $asset), [
                'name' => 'Sacred Hill of Owe',
                'category_id' => $category->id,
                'lga_id' => $lga->id,
                'latitude' => 7.8290000,
                'longitude' => 6.0780000,
                'ownership_status' => OwnershipStatus::COMMUNITY_TRADITIONAL->value,
                'condition_assessment' => ConditionAssessment::FAIR->value,
                'visitor_capacity' => 300,
                'description' => 'Updated after 2026 field inspection.',
                'change_summary' => 'Condition downgraded to Fair after rain erosion',
            ])
            ->assertRedirect(route('culture.assets.show', $asset));

        $asset->refresh();
        $this->assertSame(2, $asset->current_version);
        $this->assertSame(2, $asset->versions()->count());

        $v1 = $asset->versions()->where('version_number', 1)->firstOrFail();
        $v2 = $asset->versions()->where('version_number', 2)->firstOrFail();
        $this->assertSame('GOOD', $v1->snapshot['condition_assessment']);
        $this->assertSame('FAIR', $v2->snapshot['condition_assessment']);
        $this->assertSame('Condition downgraded to Fair after rain erosion', $v2->change_summary);
    }

    public function test_missing_gps_coordinates_default_to_lga_centroid_and_flag_approximate(): void
    {
        $dataEntry = User::where('email', 'dataentry@ksmct.kogistate.gov.ng')->firstOrFail();
        $category = AssetCategory::firstOrFail();
        $lga = Lga::where('name', 'Okene')->firstOrFail();

        $this->actingAs($dataEntry)
            ->post(route('culture.assets.store'), [
                'name' => 'Ebira Craft Guild Hall',
                'category_id' => $category->id,
                'lga_id' => $lga->id,
                'ownership_status' => OwnershipStatus::COMMUNITY_TRADITIONAL->value,
                'condition_assessment' => ConditionAssessment::GOOD->value,
            ])
            ->assertRedirect();

        $asset = CulturalAsset::where('name', 'Ebira Craft Guild Hall')->firstOrFail();
        $this->assertTrue($asset->is_approximate_location);
        $this->assertEqualsWithDelta((float) $lga->centroid_lat, $asset->location->latitude, 0.001);
    }

    public function test_duplicate_detection_rejects_same_name_and_lga(): void
    {
        $cultureHead = User::where('email', 'culture.head@ksmct.kogistate.gov.ng')->firstOrFail();
        $existing = CulturalAsset::where('name', 'Mount Patti')->firstOrFail();

        $this->actingAs($cultureHead)
            ->post(route('culture.assets.store'), [
                'name' => 'Mount Patti',
                'category_id' => $existing->category_id,
                'lga_id' => $existing->lga_id,
                'ownership_status' => OwnershipStatus::STATE_GOVERNMENT->value,
                'condition_assessment' => ConditionAssessment::GOOD->value,
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_viewer_can_view_assets_but_cannot_create_or_delete(): void
    {
        $viewer = User::where('email', 'viewer@ksmct.kogistate.gov.ng')->firstOrFail();
        $asset = CulturalAsset::firstOrFail();

        $this->actingAs($viewer)->get(route('culture.assets.index'))->assertOk();
        $this->actingAs($viewer)->get(route('culture.assets.show', $asset))->assertOk();
        $this->actingAs($viewer)->get(route('culture.assets.create'))->assertForbidden();
        $this->actingAs($viewer)->delete(route('culture.assets.destroy', $asset))->assertForbidden();
    }
}
