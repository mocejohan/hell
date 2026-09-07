<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dictamen;
use App\Services\DictamenPdfService;

class GenerarPdfsDictamenesCommand extends Command
{
    protected $signature = 'dictamenes:generar-pdfs';
    protected $description = 'Genera y almacena físicamente en disco los archivos PDF de todos los dictámenes existentes en la base de datos.';

    public function handle()
    {
        $dictamenes = Dictamen::with('reporte')->get();

        if ($dictamenes->isEmpty()) {
            $this->info('No hay dictámenes registrados en la base de datos.');
            return 0;
        }

        $this->info("Procesando {$dictamenes->count()} dictamen(es)...");
        $bar = $this->output->createProgressBar($dictamenes->count());
        $bar->start();

        $generados = 0;
        $errores = 0;

        foreach ($dictamenes as $dictamen) {
            if ($dictamen->reporte) {
                $ruta = DictamenPdfService::guardarEnDisco($dictamen->reporte, $dictamen);
                if ($ruta) {
                    $generados++;
                } else {
                    $errores++;
                }
            } else {
                $errores++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("¡Completado! {$generados} PDF(s) generados exitosamente en storage/app/public/dictamenes/." . ($errores > 0 ? " ({$errores} con error o sin reporte asociado)" : ''));

        return 0;
    }
}