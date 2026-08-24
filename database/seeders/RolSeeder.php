<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;


class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \ = Role::create(['name' => 'Mesa-control']);
        \ = Role::create(['name' => 'Tecnico']);

        Permission::create(['name' => 'estadisticas'])->syncRoles([\]);
        Permission::create(['name' => 'consultas'])->syncRoles([\]);
        Permission::create(['name' => 'administrar'])->syncRoles([\]);
        Permission::create(['name' => 'NuevoReporte'])->syncRoles([\]);
        Permission::create(['name' => 'ListaServicios'])->syncRoles([\, \]);
        Permission::create(['name' => 'atendido'])->syncRoles([\, \]);
        Permission::create(['name' => 'cancelar'])->syncRoles([\, \]);
        Permission::create(['name' => 'comentar'])->syncRoles([\, \]);
        Permission::create(['name' => 'ImprimirDictamen'])->syncRoles([\, \]);
        Permission::create(['name' => 'cerrarSolicitud'])->syncRoles([\]);
        Permission::create(['name' => 'dictaminar'])->syncRoles([\, \]);
    }
}
