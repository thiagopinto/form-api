<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            ['name' => 'guest', 'alias' => 'Visitante', 'description' => 'Recurso liberado a todos'],
            ['name' => 'staff', 'alias' => 'Colaborador', 'description' => 'Recurso requer autenticação'],
            ['name' => 'admin', 'alias' => 'Administrador', 'description' => 'Recurso requer autenticação e permissão de administrador']
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
