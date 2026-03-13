<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authBaseUrl = '/api/auth';
    }

    #[DataProvider('userDataProvider')]
    public function test_user_can_login_with_valid_credentials($password)
    {
        $user = User::factory()->create([
            'password' => $password,
        ]);

        $response = $this->postJson("$this->authBaseUrl/login", [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);
    }

    #[DataProvider('userDataProvider')]
    public function test_user_cannot_login_with_invalid_credentials($password)
    {
        $user = User::factory()->create([
            'password' => $password,
        ]);

        $response = $this->postJson("$this->authBaseUrl/login", [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized',
            ]);
    }

    public function test_user_cannot_login_with_missing_credentials()
    {
        $response = $this->postJson("$this->authBaseUrl/login", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_user_can_get_their_profile()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson("$this->authBaseUrl/me");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'email' => $user->email,
            ]);
    }

    public function test_user_cannot_get_their_profile_without_token()
    {
        $response = $this->getJson("$this->authBaseUrl/me");

        $response->assertStatus(401);
    }

    public function test_user_can_refresh_token()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson("$this->authBaseUrl/refresh");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
            ]);
    }

    public function test_user_can_logout()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson("$this->authBaseUrl/logout");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out',
            ]);

        $secondResponse = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->getJson("$this->authBaseUrl/me");

        $secondResponse->assertStatus(401);
    }

    public static function userDataProvider()
    {
        return [
            [
                'password' => 'password123',
            ],
        ];
    }
}
