<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that root / redirects to /login.
     */
    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test that /login returns 200 and loads successfully.
     */
    public function test_login_page_loads_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * Test that /dashboard page loads successfully.
     */
    public function test_dashboard_page_loads_successfully(): void
    {
        $response = $this->get('/dashboard');

        $response->assertStatus(200);
    }

    /**
     * Test that /products page loads successfully.
     */
    public function test_products_index_page_loads_successfully(): void
    {
        $response = $this->get('/products');

        $response->assertStatus(200);
    }

    /**
     * Test that /products/create page loads successfully.
     */
    public function test_products_create_page_loads_successfully(): void
    {
        $response = $this->get('/products/create');

        $response->assertStatus(200);
    }

    /**
     * Test that /products/{id}/edit page loads successfully.
     */
    public function test_products_edit_page_loads_successfully(): void
    {
        $response = $this->get('/products/1/edit');

        $response->assertStatus(200);
    }
}
