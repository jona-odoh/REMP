<?php

namespace Tests\Feature\Phase1;

use App\Enums\AuditAction;
use App\Models\AssetCategory;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_login_and_logout_are_recorded_in_audit_trail(): void
    {
        $officer = User::where('email', 'licensing@ksmct.kogistate.gov.ng')->firstOrFail();

        $this->post(route('login.store'), [
            'email' => $officer->email,
            'password' => 'Password@2026!',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $officer->id,
            'action' => AuditAction::LOGIN->value,
            'entity_id' => $officer->id,
        ]);

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $officer->id,
            'action' => AuditAction::LOGOUT->value,
            'entity_id' => $officer->id,
        ]);
    }

    public function test_crud_operations_log_before_and_after_json_values(): void
    {
        $admin = User::where('email', 'admin')->firstOrFail();
        $this->actingAs($admin);

        $category = AssetCategory::create([
            'name' => 'Waterfalls & Springs',
            'slug' => 'waterfalls-springs',
            'description' => 'Initial description',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => AuditAction::CREATE->value,
            'entity_type' => AssetCategory::class,
            'entity_id' => $category->id,
        ]);

        $category->update(['description' => 'Updated description']);

        $updateLog = AuditLog::where('action', AuditAction::UPDATE->value)
            ->where('entity_type', AssetCategory::class)
            ->where('entity_id', $category->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertSame('Initial description', $updateLog->before_value['description']);
        $this->assertSame('Updated description', $updateLog->after_value['description']);

        $category->delete();

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => AuditAction::DELETE->value,
            'entity_type' => AssetCategory::class,
            'entity_id' => $category->id,
        ]);
    }

    public function test_audit_logs_are_strictly_immutable(): void
    {
        $admin = User::where('email', 'admin')->firstOrFail();
        $this->actingAs($admin);

        AssetCategory::create([
            'name' => 'Festival Grounds',
            'slug' => 'festival-grounds',
            'is_active' => true,
        ]);

        $log = AuditLog::latest('id')->firstOrFail();

        $this->expectException(LogicException::class);
        $log->update(['ip_address' => '10.0.0.1']);
    }

    public function test_audit_log_cannot_be_deleted(): void
    {
        $admin = User::where('email', 'admin')->firstOrFail();
        $this->actingAs($admin);

        AssetCategory::create([
            'name' => 'Craft Villages',
            'slug' => 'craft-villages',
            'is_active' => true,
        ]);

        $log = AuditLog::latest('id')->firstOrFail();

        $this->expectException(LogicException::class);
        $log->delete();
    }

    public function test_audit_trail_access_is_restricted_to_super_admin_and_director(): void
    {
        $admin = User::where('email', 'admin')->firstOrFail();
        $director = User::where('email', 'director@ksmct.kogistate.gov.ng')->firstOrFail();
        $viewer = User::where('email', 'viewer@ksmct.kogistate.gov.ng')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.audit-logs.index'))->assertOk();
        $this->actingAs($director)->get(route('admin.audit-logs.index'))->assertOk();
        $this->actingAs($viewer)->get(route('admin.audit-logs.index'))->assertForbidden();
    }
}
