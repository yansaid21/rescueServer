<?php

namespace Tests\Feature;

use Faker\Provider\Lorem;
use Tests\TestCase;

class CreateZoneTest extends TestCase
{

    /**
     * Test to create a zone successfully.
     */
    public function test_create_zone_successfully(): void
    {
        $institution = $this->createInstitution();
        $response = $this->postJson('/api/institutions/' . $institution->id . '/zones', [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
        ]);

        $response->assertSuccessful()->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'institution_id',
                'created_at',
                'updated_at',
            ],
            'message',
        ]);
    }

    /**
     * Test to create a zone with a name that already exists.
     */
    public function test_create_zone_with_name_that_already_exists(): void
    {
        $institution = $this->createInstitution();
        $institution->zones()->create([
            'name' => 'Fundadores',
        ]);
        $response = $this->postJson('/api/institutions/' . $institution->id . '/zones', [
            'name' => 'Fundadores',
        ]);

        $response->assertJsonValidationErrorFor('name');
    }

    /**
     * Test to create a zone with a name that is too short.
     */
    public function test_create_zone_with_name_that_is_too_short(): void
    {
        $institution = $this->createInstitution();
        $response = $this->postJson('/api/institutions/' . $institution->id . '/zones', [
            'name' => 'F',
        ]);

        $response->assertJsonValidationErrorFor('name');
    }

    /**
     * Test to create a zone with a name that is too long.
     */
    public function test_create_zone_with_name_that_is_too_long(): void
    {
        $institution = $this->createInstitution();
        $response = $this->postJson('/api/institutions/' . $institution->id . '/zones', [
            'name' => Lorem::text(200),
        ]);

        $response->assertJsonValidationErrorFor('name');
    }

    /**
     * Test to create a zone with a invalid institution.
     */
    public function test_create_zone_with_invalid_institution(): void
    {
        $response = $this->postJson('/api/institutions/100/zones', [
            'name' => $this->faker->name,
        ]);

        $response->assertNotFound();
    }
}
