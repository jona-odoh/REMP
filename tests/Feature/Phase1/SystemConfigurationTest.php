<?php

namespace Tests\Feature\Phase1;

use App\Models\LicenseType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_super_admin_can_manage_system_configuration_tables(): void
    {
        $admin = User::where('email', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('config.asset-categories.store'), [
                'name' => 'Eco-Heritage Trails',
                'description' => 'Guided nature and cultural trails.',
                'sort_order' => 9,
                'is_active' => true,
            ])
            ->assertRedirect(route('config.asset-categories.index'));

        $this->assertDatabaseHas('asset_categories', ['slug' => 'eco-heritage-trails']);

        $licenseType = LicenseType::firstOrFail();

        $this->actingAs($admin)
            ->post(route('config.fee-schedules.store'), [
                'license_type_id' => $licenseType->id,
                'classification_rating' => 'Boutique Hotel',
                'amount' => 180000.00,
                'effective_from' => now()->toDateString(),
                'is_active' => true,
            ])
            ->assertRedirect(route('config.fee-schedules.index'));

        $this->assertDatabaseHas('fee_schedules', [
            'classification_rating' => 'Boutique Hotel',
            'currency' => 'NGN',
        ]);
    }

    public function test_form_request_validation_rejects_invalid_lga_and_fee_inputs(): void
    {
        $admin = User::where('email', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('config.lgas.store'), [
                'name' => 'Lokoja', // duplicate
                'code' => 'KG-LOK', // duplicate
                'senatorial_district' => 'Invalid Zone',
            ])
            ->assertSessionHasErrors(['name', 'code', 'senatorial_district']);

        $this->actingAs($admin)
            ->post(route('config.fee-schedules.store'), [
                'license_type_id' => 999999,
                'classification_rating' => '',
                'amount' => -500,
                'effective_from' => 'not-a-date',
            ])
            ->assertSessionHasErrors(['license_type_id', 'classification_rating', 'amount', 'effective_from']);
    }

    public function test_non_admin_can_view_config_tables_but_cannot_mutate_them(): void
    {
        $viewer = User::where('email', 'viewer@ksmct.kogistate.gov.ng')->firstOrFail();

        $this->actingAs($viewer)->get(route('config.lgas.index'))->assertOk();
        $this->actingAs($viewer)->get(route('config.fee-schedules.index'))->assertOk();

        $this->actingAs($viewer)
            ->post(route('config.asset-categories.store'), [
                'name' => 'Unauthorised Category',
            ])
            ->assertForbidden();
    }
}
