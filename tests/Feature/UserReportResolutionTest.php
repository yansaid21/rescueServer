<?php

namespace Tests\Feature;

use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserReportResolutionTest extends TestCase
{
    /**
     * Test to resolve own report in an incident successfully. 
     */
    public function test_user_can_resolve_own_report_in_an_incident_successfully()
    {
        $institution = $this->createInstitution();
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

        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports/$userReport->id/resolution", [
            'state' => 'safe',
            'description' => $this->faker->sentence(),
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_reports', [
            'id' => $userReport->id,
            'state' => 'at_risk',
        ]);
        $this->assertDatabaseHas('user_report_resolutions', [
            'user_report_id' => $userReport->id,
            'user_id' => $user->id,
            'state' => 'safe',
        ]);
    }

    /**
     * Test to resolve a report in an incident successfully as an brigadier.  
     */
    public function test_user_can_resolve_a_report_in_an_incident_successfully_as_an_brigadier()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $brigadier_role = $this->createBrigadierRole();
        $final_user_role = $this->createFinalUserRol();
        $brigadier_user = $this->createUser($institution);
        $brigadier_user->institutions()->attach($institution->id, ['role_id' => $brigadier_role->id, 'code' => $this->faker->randomNumber(8)]);
        $final_user = $this->createUser($institution);
        $final_user->institutions()->attach($institution->id, ['role_id' => $final_user_role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $brigadier_user);
        $userReport = $this->createUserReport($incident, $final_user, 'at_risk');
        Sanctum::actingAs(
            $brigadier_user,
            ['*']
        );

        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports/$userReport->id/resolution", [
            'state' => 'safe',
            'description' => $this->faker->sentence(),
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('user_reports', [
            'id' => $userReport->id,
            'state' => 'at_risk',
        ]);
        $this->assertDatabaseHas('user_report_resolutions', [
            'user_report_id' => $userReport->id,
            'user_id' => $brigadier_user->id,
            'state' => 'safe',
        ]);
    }

    /**
     * Test to resolve a report in an incident already resolved.   
     */
    public function test_user_cannot_resolve_a_report_in_an_incident_already_resolved()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $brigadier_role = $this->createBrigadierRole();
        $final_user_role = $this->createFinalUserRol();
        $brigadier_user = $this->createUser($institution);
        $brigadier_user->institutions()->attach($institution->id, ['role_id' => $brigadier_role->id, 'code' => $this->faker->randomNumber(8)]);
        $final_user = $this->createUser($institution);
        $final_user->institutions()->attach($institution->id, ['role_id' => $final_user_role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $brigadier_user);
        $userReport = $this->createUserReport($incident, $final_user, 'at_risk');
        Sanctum::actingAs(
            $brigadier_user,
            ['*']
        );

        $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports/$userReport->id/resolution", [
            'state' => 'safe',
            'description' => $this->faker->sentence(),
        ]);

        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports/$userReport->id/resolution", [
            'state' => 'safe',
            'description' => $this->faker->sentence(),
        ]);

        $response->assertStatus(400)->assertJson([
            'message' => 'El reporte de usuario ya está resuelto',
        ]);
    }

    /**
     * Test to resolve a report in an incident with an invalid state report.    
     */
    public function test_user_cannot_resolve_a_report_in_an_incident_with_an_invalid_state_report()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $brigadier_role = $this->createBrigadierRole();
        $final_user_role = $this->createFinalUserRol();
        $brigadier_user = $this->createUser($institution);
        $brigadier_user->institutions()->attach($institution->id, ['role_id' => $brigadier_role->id, 'code' => $this->faker->randomNumber(8)]);
        $final_user = $this->createUser($institution);
        $final_user->institutions()->attach($institution->id, ['role_id' => $final_user_role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $brigadier_user);
        $userReport = $this->createUserReport($incident, $final_user);
        Sanctum::actingAs(
            $brigadier_user,
            ['*']
        );

        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports/$userReport->id/resolution", [
            'state' => 'safe',
            'description' => $this->faker->sentence(),
        ]);

        $response->assertStatus(422)->assertJson([
            'message' => 'El reporte de usuario no puede ser resuelto porque no está en riesgo',
        ]);
    }

    /**
     * Test to resolve a report in an incident as a final user.      
     */
    public function test_user_cannot_resolve_a_report_in_an_incident_as_a_final_user()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $final_user_role = $this->createFinalUserRol();
        $final_user_1 = $this->createUser($institution);
        $final_user_1->institutions()->attach($institution->id, ['role_id' => $final_user_role->id, 'code' => $this->faker->randomNumber(8)]);
        $final_user_2 = $this->createUser($institution);
        $final_user_2->institutions()->attach($institution->id, ['role_id' => $final_user_role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $final_user_1);
        $userReport = $this->createUserReport($incident, $final_user_2, 'at_risk');
        Sanctum::actingAs(
            $final_user_1,
            ['*']
        );

        $response = $this->postJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports/$userReport->id/resolution", [
            'state' => 'safe',
            'description' => $this->faker->sentence(),
        ]);

        $response->assertStatus(403);
    }
}
