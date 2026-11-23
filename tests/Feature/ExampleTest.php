<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_unauthenticated_root_redirects_to_dashboard_and_login_is_accessible(): void
    {
        $this->get('/')->assertRedirect(route('dashboard'));
        $this->get(route('login'))->assertOk();
    }
}
