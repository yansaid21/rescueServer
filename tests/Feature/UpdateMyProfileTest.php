<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UsersTableSeeder;
use Tests\TestCaseWithSeeders;

class UpdateMyProfileTest extends TestCaseWithSeeders
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UsersTableSeeder::class]);
    }

    public function test_200_update_my_profile_succeed(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->putJson('/api/profile', [
            "name" => "Carlos",
            "last_name" => "Andres",
            "email" => "carlosa.paezc@autonoma.edu.co",
            "id_card" => "1234567890",
            "rhgb" => "O+",
            "social_security" => "SS123456789",
            "phone_number" => "3108925614",
            "is_active" => true,
            "code" => "123456789",
            "photo" => null,
            "institution_id" => 1
        ]);

        // $response->dump();
        $response
            ->assertStatus(200)
            ->assertJsonPath("message", "Usuario actualizado/a exitosamente");
    }

    public function test_400_user_is_not_active(): void
    {
        $user = User::where('is_active', false)->first();
        $response = $this->actingAs($user)->putJson('/api/profile', [
            "name" => "Carlos",
            "last_name" => "Andres",
            "email" => "carlosa.paezc@autonoma.edu.co",
            "id_card" => "1234567890",
            "rhgb" => "O+",
            "social_security" => "SS123456789",
            "phone_number" => "3108925614",
            "is_active" => true,
            "code" => "123456789",
            "photo" => null,
            "institution_id" => 1
        ]);

        // $response->dump();
        $response
            ->assertStatus(400)
            ->assertJsonPath("message", "El usuario no está activo");
    }

    public function test_200_photo_is_not_a_file(): void
    {
        $user = User::first();
        $response = $this->actingAs($user)->putJson('/api/profile', [
            "name" => "Carlos",
            "last_name" => "Andres",
            "email" => "carlosa.paezc@autonoma.edu.co",
            "id_card" => "1234567890",
            "rhgb" => "O+",
            "social_security" => "SS123456789",
            "phone_number" => "3108925614",
            "is_active" => true,
            "code" => "123456789",
            "photo" => "RobertoPelotaPlaceHolder",
            "institution_id" => 1
        ]);

        // $response->dump();
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(["photo" => "El campo foto debe ser un archivo de tipo: image/heic, image/heif, image/jpg, image/jpeg, image/png, image/webp, image/svg."]);
    }
}
