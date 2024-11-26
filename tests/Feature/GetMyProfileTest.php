<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UsersTableSeeder;
use Tests\TestCaseWithSeeders;

class GetMyProfileTest extends TestCaseWithSeeders
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UsersTableSeeder::class);
    }

    public function test_200_admin_get_my_profile_succeed(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->getJson('/api/profile');
        // $response->dump();
        $response
            ->assertStatus(200)
            ->assertJsonPath("message", "Usuario recuperado/a exitosamente")
            ->assertJsonPath("data.id_card", $user->id_card);
        $this->assertTrue($user->is_active);
    }

    public function test_200_brigadier_get_my_profile_succeed(): void
    {
        $user = User::whereHas('role', function ($q) {
            $q->where('name', 'Brigadier');
        })->first();
        $response = $this->actingAs($user)->getJson('/api/profile');
        // $response->dump();
        $response
            ->assertStatus(200)
            ->assertJsonPath("data.id_card", $user->id_card);
    }

    public function test_200_final_user_get_my_profile_succeed(): void
    {
        $user = User::whereHas('role', function ($q) {
            $q->where('name', 'Final User');
        })->first();
        $response = $this->actingAs($user)->getJson('/api/profile');
        // $response->dump();
        $response
            ->assertStatus(200)
            ->assertJsonPath("data.id_card", $user->id_card);
    }

    public function test_400_user_is_not_active(): void
    {
        $user = User::where('is_active', false)->first();
        $response = $this->actingAs($user)->getJson('/api/profile');
        // $response->dump();
        $response
            ->assertStatus(400)
            ->assertJsonPath("message", "El usuario no está activo");
    }

    public function test_200_user_has_completed_register(): void
    {
        $user = User::where('name', 'Final')->first();
        $response = $this->actingAs($user)->getJson('/api/profile?institution=1');
        // $response->dump();
        $response
            ->assertStatus(200)
            ->assertJsonPath("data.registerCompleted", true);
    }

    public function test_200_user_has_not_completed_register(): void
    {
        $user = User::where('name', 'Gerardo')->first();
        $response = $this->actingAs($user)->getJson('/api/profile?institution=1');
        // $response->dump();
        $response
            ->assertStatus(200)
            ->assertJsonPath("data.registerCompleted", false);
    }
}
