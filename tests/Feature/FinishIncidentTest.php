<?php

namespace Tests\Feature;

use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FinishIncidentTest extends TestCase
{
    /**
     * Test if a administrator can finish an incident.
     *
     * @return void
     */
    public function test_administrator_can_finish_incident()
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
        $response = $this->putJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id", ['description' => $this->faker->text]);
        $response->assertSuccessful();
    }

    /**
     * Test to finish an incident that does not exist.
     *
     * @return void
     */
    public function test_finish_nonexistent_incident()
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
        $response = $this->putJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/1", ['description' => $this->faker->text]);
        $response->assertNotFound();
    }

    /**
     * Test to finish an incident that does not belong to the risk situation.
     *
     * @return void
     */
    public function test_finish_incident_not_belonging_to_risk_situation()
    {
        $institution = $this->createInstitution();
        $institution_2 = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $riskSituation2 = $this->createRiskSituation($institution_2);
        $role = $this->createAdministratorRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation2, $user);
        Sanctum::actingAs(
            $user,
            ['*']
        );
        $response = $this->putJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id", ['description' => $this->faker->text]);
        $response->assertNotFound();
    }

    /**
     * Test to finish an incident sending a final date.
     *
     * @return void
     */
    public function test_finish_incident_with_final_date()
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
        $response = $this->putJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id", ['description' => $this->faker->text, 'final_date' => now()]);
        $response->assertJsonValidationErrorFor('final_date');
    }
}
