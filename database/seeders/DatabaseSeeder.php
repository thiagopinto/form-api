<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        
        User::factory(1)->create(
            [
                'name' => 'Thiago Pinto Dias',
                'email' => 'thiagopinto.lx@gmail.com',
                'phone' => '86988310563',
                'password' => bcrypt('secret')

            ]
        );
        
        $roles = new RolesSeeder;
        $roles->run();

        $user = User::with(['roles'])->find(1);
        $roles = Role::get(); 
        foreach ($roles as $role) {
            $user->roles()->attach($role->id);
        }

        $healthUnit = new HealthUnitSeeder;
        $healthUnit->run();
    }
}
