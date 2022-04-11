<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\Location\The\NeighborhoodZoneSeeder;
use Database\Seeders\Location\The\NeighborhoodSeeder;
use Database\Seeders\Location\The\NeighborhoodPopulationSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        /*
        User::factory(1)->create(
            [
                'name' => 'Thiago Pinto Dias',
                'email' => 'thiagopinto.lx@gmail.com',
                'phone' => '86988310563',
                'password' => bcrypt('secret')

            ]
        );

        $roles = new RolesSeeder();
        $roles->run();

        $user = User::with(['roles'])->find(1);
        $roles = Role::get();
        foreach ($roles as $role) {
            $user->roles()->attach($role->id);
        }

        $healthUnit = new HealthUnitSeeder();
        $healthUnit->run();
        */

        $cidChapterSeeder = new CidChapterSeeder();
        $cidGroupSeeder = new CidGroupSeeder();
        $cidCategorySeeder = new CidCategorySeeder();
        $cidSeeder = new CidSeeder();
        $cboDatasusSeeder = new CboDatasusSeeder();

        $cidChapterSeeder->run();
        $cidGroupSeeder->run();
        $cidCategorySeeder->run();
        $cidSeeder->run();
        $cboDatasusSeeder->run();


        /**
         * NeighborhoodZonesSeeder
         * NeighborhoodSeeder
         * exlusivo para dvs
         */

        $neighborhoodZoneSeeder = new NeighborhoodZoneSeeder();
        $neighborhoodSeeder = new NeighborhoodSeeder();
        $neighborhoodPopulationSeeder = new NeighborhoodPopulationSeeder();
        $neighborhoodZoneSeeder->run();
        $neighborhoodSeeder->run();
        $neighborhoodPopulationSeeder->run();
    }
}
