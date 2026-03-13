<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_list_clients()
    {
        Client::factory()->times(2)->create();

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/clients');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_admin_can_view_client_details()
    {
        $client = Client::factory(['name' => 'Tester', 'email' => 'tester@test.com'])->create();

        $response = $this->actingAs($this->admin, 'api')
            ->getJson("/api/clients/{$client->id}");

        $response->assertStatus(200)
            ->assertJsonPath('name', 'Tester');
    }
}
