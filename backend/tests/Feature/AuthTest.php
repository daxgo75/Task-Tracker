<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email'    => 'test@example.com',
            'password' => bcrypt('password123'),
        ], $overrides));
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = $this->createUser();

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'user' => ['id', 'name', 'email']],
            ])
            ->assertJson(['success' => true]);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        $this->createUser();

        $response = $this->postJson('/api/auth/login', [
            'email'    => 'test@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_login_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email'    => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['errors' => ['email']]);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['errors' => ['email', 'password']]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user  = $this->createUser();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(401);
    }

    public function test_user_can_get_own_profile(): void
    {
        $user  = $this->createUser();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'test@example.com');
    }
}
