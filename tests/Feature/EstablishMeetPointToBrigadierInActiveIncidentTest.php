<?php

namespace Tests\Feature;

use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EstablishMeetPointToBrigadierInActiveIncidentTest extends TestCase
{
    /**
     * Test the establishment of a meet point to a brigadier in an active incident.
     *
     * @return void
     */
    public function test_establishment_of_meet_point_to_brigadier_in_active_incident()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $meetPoint = $this->createMeetPoint($institution, $zone);
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createBrigadierRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $user);
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->postJson('/api/institutions/' . $institution->id . '/meet_points/' . $meetPoint->id . '/assign');
        $response->assertSuccessful();
        $this->assertDatabaseHas('meet_point_brigadiers', [
            'meet_point_id' => $meetPoint->id,
            'brigadier_id' => $user->id,
            'incident_id' => $incident->id,
        ]);
    }

    /**
     * Test the establishment of a meet point to a brigadier in an active incident with a user that is not part of the institution.
     *
     * @return void
     */
    public function test_establishment_of_meet_point_to_brigadier_in_active_incident_with_user_that_is_not_part_of_the_institution()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $meetPoint = $this->createMeetPoint($institution, $zone);
        $riskSituation = $this->createRiskSituation($institution);
        $this->createBrigadierRole();
        $user = $this->createUser();
        $this->createInitialIncident($riskSituation, $user);
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->postJson("/api/institutions/$institution->id/meet_points/$meetPoint->id/assign");
        $response->assertStatus(403);
    }

    /**
     * Test the establishment of a meet point to a brigadier in an active incident with a user that is not a brigadier.
     *
     * @return void
     */
    public function test_establishment_of_meet_point_to_brigadier_in_active_incident_with_user_that_is_not_a_brigadier()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $meetPoint = $this->createMeetPoint($institution, $zone);
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createFinalUserRol();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $this->createInitialIncident($riskSituation, $user);
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->postJson("/api/institutions/$institution->id/meet_points/$meetPoint->id/assign");
        $response->assertStatus(403);
    }

    /**
     * Test the establishment of a meet point to a brigadier in an active incident with a meet point that is not part of the institution.
     *
     * @return void
     */
    public function test_establishment_of_meet_point_to_brigadier_in_active_incident_with_meet_point_that_is_not_part_of_the_institution()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $institution_2 = $this->createInstitution();
        $meetPoint = $this->createMeetPoint($institution_2, $zone);
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createBrigadierRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $this->createInitialIncident($riskSituation, $user);
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->postJson("/api/institutions/$institution->id/meet_points/$meetPoint->id/assign");
        $response->assertStatus(404);
    }

    /**
     * Test the establishment of a meet point to a brigadier in an active incident without an incidents
     *
     * @return void
     */
    public function test_establishment_of_meet_point_to_brigadier_in_active_incident_without_an_incident()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $meetPoint = $this->createMeetPoint($institution, $zone);
        $role = $this->createBrigadierRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->postJson("/api/institutions/$institution->id/meet_points/$meetPoint->id/assign");
        $response->assertStatus(400);
    }

    /**
     * Test the establishment of a meet point to a brigadier in an active incident without an active incident.
     *
     * @return void
     */
    public function test_establishment_of_meet_point_to_brigadier_in_active_incident_without_an_active_incident()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $meetPoint = $this->createMeetPoint($institution, $zone);
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createBrigadierRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $this->createTotalIncident($riskSituation, $user);
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->postJson("/api/institutions/$institution->id/meet_points/$meetPoint->id/assign");
        $response->assertStatus(400);
    }

    /**
     * Test the establishment of a meet point to a brigadier in an active incident with a brigadier that is already assigned to the incident.
     *
     * @return void
     */
    public function test_establishment_of_meet_point_to_brigadier_in_active_incident_with_brigadier_that_is_already_assigned_to_the_incident()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $meetPoint = $this->createMeetPoint($institution, $zone);
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createBrigadierRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $user);
        $this->createMeetPointBrigadier($user, $meetPoint, $incident);
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->postJson('/api/institutions/' . $institution->id . '/meet_points/' . $meetPoint->id . '/assign');
        $response->assertStatus(400);
    }
}
