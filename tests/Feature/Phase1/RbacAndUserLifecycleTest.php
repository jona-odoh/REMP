<?php

namespace Tests\Feature\Phase1;

use App\Enums\AuditAction;
use App\Enums\Department;
use App\Enums\RoleName;
use App\Models\AuditLog;
use App\Models\Lga;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RbacAndUserLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_seeder_populates_all_8_roles_and_21_kogi_lgas(): void
    {
        $this->assertSame(21, Lga::count());
        $this->assertDatabaseHas('lgas', ['name' => 'Lokoja', 'senatorial_district' => 'Kogi West']);
        $this->assertDatabaseHas('lgas', ['name' => 'Ogori/Magongo', 'senatorial_district' => 'Kogi Central']);
        $this->assertDatabaseHas('lgas', ['name' => 'Idah', 'senatorial_district' => 'Kogi East']);

        foreach (RoleName::cases() as $role) {
            $this->assertDatabaseHas('roles', ['name' => $role->value]);
        }
    }

    public function test_super_admin_can_create_staff_user_with_role_and_department(): void
    {
        $admin = User::where('email', 'admin@ksmct.kogistate.gov.ng')->firstOrFail();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Adebayo Bello',
            'email' => 'adebayo.bello@ksmct.kogistate.gov.ng',
            'staff_id' => 'KSMCT/CUL/099',
            'phone' => '+234 803 111 2222',
            'department' => Department::CULTURE->value,
            'role' => RoleName::DATA_ENTRY->value,
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'must_change_password' => true,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $created = User::where('email', 'adebayo.bello@ksmct.kogistate.gov.ng')->firstOrFail();
        $this->assertTrue($created->hasRole(RoleName::DATA_ENTRY->value));
        $this->assertTrue($created->must_change_password);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::CREATE->value,
            'entity_type' => User::class,
            'entity_id' => $created->id,
        ]);
    }

    public function test_deactivated_user_cannot_login_and_sessions_are_terminated(): void
    {
        $admin = User::where('email', 'admin@ksmct.kogistate.gov.ng')->firstOrFail();
        $officer = User::where('email', 'licensing@ksmct.kogistate.gov.ng')->firstOrFail();

        DB::table('sessions')->insert([
            'id' => 'test-session-123',
            'user_id' => $officer->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => base64_encode('test'),
            'last_activity' => time(),
        ]);

        $this->actingAs($admin)
            ->post(route('admin.users.deactivate', $officer), ['reason' => 'Transferred out of Ministry'])
            ->assertRedirect(route('admin.users.index'));

        $this->assertFalse($officer->fresh()->is_active);
        $this->assertDatabaseMissing('sessions', ['id' => 'test-session-123']);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::DEACTIVATE->value,
            'entity_id' => $officer->id,
        ]);

        // Attempt login as deactivated user
        auth()->logout();
        $this->post(route('login.store'), [
            'email' => $officer->email,
            'password' => 'Password@2026!',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_super_admin_cannot_deactivate_or_force_logout_themselves(): void
    {
        $admin = User::where('email', 'admin@ksmct.kogistate.gov.ng')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.users.deactivate', $admin))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.users.force-logout', $admin))
            ->assertForbidden();
    }

    public function test_admin_password_reset_forces_password_change_on_next_request(): void
    {
        $admin = User::where('email', 'admin@ksmct.kogistate.gov.ng')->firstOrFail();
        $officer = User::where('email', 'dataentry@ksmct.kogistate.gov.ng')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.users.reset-password', $officer))
            ->assertRedirect(route('admin.users.index'));

        $officer->refresh();
        $this->assertTrue($officer->must_change_password);
        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::PASSWORD_RESET->value,
            'entity_id' => $officer->id,
        ]);

        $this->actingAs($officer)
            ->get(route('dashboard'))
            ->assertRedirect(route('password.force-change'));
    }

    public function test_rbac_blocks_non_admins_from_user_management(): void
    {
        $director = User::where('email', 'director@ksmct.kogistate.gov.ng')->firstOrFail();
        $dataEntry = User::where('email', 'dataentry@ksmct.kogistate.gov.ng')->firstOrFail();
        $viewer = User::where('email', 'viewer@ksmct.kogistate.gov.ng')->firstOrFail();

        // Director can view users list, cannot create
        $this->actingAs($director)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($director)->get(route('admin.users.create'))->assertForbidden();

        // Data entry & viewer cannot access user management at all
        $this->actingAs($dataEntry)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('admin.users.index'))->assertForbidden();
    }
}
