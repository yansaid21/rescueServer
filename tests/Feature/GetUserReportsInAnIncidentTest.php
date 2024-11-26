<?php

namespace Tests\Feature;

use Tests\TestCase;

class GetUserReportsInAnIncidentTest extends TestCase
{
    /**
     * Test to get all reports in an incident. 
     */
    public function test_a_user_can_get_all_reports_in_an_incident()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createFinalUserRol();
        $user_1 = $this->createUser($institution);
        $user_2 = $this->createUser($institution);
        $user_1->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $user_2->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $user_1);
        $this->createUserReport($incident, $user_1);
        $this->createUserReport($incident, $user_2);

        $response = $this->getJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports");
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'incident_id',
                    'user_id',
                    'description',
                    'state',
                    'created_at',
                    'updated_at',
                    'user',
                    'zone',
                    'resolution',
                ]
            ],
            'pagination' => [
                'total',
                'per_page',
                'current_page',
                'total_pages',
                'last_page',
                'next_page_url',
                'prev_page_url',
            ]
        ]);
    }

    /**
     * Test to get all reports in an incident with a specific state. 
     */
    public function test_a_user_can_get_all_reports_in_an_incident_with_a_specific_state()
    {
        $institution = $this->createInstitution();
        $riskSituation = $this->createRiskSituation($institution);
        $role = $this->createFinalUserRol();
        $user_1 = $this->createUser($institution);
        $user_2 = $this->createUser($institution);
        $user_1->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $user_2->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $incident = $this->createInitialIncident($riskSituation, $user_1);
        $userReport_1 = $this->createUserReport($incident, $user_1);
        $userReport_2 = $this->createUserReport($incident, $user_2, 'at_risk');

        $response = $this->getJson("/api/institutions/$institution->id/risk_situations/$riskSituation->id/incidents/$incident->id/user_reports?state=at_risk");
        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment([
            'id' => $userReport_2->id,
            'state' => 'at_risk',
        ]);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'incident_id',
                    'user_id',
                    'description',
                    'state',
                    'created_at',
                    'updated_at',
                    'user',
                ]
            ],
            'pagination' => [
                'total',
                'per_page',
                'current_page',
                'total_pages',
                'last_page',
                'next_page_url',
                'prev_page_url',
            ]
        ]);
    }
}
