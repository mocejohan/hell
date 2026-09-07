<?php

namespace App\Services;

use App\Models\Reporte;
use App\Models\Dictamen;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class DictamenPdfService
{
    /**
     * Genera la instancia DomPDF para un reporte y su dictamen correspondiente.
     */
    public static function generarPdf(Reporte $reporte, ?Dictamen $dictamen = null)
    {
        $reporte->loadMissing([
            'dictamenes' => fn($q) => $q->latest(),
            'categoria', 'estado', 'tecnico', 'tecnicos',
            'departamento', 'area',
        ]);

        $dictamen = $dictamen ?? $reporte->dictamenes->first();
        if (!$dictamen) {
            return null;
        }

        return Pdf::loadView('pdf.dictamen', [
            'reporte'  => $reporte,
            'dictamen' => $dictamen,
        ])->setPaper('letter');
    }

    /**
     * Genera y guarda físicamente el archivo .pdf en el disco (storage/app/public/dictamenes/).
     * Retorna la ruta relativa en disco (ej. 'dictamenes/dictamen-reporte-123.pdf').
     */
    public static function guardarEnDisco(Reporte $reporte, ?Dictamen $dictamen = null): ?string
    {
        try {
            $pdf = self::generarPdf($reporte, $dictamen);
            if (!$pdf) {
                return null;
            }

            $nombreArchivo = "dictamenes/dictamen-reporte-{$reporte->id}.pdf";
            Storage::disk('public')->put($nombreArchivo, $pdf->output());

            return $nombreArchivo;
        } catch (\Exception $e) {
            \Log::error("Error al guardar PDF de dictamen en disco: " . $e->getMessage());
            return null;
        }
    }
}