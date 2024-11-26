<?php

namespace Tests\Feature;

use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateIncidentTest extends TestCase
{

    /**
     * Test if a brigadier cannot create an incident.
     *
     * @return void
     */
    public function test_brigadier_cannot_create_incident()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createBrigadierRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents");
        $response->assertStatus(403);
    }

    /**
     * Test if a administrator can create an incident.
     *
     * @return void
     */
    public function test_administrator_can_create_incident()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createAdministratorRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents");
        $response->assertSuccessful();
    }

    /**
     * Test if a user cannot create an incident.
     *
     * @return void
     */
    public function test_user_cannot_create_incident()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $user = $this->createUser($institution);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents");
        $response->assertStatus(403);
    }

    /**
     * Test if a user cannot create an incident if there is an active incident.
     *
     * @return void
     */
    public function test_user_cannot_create_incident_if_there_is_an_active_incident()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createAdministratorRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $user);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents");
        $response->assertStatus(400);
    }

    /**
     * Test if a user cannot create an incident if the risk situation does not exist.
     *
     * @return void
     */
    public function test_user_cannot_create_incident_if_the_risk_situation_does_not_exist()
    {
        $institution = $this->createInstitution();
        $role = $this->createAdministratorRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/100/incidents");
        $response->assertStatus(404);
    }

    /**
     * Test if a user cannot create an incident if the user is not related to the institution.
     * 
     * @return void
     */
    public function test_user_cannot_create_incident_if_the_user_is_not_related_to_the_institution()
    {
        $institution = $this->createInstitution();
        $institution_2 = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createAdministratorRole();
        $user = $this->createUser();
        $user->institutions()->attach($institution_2->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents");
        $response->assertStatus(403);
    }

    /**
     * Test if a user cannot create an incident if the user is not authenticated.
     * 
     * @return void
     */
    public function test_user_cannot_create_incident_if_the_user_is_not_authenticated()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createAdministratorRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents");
        $response->assertStatus(401);
    }
}
