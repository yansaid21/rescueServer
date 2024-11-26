<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UsersTableSeeder;
use Tests\TestCaseWithSeeders;

class LoginTest extends TestCaseWithSeeders
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([UsersTableSeeder::class]);
    }

    public function test_logs_in_when_credentials_are_correct(): void
    {
        $response = $this->postJson('/api/auth/login', [
            "email" => "johndoe@autonoma.edu.co",
            "password" =>  "password123",
            "device_name" =>  "galaxys7"
        ]);

        // $response->dumpHeaders();
        // $response->dumpSession();
        // $response->dump();
        $response
            ->assertStatus(200)
            ->assertJson([
                "message" => "Usuario autenticado correctamente.",
            ])
            ->assertJsonPath("user.id_card", 123456788)
            ->assertJsonMissingPath("user.password");


        // $response->assertExactJson([
        //     "message" => "Usuario autenticado correctamente.",
        // ]);

        // Exceptions::assertNothingReported(); // For some reason it does not work
    }

    public function test_422_user_does_not_exists(): void
    {
        $response = $this->postJson('/api/auth/login', [
            "email" => "roberto@autonoma.edu.co",
            "password" =>  "password1234",
            "device_name" =>  "galaxys7"
        ]);

        // $response->dumpHeaders();
        // $response->dumpSession();
        // $response->dump();
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                "credentials" => "Las credenciales proporcionadas son incorrectas."
            ]);
    }

    public function test_422_password_is_wrong(): void
    {
        $response = $this->postJson('/api/auth/login', [
            "email" => "johndoe@autonoma.edu.co",
            "password" =>  "contraseñamala",
            "device_name" =>  "galaxys7"
        ]);

        // $response->dumpHeaders();
        // $response->dumpSession();
        // $response->dump();
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                "credentials" => "Las credenciales proporcionadas son incorrectas."
            ]);
    }

    public function test_422_fields_are_missing(): void
    {
        $response = $this->postJson('/api/auth/login', [
            "email" => "",
            "password" =>  "",
            "device_name" =>  ""
        ]);

        // $response->dumpHeaders();
        // $response->dumpSession();
        // $response->dump();
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                "email" => "El campo correo electrónico es requerido.",
                "password" => "El campo contraseña es requerido.",
                "device_name" => "El campo device name es requerido.",
            ]);
    }

    public function test_422_fields_are_too_long(): void
    {
        $response = $this->postJson('/api/auth/login', [
            "email" => str_repeat("a", 256) . "@autonoma.edu.co",
            "password" =>  str_repeat("a", 101),
            "device_name" =>  str_repeat("a", 101)
        ]);

        // $response->dumpHeaders();
        // $response->dumpSession();
        // $response->dump();
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                "email" => "El campo correo electrónico no debe ser mayor que 255 caracteres.",
                "password" => "El campo contraseña no debe ser mayor que 100 caracteres.",
                "device_name" => "El campo device name no debe ser mayor que 100 caracteres.",
            ]);
    }

    public function test_422_sql_injection(): void
    {
        $response = $this->postJson('/api/auth/login', [
            "email" => "johndoe@autonoma.edu.co",
            "password" =>  "'or 1=1 #",
            "device_name" =>  "galaxys7"
        ]);

        // $response->dumpHeaders();
        // $response->dumpSession();
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                "credentials" => "Las credenciales proporcionadas son incorrectas."
            ]);
    }

    public function test_new_session_device_is_repeated(): void
    {
        $user = User::where('email', 'johndoe@autonoma.edu.co')->first();
        $user->createToken("galaxys7");

        $response = $this->postJson('/api/auth/login', [
            "email" => "johndoe@autonoma.edu.co",
            "password" =>  "password123",
            "device_name" =>  "galaxys7"
        ]);

        // dd($response->session());
        $response
            ->assertStatus(200)
            ->assertJson([
                "message" => "Usuario autenticado correctamente.",
            ])
            ->assertJsonPath("user.id_card", 123456788)
            ->assertJsonMissingPath("user.password");

        $count = 0;
        foreach ($user->tokens as $token) {
            if ($token->name == "galaxys7") {
                $count++;
            }
        }
        $this->assertEquals(1, $count);
    }
}
