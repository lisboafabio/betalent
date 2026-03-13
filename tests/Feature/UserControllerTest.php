<?php

namespace Tests\Feature;

use App\Domain\User\Enums\UserRoleEnum;
use App\Models\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => UserRoleEnum::ADMIN->value]);
        $this->manager = User::factory()->create(['role' => UserRoleEnum::MANAGER->value]);
        $this->finance = User::factory()->create(['role' => UserRoleEnum::FINANCE->value]);
        $this->user = User::factory()->create(['role' => UserRoleEnum::USER->value]);
        $this->baseRouteUrlPath = "/api/users";
    }

    public function test_admin_can_list_users()
    {
        $response = $this->actingAs($this->admin, 'api')->getJson($this->baseRouteUrlPath);
        $response->assertStatus(200);
        $this->assertCount(4, $response->json('data'));
    }

    public function test_finance_cannot_list_users()
    {
        $response = $this->actingAs($this->finance, 'api')->getJson($this->baseRouteUrlPath);
        $response->assertStatus(403);
    }

    public function test_admin_can_create_admin()
    {
        $payload = [
            'name' => 'New Admin',
            'email' => 'admin@test.com',
            'password' => 'password123',
            'role' => UserRoleEnum::ADMIN->value,
        ];

        $response = $this->actingAs($this->admin, 'api')->postJson($this->baseRouteUrlPath, $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'admin@test.com', 'role' => UserRoleEnum::ADMIN->value]);
    }

    public function test_manager_cannot_create_admin()
    {
        $payload = [
            'name' => 'Should Fail',
            'email' => 'fail@test.com',
            'password' => 'password123',
            'role' => UserRoleEnum::ADMIN->value,
        ];

        $response = $this->actingAs($this->manager, 'api')->postJson($this->baseRouteUrlPath, $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    public function test_manager_can_create_user()
    {
        $payload = [
            'name' => 'New User by manager',
            'email' => 'user_manager@test.com',
            'password' => 'password123',
            'role' => UserRoleEnum::USER->value,
        ];

        $response = $this->actingAs($this->manager, 'api')->postJson($this->baseRouteUrlPath, $payload);
        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => 'user_manager@test.com', 'role' => UserRoleEnum::USER->value]);
    }

    public function test_finance_cannot_create_users()
    {
        $payload = [
            'name' => 'New User by finance',
            'email' => 'user_finance@test.com',
            'password' => 'password123',
            'role' => UserRoleEnum::USER->value,
        ];

        $response = $this->actingAs($this->finance, 'api')->postJson($this->baseRouteUrlPath, $payload);
        $response->assertStatus(403);
    }

    public function test_user_can_edit_own_name_but_not_role()
    {
        $payload = [
            'name' => 'Updated Name',
            'role' => UserRoleEnum::ADMIN->value,
        ];

        $response = $this->actingAs($this->user, 'api')->putJson("$this->baseRouteUrlPath/{$this->user->id}", $payload);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);

        $payload2 = [
            'name' => 'Updated Name 2'
        ];
        $response2 = $this->actingAs($this->user, 'api')->putJson("$this->baseRouteUrlPath/{$this->user->id}", $payload2);
        $response2->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $this->user->id, 'name' => 'Updated Name 2', 'role' => UserRoleEnum::USER->value]);

    }

    public function test_user_cannot_edit_other_user()
    {
        $payload = ['name' => 'Hacked Name'];

        $response = $this->actingAs($this->user, 'api')->putJson("$this->baseRouteUrlPath/{$this->manager->id}", $payload);
        $response->assertStatus(403);
    }

    public function test_admin_can_delete_user_but_not_self()
    {
        $responseSelf = $this->actingAs($this->admin, 'api')->deleteJson("$this->baseRouteUrlPath/{$this->admin->id}");
        $responseSelf->assertStatus(403);

        $responseUser = $this->actingAs($this->admin, 'api')->deleteJson("$this->baseRouteUrlPath/{$this->user->id}");
        $responseUser->assertStatus(204);
        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }

    public function test_manager_cannot_delete_admin()
    {
        $response = $this->actingAs($this->manager, 'api')->deleteJson("$this->baseRouteUrlPath/{$this->admin->id}");
        $response->assertStatus(403);
    }
}
