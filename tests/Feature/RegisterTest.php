<?php

namespace Tests\Feature;

use Database\Seeders\UsersTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCaseWithSeeders;

class RegisterTest extends TestCaseWithSeeders
{

    protected function setUp(): void
    {
        parent::setUp();
    }

    use RefreshDatabase;
    public function test_succed_with_all_fields(): void
    {
        Storage::fake();

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson('/api/users', [
            "name" => "John",
            "last_name" => "Doe",
            "email" => "johndoe@autonoma.edu.co",
            "password" => "password123",
            "id_card" => "123456788",
            "rhgb" => "PI",
            "social_security" => "SURA",
            "phone_number" => "3224422392",
            "photo" => $file,
            "institution_id" => "1",
            "code" => "123456789"
        ]);

        // $response->dump();
        $response->assertStatus(201);
        Storage::disk()->assertExists("public/users/" . $response->json()["data"]["id"] . ".jpg");
    }

    public function test_422_code_field_is_missing(): void
    {
        Storage::fake();

        $file = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->postJson('/api/users', [
            "name" => "John",
            "last_name" => "Doe",
            "email" => "johndoe@autonoma.edu.co",
            "password" => "password123",
            "id_card" => "123456788",
            "rhgb" => "PI",
            "social_security" => "SURA",
            "phone_number" => "3224422392",
            "photo" => $file,
            "institution_id" => "1"
        ]);

        // $response->dump();
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                "code" => "El campo código es requerido."
            ]);
        $this->assertEmpty(Storage::disk()->allFiles());
    }

    public function test_422_photo_field_is_not_an_image()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('image.jpg', 100, 'application/pdf');

        $response = $this->postJson('/api/users', [
            "name" => "John",
            "last_name" => "Doe",
            "email" => "johndoe@autonoma.edu.co",
            "password" => "password123",
            "id_card" => "123456788",
            "rhgb" => "PI",
            "social_security" => "SURA",
            "phone_number" => "3224422392",
            "photo" => $file,
            "institution_id" => "1",
            "code" => "123456789"
        ]);

        // $response->dump();
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                "photo" => "El campo foto debe ser de alguno de los siguientes tipos de imagen: jpeg, png, webp, svg, heic, heif"
            ]);
        $this->assertEmpty(Storage::disk()->allFiles());
    }

    public function test_422_photo_field_is_too_large()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('image.jpg', 1024 * 1024 * 2, 'image/jpeg');

        $response = $this->postJson('/api/users', [
            "name" => "John",
            "last_name" => "Doe",
            "email" => "johndoe@autonoma.edu.co",
            "password" => "password123",
            "id_card" => "123456788",
            "rhgb" => "PI",
            "social_security" => "SURA",
            "phone_number" => "3224422392",
            "photo" => $file,
            "institution_id" => "1",
            "code" => "123456789"
        ]);

        // $response->dump();
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                "photo" => "El campo foto no debe ser mayor que 2000 kilobytes."
            ]);
        $this->assertEmpty(Storage::disk()->allFiles());
    }

    public function test_201_photo_field_is_missing()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('image.jpg', 100, 'image/jpeg');

        $response = $this->postJson('/api/users', [
            "name" => "John",
            "last_name" => "Doe",
            "email" => "johndoe@autonoma.edu.co",
            "password" => "password123",
            "id_card" => "123456788",
            "rhgb" => "PI",
            "social_security" => "SURA",
            "phone_number" => "3224422392",
            "institution_id" => "1",
            "code" => "123456789"
        ]);

        // $response->dump();
        $response
            ->assertStatus(201);
        $this->assertEmpty(Storage::disk()->allFiles());
    }

    public function test_201_photo_field_is_heic_mime_type()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('avatar.jpg', 300, 'image/heic');

        $response = $this->postJson('/api/users', [
            "name" => "John",
            "last_name" => "Doe",
            "email" => "johndoe@autonoma.edu.co",
            "password" => "password123",
            "id_card" => "123456788",
            "rhgb" => "PI",
            "social_security" => "SURA",
            "phone_number" => "3224422392",
            "photo" => $file,
            "institution_id" => "1",
            "code" => "123456789"
        ]);

        // $response->dump();
        $response->assertStatus(201);
        Storage::disk()->assertExists("public/users/" . $response->json()["data"]["id"] . ".heic");
    }

    public function test_422_institution_does_not_exists(): void
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('avatar.jpg', 300, 'image/heic');

        $response = $this->postJson('/api/users', [
            "name" => "John",
            "last_name" => "Doe",
            "email" => "johndoe@autonoma.edu.co",
            "password" => "password123",
            "id_card" => "123456788",
            "rhgb" => "PI",
            "social_security" => "SURA",
            "phone_number" => "3224422392",
            "photo" => $file,
            "institution_id" => "100",
            "code" => "123456789"
        ]);

        // $response->dump();
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                "institution_id" => "El o la institución debe existir."
            ]);
        $this->assertEmpty(Storage::disk()->allFiles());
    }

    public function test_400_user_with_email_already_have_institution()
    {
        $this->seed([UsersTableSeeder::class]);
        Storage::fake();
        $file = UploadedFile::fake()->create('avatar.jpg', 300, 'image/heic');

        $response = $this->postJson('/api/users', [
            "name" => "John",
            "last_name" => "Doe",
            "email" => "johndoe@autonoma.edu.co",
            "password" => "password123",
            "id_card" => "123456777",
            "rhgb" => "PI",
            "social_security" => "SURA",
            "phone_number" => "3224422392",
            "photo" => $file,
            "institution_id" => "1",
            "code" => "123456789"
        ]);

        // $response->dump();
        $response
            ->assertStatus(400)
            ->assertJsonPath("message", "El usuario con ese id ya está asociado con la institución");
        $this->assertEmpty(Storage::disk()->allFiles());
    }

    public function test_400_user_with_id_card_already_have_institution()
    {
        $this->seed([UsersTableSeeder::class]);
        Storage::fake();
        $file = UploadedFile::fake()->create('avatar.jpg', 300, 'image/heic');

        $response = $this->postJson('/api/users', [
            "name" => "John",
            "last_name" => "Doe",
            "email" => "roberto@autonoma.edu.co",
            "password" => "password123",
            "id_card" => "123456788",
            "rhgb" => "PI",
            "social_security" => "SURA",
            "phone_number" => "3224422392",
            "photo" => $file,
            "institution_id" => "1",
            "code" => "123456789"
        ]);

        // $response->dump();
        $response
            ->assertStatus(400)
            ->assertJsonPath("message", "El usuario con ese id ya está asociado con la institución");
        $this->assertEmpty(Storage::disk()->allFiles());
    }
}
