<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_me_returns_user()
    {
        // Ensure user exists with known password
        $user = User::factory()->create([
            'email' => 'admin@admin.com',
            'password' => 'password', // password cast will hash in model if applicable
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@admin.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertArrayHasKey('token', $data);

        $token = $data['token'];

        $me = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/auth/me');

        $me->assertStatus(200);
        $me->assertJsonFragment(['email' => 'admin@admin.com']);
    }
}
