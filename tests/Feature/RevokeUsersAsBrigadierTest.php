<?php

namespace Tests\Feature;

use Tests\TestCase;

class RevokeUsersAsBrigadierTest extends TestCase
{
    /**
     * Test to revoke users as brigadier successfully.
     */
    public function test_revoke_users_as_brigadier_successfully(): void
    {
        $institution = $this->createInstitution();
        $this->createFinalUserRol();
        $role = $this->createBrigadierRole();
        $user_1 = $this->createUser();
        $user_2 = $this->createUser();
        $user_1->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $user_2->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);

        $response = $this->deleteJson('/api/institutions/' . $institution->id . '/brigadiers', [
            'users' => [$user_1->id, $user_2->id],
        ]);
        $response->assertSuccessful();
    }

    /**
     * Test to revoke users as brigadier with a user that is not a brigadier.
     */
    public function test_revoke_users_as_brigadier_with_user_that_is_not_a_brigadier(): void
    {
        $institution = $this->createInstitution();
        $final_role = $this->createFinalUserRol();
        $this->createBrigadierRole();
        $admin_role = $this->createAdministratorRole();
        $admin_user = $this->createUser($institution);
        $final_user = $this->createUser($institution);
        $final_user->institutions()->attach($institution->id, ['role_id' => $final_role->id, 'code' => $this->faker->randomNumber(8)]);
        $admin_user->institutions()->attach($institution->id, ['role_id' => $admin_role->id, 'code' => $this->faker->randomNumber(8)]);

        $response = $this->deleteJson('/api/institutions/' . $institution->id . '/brigadiers', [
            'users' => [$admin_user->id, $final_user->id],
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('institution_users', [
            'user_id' => $admin_user->id,
            'institution_id' => $institution->id,
            'role_id' => $admin_role->id,
        ]);
        $this->assertDatabaseHas('institution_users', [
            'user_id' => $final_user->id,
            'institution_id' => $institution->id,
            'role_id' => $final_role->id,
        ]);
    }

    /**
     * Test to revoke users as brigadier successfully when the brigadiers has meet points related.
     * Send the force parameter as 1.
     */
    public function test_revoke_users_as_brigadier_successfully_when_the_brigadiers_has_meet_points_related(): void
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $meetPoint = $this->createMeetPoint($institution, $zone);
        $this->createFinalUserRol();
        $role = $this->createBrigadierRole();
        $user_1 = $this->createUser();
        $user_2 = $this->createUser();
        $user_1->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $user_2->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $this->createMeetPointBrigadier($user_1, $meetPoint);
        $this->createMeetPointBrigadier($user_2, $meetPoint);
        $response = $this->deleteJson('/api/institutions/' . $institution->id . '/brigadiers?force=1', [
            'users' => [$user_1->id, $user_2->id],
        ]);

        $response->assertSuccessful();
    }

    /**
     * Test to revoke users as brigadier when the brigadiers has meet points related.
     * Without send the force parameter.
     */
    public function test_revoke_users_as_brigadier_when_the_brigadiers_has_meet_points_related_without_send_force_parameter(): void
    {
        $institution = $this->createInstitution();
        $zone = $this->createZone($institution);
        $meetPoint = $this->createMeetPoint($institution, $zone);
        $this->createFinalUserRol();
        $role = $this->createBrigadierRole();
        $user_1 = $this->createUser();
        $user_2 = $this->createUser();
        $user_1->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $user_2->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $this->createMeetPointBrigadier($user_1, $meetPoint);
        $this->createMeetPointBrigadier($user_2, $meetPoint);

        $response = $this->deleteJson('/api/institutions/' . $institution->id . '/brigadiers', [
            'users' => [$user_1->id, $user_2->id],
        ]);

        $response->assertStatus(400)->assertJsonStructure([
            'message',
            'errors'
        ]);
    }

    /**
     * Test to revoke users as brigadier with an invalid institution.
     */
    public function test_revoke_users_as_brigadier_with_invalid_institution(): void
    {
        $response = $this->deleteJson('/api/institutions/1/brigadiers', [
            'users' => [1, 2],
        ]);
        $response->assertStatus(404);
    }

    /**
     * Test to revoke users as brigadier with an invalid user.
     */
    public function test_revoke_users_as_brigadier_with_invalid_user(): void
    {
        $institution = $this->createInstitution();
        $response = $this->deleteJson('/api/institutions/' . $institution->id . '/brigadiers', [
            'users' => [1],
        ]);
        $response->assertJsonValidationErrorFor('users.0');
    }


    /**
     * Test to revoke users as brigadier with a user that is not part of the institution.
     */
    public function test_revoke_users_as_brigadier_with_user_that_is_not_part_of_the_institution(): void
    {
        $institution = $this->createInstitution();
        $this->createFinalUserRol();
        $user = $this->createUser();
        $response = $this->deleteJson('/api/institutions/' . $institution->id . '/brigadiers', [
            'users' => [$user->id],
        ]);
        $response->assertJsonValidationErrorFor('users.0');
    }
}
