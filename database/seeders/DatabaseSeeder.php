<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\Level;
use App\Models\MeetPoint;
use App\Models\RiskSituation;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin_role = Role::create([
            'name' => 'Administrator',
            'description' => 'Administrator of risk situations in the institution'
        ]);

        Role::create([
            'name' => 'Brigadier',
            'description' => 'Brigadier of the institution'
        ]);

        Role::create([
            'name' => 'Final User',
            'description' => 'Final user that are part of the institution'
        ]);

        $institution = Institution::create([
            'name' => 'Universidad Autonoma de Manizales',
            'description' => 'Universidad Autonoma de Manizales',
            'is_active' => true
        ]);
        error_log(json_encode($institution));

        Level::create([
            'name' => 'Piso 1',
            'description' => 'Piso 1',
            'institution_id' => 1
        ]);

        Level::create([
            'name' => 'Piso 2',
            'description' => 'Piso 2',
            'institution_id' => 1
        ]);

        Level::create([
            'name' => 'Piso 3',
            'description' => 'Piso 3',
            'institution_id' => 1
        ]);

        Level::create([
            'name' => 'Piso 4',
            'description' => 'Piso 4',
            'institution_id' => 1
        ]);

        Level::create([
            'name' => 'Piso 5',
            'description' => 'Piso 5',
            'institution_id' => 1
        ]);

        $user = User::create([
            'name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@autonoma.edu.co',
            'password' => bcrypt('admin1234'),
            'id_card' => 123456789,
            'rhgb' => 'O+',
            'social_security' => 'UAM',
            'phone_number' => '3568415980',
            'is_active' => true,
        ]);

        $user->institutions()->attach($institution->id, ['code' => 'UAM', 'role_id' => $admin_role->id]);

        RiskSituation::create([
            'name' => 'Incendio',
            'description' => 'Incendio',
            'institution_id' => 1
        ]);

        MeetPoint::create([
            'name' => 'Punto de encuentro 1',
            'description' => 'Punto de encuentro 1',
            'institution_id' => 1
        ]);

        // uncomment to run the listed seeders after this one.
        $this->call([
            UsersTableSeeder::class,
        ]);
    }
}
