<?php

namespace Tests\Feature;

use Tests\TestCase;

class EstablishMeetPointToBrigadierTest extends TestCase
{
    /**
     * Test the establishment of a meet point to a brigadier.
     *
     * @return void
     */
    public function test_establishment_of_meet_point_to_brigadier()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $meetPoint = $this->createMeetPoint($institution, $zone);
        $role = $this->createBrigadierRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);

        $response = $this->postJson('/api/institutions/' . $institution->id . '/brigadiers/' . $user->id, [
            'meet_point' => $meetPoint->id
        ]);
        $response->assertSuccessful();
    }

    /**
     * Test the establishment of a meet point to a user without brigadier role.
     *
     * @return void
     */
    public function test_establishment_of_meet_point_to_user_without_brigadier_role()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $meetPoint = $this->createMeetPoint($institution, $zone);
        $role = $this->createFinalUserRol();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);

        $response = $this->postJson('/api/institutions/' . $institution->id . '/brigadiers/' . $user->id, [
            'meet_point' => $meetPoint->id
        ]);
        $response->assertStatus(400)->assertJson([
            'message' => 'El usuario no es un brigadista'
        ]);
    }

    /**
     * Test the establishment of a meet point to a user that does not belong to the institution.
     *
     * @return void
     */
    public function test_establishment_of_meet_point_to_user_that_does_not_belong_to_institution()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $meetPoint = $this->createMeetPoint($institution, $zone);
        $role = $this->createBrigadierRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $institution2 = $this->createInstitution();

        $response = $this->postJson('/api/institutions/' . $institution2->id . '/brigadiers/' . $user->id, [
            'meet_point' => $meetPoint->id
        ]);

        $response->assertStatus(404);
    }

    /**
     * Test the establishment of a meet point to a brigadier that already has a meet point assigned.
     *
     * @return void
     */
    public function test_establishment_of_meet_point_to_brigadier_that_already_has_meet_point_assigned()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $meetPoint = $this->createMeetPoint($institution, $zone);
        $meetPoint2 = $this->createMeetPoint($institution, $zone);
        $role = $this->createBrigadierRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $this->createMeetPointBrigadier($user, $meetPoint);

        $response = $this->postJson('/api/institutions/' . $institution->id . '/brigadiers/' . $user->id, [
            'meet_point' => $meetPoint->id
        ]);
        $response->assertStatus(400)->assertJson([
            'message' => 'El usuario ya está asignado a un punto de encuentro fijo'
        ]);
    }

    /**
     * Test the establishment of a meet point to a brigadier that is asigned to a meet point in an incident.
     *
     * @return void
     */
    public function test_establishment_of_meet_point_to_brigadier_that_is_assigned_to_meet_point_in_incident()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $meetPoint = $this->createMeetPoint($institution, $zone);
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createBrigadierRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createTotalIncident($riskSituation, $user);
        $this->createMeetPointBrigadier($user, $meetPoint, $incident);

        $response = $this->postJson('/api/institutions/' . $institution->id . '/brigadiers/' . $user->id, [
            'meet_point' => $meetPoint->id
        ]);
        $response->assertSuccessful();
    }

    /**
     * Test the establishment of a meet point that does not exist.
     *
     * @return void
     */
    public function test_establishment_of_meet_point_that_does_not_exist()
    {
        $institution = $this->createInstitution();
        $role = $this->createBrigadierRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);

        $response = $this->postJson('/api/institutions/' . $institution->id . '/brigadiers/' . $user->id, [
            'meet_point' => 100
        ]);
        $response->assertJsonValidationErrorFor('meet_point');
    }
}
