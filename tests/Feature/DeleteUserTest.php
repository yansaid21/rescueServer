<?php

namespace Tests\Feature;

use Database\Seeders\UsersTableSeeder;
use Tests\TestCaseWithSeeders;

class DeleteUserTest extends TestCaseWithSeeders
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UsersTableSeeder::class);
    }

    public function test_200_delete_user_succeed(): void
    {
        $response = $this->deleteJson('/api/users/1');
        $response
            ->assertStatus(200)
            ->assertJsonPath("message", "Usuario eliminado/a exitosamente");
    }

    public function test_404_user_not_found(): void
    {
        $response = $this->deleteJson('/api/users/100');
        $response->dump();
        $response
            ->assertStatus(404)
            ->assertJsonPath("message", "Usuario no encontrado");
    }
}
