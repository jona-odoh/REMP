<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageHelpModalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_login_page_renders_info_button_and_login_form_guide(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Page Guide')
            ->assertSee('Staff Sign In Guide')
            ->assertSee('Username / Official Ministry Email');
    }

    public function test_authenticated_pages_render_route_specific_info_modal_and_form_guides(): void
    {
        $admin = User::where('email', 'admin')->firstOrFail();

        $pages = [
            ['route' => 'dashboard', 'expected' => 'Ministry Back-Office Overview'],
            ['route' => 'admin.users.create', 'expected' => 'How to Create a Staff Account'],
            ['route' => 'culture.assets.create', 'expected' => 'How to Register a Cultural or Tourism Asset'],
            ['route' => 'licensing.hotels.create', 'expected' => 'How to Register a Hospitality Establishment'],
            ['route' => 'projects.create', 'expected' => 'How to Register a Ministry Project'],
            ['route' => 'reports.query-builder', 'expected' => 'Ad-Hoc Query Builder Guide'],
            ['route' => 'documents.index', 'expected' => 'Central Document Repository Guide'],
            ['route' => 'tasks.index', 'expected' => 'Task Assignment & Follow-Up Guide'],
        ];

        foreach ($pages as $page) {
            $this->actingAs($admin)
                ->get(route($page['route']))
                ->assertOk()
                ->assertSee('Page Guide')
                ->assertSee($page['expected']);
        }
    }
}
