<?php

namespace Tests\Feature;

use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateUserReportInAnIncidentTest extends TestCase
{
    /**
     * Test to create a report in an incident with a safe state. 
     */
    public function test_user_can_create_a_report_in_an_incident_with_a_safe_state()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createFinalUserRol();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $user);
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports", [
            'state' => 'safe',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('user_reports', [
            'incident_id' => $incident->id,
            'zone_id' => null,
        ]);
    }

    /**
     * Test to create a report in an incident with an at risk state. 
     */
    public function test_user_can_create_a_report_in_an_incident_with_an_at_risk_state()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createFinalUserRol();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $user);
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports", [
            'state' => 'at_risk',
            'zone_id' => $zone->id,
            'description' => $this->faker->text(),
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('user_reports', [
            'incident_id' => $incident->id,
            'zone_id' => $zone->id,
        ]);
    }

    /**
     * Test to create a report in an incident that is already reported. 
     */
    public function test_user_cannot_create_a_report_in_an_incident_that_is_already_reported()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createFinalUserRol();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $user);
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports", [
            'state' => 'safe',
        ]);

        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports", [
            'state' => 'safe',
        ]);

        $response->assertStatus(400);
    }

    /**
     * Test to create a report in an incident that is already finished. 
     */
    public function test_user_cannot_create_a_report_in_an_incident_that_is_already_finished()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createFinalUserRol();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createTotalIncident($riskSituation, $user);
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports", [
            'state' => 'safe',
        ]);

        $response->assertStatus(400);
    }
}
