<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeleteZoneTest extends TestCase
{

    // /**
    //  * Test to delete a zone successfully.
    //  */
    // public function test_delete_zone_successfully(): void
    // {
    //     $institution = $this->createInstitution();
    //     $zone = $this->createZone($institution);
    //
    //     $response = $this->deleteJson('/api/institutions/' . $institution->id . '/zones/' . $zone->id);
    //     $response->assertSuccessful()->assertJson([
    //         'message' => __('messages.deleted', ['Model' => __('zone')]),
    //     ]);
    // }
    //
    // /**
    //  * Test to delete a zone that has related meet points.
    //  */
    // public function test_delete_zone_that_has_related_meet_points(): void
    // {
    //     $institution = $this->createInstitution();
    //     $zone = $this->createZone($institution);
    //     $this->createMeetPoint($institution, $zone);
    //
    //     $response = $this->deleteJson('/api/institutions/' . $institution->id . '/zones/' . $zone->id);
    //     $response->assertStatus(400)->assertJson([
    //         'message' => __('messages.cannot_delete', ['Model' => __('zone'), 'resources' => __('meet points')]),
    //     ]);
    // }
    //
    // /**
    //  * Test to delete a zone that has related rooms.
    //  */
    // public function test_delete_zone_that_has_related_rooms(): void
    // {
    //     $institution = $this->createInstitution();
    //     $zone = $this->createZone($institution);
    //     $level = $this->createLevel($institution);
    //     $this->createRoom($zone, $level);
    //
    //     $response = $this->deleteJson('/api/institutions/' . $institution->id . '/zones/' . $zone->id);
    //     $response->assertStatus(400)->assertJson([
    //         'message' => __('messages.cannot_delete', ['Model' => __('zone'), 'resources' => __('rooms')]),
    //     ]);
    // }
    //
    // /**
    //  * Test to update a zone that does not exist.
    //  */
    // public function test_delete_zone_that_does_not_exist(): void
    // {
    //     $institution = $this->createInstitution();
    //     $response = $this->putJson('/api/institutions/' . $institution->id . '/zones/100', [
    //         'name' => 'Fundadores',
    //         'description' => 'Zona de fundadores actualizada',
    //     ]);
    //     $response->assertNotFound();
    // }
    //
    // /**
    //  * Test to delete a zone with an institution that is not the institution of the zone.
    //  */
    // public function test_delete_zone_with_institution_that_is_not_the_institution_of_the_zone(): void
    // {
    //     $institution = $this->createInstitution();
    //     $invalid_institution = $this->createInstitution();
    //     $zone = $this->createZone($institution);
    //     $this->createMeetPoint($institution, $zone);
    //
    //     $response = $this->putJson('/api/institutions/' . $invalid_institution->id . '/zones/' . $zone->id, [
    //         'name' => 'Fundadores',
    //         'description' => 'Zona de fundadores actualizada',
    //     ]);
    //     $response->assertNotFound()->assertJson([
    //         'message' => __('messages.not_found_in_institution', ['Model' => __('zone')]),
    //     ]);
    // }
    //
    // /**
    //  * Test to delete a zone with an invalid institution.
    //  */
    // public function test_delete_zone_with_invalid_institution(): void
    // {
    //     $institution = $this->createInstitution();
    //     $zone = $this->createZone($institution);
    //     $this->createMeetPoint($institution, $zone);
    //
    //     $response = $this->putJson('/api/institutions/100/zones/' . $zone->id, [
    //         'name' => 'Fundadores',
    //         'description' => 'Zona de fundadores actualizada',
    //     ]);
    //     $response->assertNotFound();
    // }
}
