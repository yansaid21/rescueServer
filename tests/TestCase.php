<?php

namespace Tests;

use App\Models\Institution;
use App\Models\MeetPoint;
use App\Models\MeetPointBrigadier;
use App\Models\Role;
use App\Models\User;
use App\Models\UserReport;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase, WithFaker;

    public function createInstitution()
    {
        return Institution::factory()->create();
    }

    public function createZone($institution)
    {
        return $institution->zones()->create([
            'name' => $this->faker->name,
        ]);
    }

    public function createMeetPoint($institution, $zone)
    {
        $meetpoint = MeetPoint::create([
            'institution_id' => $institution->id,
            'name' => 'Parque de los estudiantes',
        ]);
        $meetpoint->zones()->attach($zone->id);
        return $meetpoint;
    }

    public function createLevel($institution)
    {
        return $institution->levels()->create([
            'name' => $this->faker->name,
        ]);
    }

    public function createRoom($zone, $level)
    {
        return $zone->rooms()->create([
            'name' => $this->faker->name,
            'level_id' => $level->id,
            'code' => '101',
        ]);
    }

    public function createFinalUserRol()
    {
        return Role::create([
            'name' => 'Final User',
            'description' => 'Final user that are part of the institution'
        ]);
    }

    public function createBrigadierRole()
    {
        return Role::create([
            'name' => 'Brigadier',
            'description' => 'Brigadier of the institution'
        ]);
    }

    public function createAdministratorRole()
    {
        return Role::create([
            'name' => 'Administrator',
            'description' => 'Administrator of the institution'
        ]);
    }

    public function createUser($institution = null)
    {
        if ($institution) {
            return User::create([
                'name' => $this->faker->name,
                'last_name' => $this->faker->lastName,
                'email' => $this->faker->email,
                'password' => bcrypt('password'),
                'id_card' => $this->faker->randomNumber(8),
                'institution_id' => $institution->id,
            ]);
        } else {
            return User::create([
                'name' => $this->faker->name,
                'last_name' => $this->faker->lastName,
                'email' => $this->faker->email,
                'password' => bcrypt('password'),
                'id_card' => $this->faker->randomNumber(8),
            ]);
        }
    }

    public function createMeetPointBrigadier($brigadier, $meetPoint, $incident = null)
    {
        return MeetPointBrigadier::create([
            'brigadier_id' => $brigadier->id,
            'meet_point_id' => $meetPoint->id,
            'incident_id' => $incident ? $incident->id : null,
        ]);
    }

    public function createRiskSituation($institution)
    {
        return $institution->riskSituations()->create([
            'name' => $this->faker->name,
            'description' => $this->faker->text,
        ]);
    }

    public function createInitialIncident($riskSituation, $user)
    {
        return $riskSituation->incidents()->create([
            'initial_date' => now(),
            'informer_id' => $user->id,
        ]);
    }

    public function createTotalIncident($riskSituation, $user)
    {
        return $riskSituation->incidents()->create([
            'initial_date' => now(),
            'final_date' => now(),
            'description' => $this->faker->text,
            'informer_id' => $user->id,
        ]);
    }

    public function createUserReport($incident, $user, $state = 'safe', $zone = null)
    {
        return UserReport::create([
            'incident_id' => $incident->id,
            'user_id' => $user->id,
            'state' => $state,
            'description' => 'The user is feeling bad',
            'zone_id' => $zone ? $zone->id : null,
        ]);
    }
}
