<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_create_product()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/products', [
                'name' => 'Test Product',
                'amount' => 1500
            ]);

        $response->assertStatus(201)
                 ->assertJsonPath('name', 'Test Product')
                 ->assertJsonPath('amount', 1500);

        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
    }

    public function test_admin_can_list_products()
    {
        Product::factory()->times(3)->create();

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/products');

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('data'));
    }

    public function test_validation_fails_on_missing_fields()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/products', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name', 'amount']);
    }
}
