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
        $rolMesaControl = Role::create(['name' => 'Mesa-control']);
        $rolTecnico = Role::create(['name' => 'Tecnico']);
        // $rolDesarrollo = Role::create(['name' => 'Adm-desarrollador']);

        // Permission::create(['name' => 'mesa'])->syncRoles([$rolMesaControl, $rolTecnico]);
        Permission::create(['name' => 'estadisticas'])->syncRoles([$rolMesaControl]);
        Permission::create(['name' => 'consultas'])->syncRoles([$rolMesaControl]);
        Permission::create(['name' => 'administrar'])->syncRoles([$rolMesaControl]);
        Permission::create(['name' => 'NuevoReporte'])->syncRoles([$rolMesaControl]);
        Permission::create(['name' => 'ListaServicios'])->syncRoles([$rolMesaControl, $rolTecnico]);
        Permission::create(['name' => 'atendido'])->syncRoles([$rolMesaControl]);
        Permission::create(['name' => 'cancelar'])->syncRoles([$rolMesaControl]);
        Permission::create(['name' => 'comentar'])->syncRoles([$rolMesaControl, $rolTecnico]);
        Permission::create(['name' => 'ImprimirDictamen'])->syncRoles([$rolMesaControl, $rolTecnico]);
    }
}
