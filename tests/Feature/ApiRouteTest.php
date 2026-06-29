<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration with valid data.
     */
    public function test_api_register_success(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => ['id', 'name', 'email', 'created_at', 'updated_at'],
                'access_token',
                'token_type',
            ],
        ]);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    }

    /**
     * Test user registration validation failure.
     */
    public function test_api_register_validation_failure(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => ['name', 'email', 'password'],
        ]);
        $response->assertJsonPath('success', false);
    }

    /**
     * Test user login with valid credentials.
     */
    public function test_api_login_success(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user',
                'access_token',
                'token_type',
            ],
        ]);
        $response->assertJsonPath('success', true);
    }

    /**
     * Test user login with invalid credentials.
     */
    public function test_api_login_failure(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Invalid login credentials');
    }

    /**
     * Test protected routes return 401 when unauthenticated.
     */
    public function test_api_protected_routes_require_authentication(): void
    {
        $this->getJson('/api/user')->assertStatus(401);
        $this->postJson('/api/logout')->assertStatus(401);
        $this->getJson('/api/products')->assertStatus(401);
        $this->postJson('/api/products', [])->assertStatus(401);
        $this->getJson('/api/products/1')->assertStatus(401);
        $this->putJson('/api/products/1', [])->assertStatus(401);
        $this->deleteJson('/api/products/1')->assertStatus(401);
    }

    /**
     * Test get authenticated user profile.
     */
    public function test_api_get_authenticated_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJsonPath('email', $user->email);
    }

    /**
     * Test logout deletes access token.
     */
    public function test_api_logout_success(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('message', 'Logged out successfully');
    }

    /**
     * Test product listing with pagination and search.
     */
    public function test_api_list_products(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Product::factory()->create(['name' => 'Sony Headphone']);
        Product::factory()->create(['name' => 'Apple iPad']);

        // Test normal list
        $response = $this->getJson('/api/products');
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');

        // Test search
        $responseSearch = $this->getJson('/api/products?search=Sony');
        $responseSearch->assertStatus(200);
        $responseSearch->assertJsonCount(1, 'data');
        $responseSearch->assertJsonPath('data.0.name', 'Sony Headphone');
    }

    /**
     * Test store product success.
     */
    public function test_api_store_product_success(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/products', [
            'name' => 'New Tablet',
            'description' => 'A great new tablet.',
            'price' => 299.99,
            'quantity' => 10,
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.name', 'New Tablet');
        $this->assertDatabaseHas('products', ['name' => 'New Tablet']);
    }

    /**
     * Test store product validation failure.
     */
    public function test_api_store_product_validation_failure(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/products', [
            'name' => '',
            'price' => 'invalid-price',
            'quantity' => -5,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => ['name', 'price', 'quantity'],
        ]);
    }

    /**
     * Test show single product.
     */
    public function test_api_show_product(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.name', $product->name);
    }

    /**
     * Test show product not found.
     */
    public function test_api_show_product_not_found(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/products/99999');

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Product not found');
    }

    /**
     * Test update product success.
     */
    public function test_api_update_product(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create([
            'name' => 'Old Name',
            'price' => 100.00,
        ]);

        $response = $this->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Name',
            'price' => 150.00,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.name', 'Updated Name');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => '150.00',
        ]);
    }

    /**
     * Test update product not found.
     */
    public function test_api_update_product_not_found(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/products/99999', [
            'name' => 'Updated Name',
            'price' => 150.00,
        ]);

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Product not found');
    }

    /**
     * Test delete product success.
     */
    public function test_api_delete_product(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * Test delete product not found.
     */
    public function test_api_delete_product_not_found(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/products/99999');

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('message', 'Product not found');
    }
}
