<?php

namespace Database\Seeders;

use App\Models\Bien;
use Illuminate\Database\Seeder;

class BienSeeder extends Seeder
{
    /**
     * Importa bienes de informática desde inventarios_informatica.json
     * Usa updateOrCreate para poder re-ejecutarse sin duplicar registros.
     */
    public function run(): void
    {
        $path = database_path('data/inventarios_informatica.json');

        if (!file_exists($path)) {
            $this->command->error("No se encontró el archivo: {$path}");
            return;
        }

        $items = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Error al decodificar JSON: ' . json_last_error_msg());
            return;
        }

        $count = 0;

        foreach ($items as $item) {
            Bien::updateOrCreate(
                ['numero_inventario' => $item['numero_inventario']],
                [
                    'numero_inventario_anterior' => $item['numero_inventario_anterior'] ?? null,
                    'equipo'    => $item['equipo'] ?? null,
                    'marca'     => $item['marca'] ?? null,
                    'modelo'    => $item['modelo'] ?? null,
                    'serie'     => $item['serie'] ?? null,
                    'ubicacion' => $item['ubicacion'] ?? null,
                ]
            );
            $count++;
        }

        $this->command->info("Se importaron/actualizaron {$count} bienes de informática.");
    }
}
