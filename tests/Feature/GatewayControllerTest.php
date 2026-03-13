<?php

namespace Tests\Feature;

use App\Domain\User\Enums\UserRoleEnum;
use App\Models\Gateway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    }

    public function test_admin_can_activate_gateway()
    {
        $gateway = Gateway::factory(['name' => 'Gateway 1', 'is_active' => false, 'priority' => 1])->create();

        $response = $this->actingAs($this->admin, 'api')
            ->postJson("/api/gateways/{$gateway->id}/activate");

        $response->assertStatus(200);
        $this->assertDatabaseHas('gateways', ['id' => $gateway->id, 'is_active' => 1]);
    }

    public function test_admin_can_change_gateway_priority()
    {
        $gateway = Gateway::factory(['name' => 'Gateway 1', 'is_active' => true, 'priority' => 10])->create();

        $response = $this->actingAs($this->admin, 'api')
            ->putJson("/api/gateways/{$gateway->id}/priority", ['priority' => 1]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('gateways', ['id' => $gateway->id, 'priority' => 1]);
    }

    public function test_user_cannot_activate_gateway()
    {
        $user = User::factory()->create();
        $gateway = Gateway::factory(['name' => 'Gateway 1', 'is_active' => false, 'priority' => 1])->create();

        $response = $this->actingAs($user, 'api')
            ->postJson("/api/gateways/{$gateway->id}/activate");

        $response->assertStatus(403);
    }
    public function test_cannot_activate_non_existent_gateway()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/gateways/999/activate');

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Gateway not found');
    }

    public function test_cannot_change_priority_of_non_existent_gateway()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->putJson('/api/gateways/999/priority', ['priority' => 1]);

        $response->assertStatus(404)
            ->assertJsonPath('message', 'Gateway not found');
    }
}
