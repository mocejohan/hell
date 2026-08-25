<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Evento;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            AreasInformaticaSeeder::class,
            RolSeeder::class,
            UsuariosSeeder::class,
            CategoriasSeeder::class,
            DepartamentosCongresoSeeder::class,
            EstadosSeeder::class,
            // ReportesSeeder::class,
            // ComentariosSeeder::class,
            EventosSeeder::class,
            BienSeeder::class,
            // ReportesFactorySeeder::class,

            // Otros seeders pueden ser llamados aquí
        ]);
    }
}
