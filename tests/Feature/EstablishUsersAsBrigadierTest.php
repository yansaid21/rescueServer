<?php

namespace Tests\Feature;

use Tests\TestCase;

class EstablishUsersAsBrigadierTest extends TestCase
{
    /**
     * Test to establish a user as a brigadier successfully.
     */
    public function test_establish_user_as_brigadier_successfully(): void
    {
        $institution = $this->createInstitution();
        $role = $this->createFinalUserRol();
        $this->createBrigadierRole();
        $this->createUser($institution);
        $user_1 = $this->createUser($institution);
        $user_2 = $this->createUser();

        $user_1->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $user_2->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);

        $response = $this->postJson('/api/institutions/' . $institution->id . '/brigadiers', [
            'users' => [$user_1->id, $user_2->id],
        ]);

        $response->assertSuccessful();
    }

    /**
     * Test to establish a user as a brigadier with an invalid institution.
     */
    public function test_establish_user_as_brigadier_with_invalid_institution(): void
    {
        $response = $this->postJson('/api/institutions/1/brigadiers', [
            'users' => [1, 2],
        ]);
        $response->assertStatus(404);
    }

    /**
     * Test to establish a user as a brigadier with an invalid user.
     */
    public function test_establish_user_as_brigadier_with_invalid_user(): void
    {
        $institution = $this->createInstitution();
        $response = $this->postJson('/api/institutions/' . $institution->id . '/brigadiers', [
            'users' => [1],
        ]);
        $response->assertJsonValidationErrorFor('users.0');
    }

    /**
     * Test to establish a user as a brigadier with a user that is already a brigadier.
     */
    public function test_establish_user_as_brigadier_with_user_that_is_already_a_brigadier(): void
    {
        $institution = $this->createInstitution();
        $role = $this->createBrigadierRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);
        $response = $this->postJson('/api/institutions/' . $institution->id . '/brigadiers', [
            'users' => [$user->id],
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('institution_users', [
            'user_id' => $user->id,
            'institution_id' => $institution->id,
            'role_id' => $role->id,
        ]);
    }

    /**
     * Test to establish a user as a brigadier with a user that as the rol of administrator.
     */
    public function test_establish_user_as_brigadier_with_user_that_as_the_rol_of_administrator(): void
    {
        $institution = $this->createInstitution();
        $role = $this->createAdministratorRole();
        $this->createBrigadierRole();
        $user = $this->createUser($institution);
        $user->institutions()->attach($institution->id, ['role_id' => $role->id, 'code' => $this->faker->randomNumber(8)]);

        $response = $this->postJson('/api/institutions/' . $institution->id . '/brigadiers', [
            'users' => [$user->id],
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('institution_users', [
            'user_id' => $user->id,
            'institution_id' => $institution->id,
            'role_id' => $role->id,
        ]);
    }

    /**
     * Test to establish a user as a brigadier with a user that is not part of the institution.
     */
    public function test_establish_user_as_brigadier_with_user_that_is_not_part_of_the_institution(): void
    {
        $institution = $this->createInstitution();
        $this->createBrigadierRole();
        $user = $this->createUser();

        $response = $this->postJson('/api/institutions/' . $institution->id . '/brigadiers', [
            'users' => [$user->id],
        ]);

        $response->assertJsonValidationErrorFor('users.0');
    }
}
