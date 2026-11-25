<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeveloperInfoPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_to_login_from_developer_info_page(): void
    {
        $this->get(route('developer.info'))->assertRedirect(route('login'));
    }

    public function test_authenticated_staff_can_view_developer_information_page(): void
    {
        $viewer = User::where('email', 'viewer@ksmct.kogistate.gov.ng')->firstOrFail();

        $this->actingAs($viewer)
            ->get(route('developer.info'))
            ->assertOk()
            ->assertSee('Developer &amp; Technical Documentation', false)
            ->assertSee('Jonathan Odoh')
            ->assertSee('Lead Software Architect')
            ->assertSee('jonathanodoh3140@gmail.com')
            ->assertSee('PHP Engine')
            ->assertSee('Database Engine')
            ->assertSee('Page Guide');
    }
}
