<?php

namespace Tests\Feature;

use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateUserReportInAnIncidentTest extends TestCase
{
    /**
     * Test to update a report in an incident with a safe state. 
     */
    public function test_user_cannot_update_a_report_in_an_incident_with_a_safe_state()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createFinalUserRol();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $user);
        $userReport = $this->createUserReport($incident, $user, 'outside');
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->putJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports/$userReport->id", [
            'state' => 'at_risk',
        ]);

        $response->assertStatus(422)->assertJson([
            'message' => 'No se puede cambiar el estado del reporte de usuario',
        ]);
    }

    /**
     * Test to update a report in an incident with an at risk state. 
     */
    public function test_user_can_update_a_report_in_an_incident_with_an_at_risk_state()
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createFinalUserRol();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $user);
        $userReport = $this->createUserReport($incident, $user, 'at_risk');
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->putJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports/$userReport->id", [
            'zone_id' => $zone->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_reports', [
            'id' => $userReport->id,
            'state' => 'at_risk',
            'zone_id' => $zone->id,
        ]);
    }

    /**
     * Test to update a report in a not active incident.
     */
    public function test_user_cannot_update_a_report_in_a_not_active_incident()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createFinalUserRol();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createTotalIncident($riskSituation, $user);
        $userReport = $this->createUserReport($incident, $user);
        Sanctum::actingAs(
            $user,
            ['*']
        );

        $response = $this->putJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports/$userReport->id", [
            'state' => 'at_risk',
        ]);

        $response->assertStatus(400)->assertJson([
            'message' => 'El incidente ha finalizado',
        ]);
    }
}
