<?php

namespace Tests\Feature;

use Faker\Provider\Lorem;
use Tests\TestCase;

class UpdateZoneTest extends TestCase
{
    /**
     * Test to update a zone successfully.
     */
    public function test_update_zone_successfully(): void
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);

        $response = $this->putJson('/api/institutions/' . $institution->id . '/zones/' . $zone->id, [
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
     * Test to update a zone with the same name.
     */
    public function test_update_zone_with_the_same_name(): void
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);

        $response = $this->putJson('/api/institutions/' . $institution->id . '/zones/' . $zone->id, [
            'name' => $zone->name,
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
     * Test to update a zone with a name that already exists.
     */
    public function test_update_zone_with_name_that_already_exists(): void
    {
        $institution = $this->createInstitution();
        $institution->zones()->create([
            'name' => 'Sacatin',
        ]);
        $zone_to_update = $institution->zones()->create([
            'name' => 'Fundadores',
        ]);

        $response = $this->putJson('/api/institutions/' . $institution->id . '/zones/' . $zone_to_update->id, [
            'name' => 'Sacatin',
            'description' => 'Zona de sacatin',
        ]);
        $response->assertJsonValidationErrorFor('name');
    }

    /**
     * Test to update a zone with a name that is too short.
     */
    public function test_update_zone_with_name_that_is_too_short(): void
    {
        $institution = $this->createInstitution();
        $zone_to_update = $this->createZone($institution);

        $response = $this->putJson('/api/institutions/' . $institution->id . '/zones/' . $zone_to_update->id, [
            'name' => 'F',
        ]);

        $response->assertJsonValidationErrorFor('name');
    }

    /**
     * Test to update a zone with a name that is too long.
     */
    public function test_update_zone_with_name_that_is_too_long(): void
    {
        $institution = $this->createInstitution();
        $zone_to_update = $this->createZone($institution);

        $response = $this->putJson('/api/institutions/' . $institution->id . '/zones/' . $zone_to_update->id, [
            'name' => Lorem::text(200),
        ]);

        $response->assertJsonValidationErrorFor('name');
    }

    /**
     * Test to update a zone that does not exist.
     */
    public function test_update_zone_that_does_not_exist(): void
    {
        $institution = $this->createInstitution();
        $response = $this->putJson('/api/institutions/' . $institution->id . '/zones/100', [
            'name' => $this->faker->name,
        ]);

        $response->assertNotFound();
    }

    /**
     * Test to update a zone with an institution that is not the institution of the zone.
     */
    public function test_update_zone_with_institution_that_is_not_the_institution_of_the_zone(): void
    {
        $institution = $this->createInstitution();
        $invalid_institution = $this->createInstitution();
        $zone = $this->createZone($institution);

        $response = $this->putJson('/api/institutions/' . $invalid_institution->id . '/zones/' . $zone->id, [
            'name' => $this->faker->name,
        ]);

        $response->assertNotFound()->assertJson([
            'message' => __('messages.not_found_in_institution', ['Model' => __('zone')]),
        ]);
    }

    /**
     * Test to update a zone with an invalid institution.
     */
    public function test_update_zone_with_invalid_institution(): void
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);

        $response = $this->putJson('/api/institutions/100/zones/' . $zone->id, [
            'name' => $this->faker->name,
        ]);
        $response->assertNotFound();
    }
}
