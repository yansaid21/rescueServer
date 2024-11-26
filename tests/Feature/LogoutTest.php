<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UsersTableSeeder;
use Tests\TestCaseWithSeeders;

class LogoutTest extends TestCaseWithSeeders
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UsersTableSeeder::class]);
    }

    public function test_200_logout_succeed(): void
    {
        $user = User::first();
        $token = $user->createToken("galaxys7");
        $response = $this->withHeader("Authorization", "Bearer " . $token->plainTextToken)->postJson('/api/auth/logout');
        $response->assertStatus(200);
        $this->assertEmpty(User::first()->tokens);
    }

    public function test_401_no_token_in_header(): void
    {
        $user = User::first();
        $user->createToken("galaxys7");
        $response = $this->postJson('/api/auth/logout');
        $response
            ->assertStatus(401)
            ->assertJsonPath("message", "Usuario no autenticado.");
        $this->assertCount(1, User::first()->tokens);
    }

    public function test_401_token_not_valid(): void
    {
        $user = User::first();
        $token = $user->createToken("galaxys7");
        $response = $this->withHeader("Authorization", "Bearer " . $token->plainTextToken . "a")->postJson('/api/auth/logout');
        $response
            ->assertStatus(401)
            ->assertJsonPath("message", "Usuario no autenticado.");
        $this->assertCount(1, User::first()->tokens);
    }
}
