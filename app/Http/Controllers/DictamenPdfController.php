<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Services\DictamenPdfService;

class DictamenPdfController extends Controller
{
    public function show(Reporte $reporte)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Mesa-control', 'Tecnico']) && !$user->can('ImprimirDictamen')) {
            abort(403, 'No tienes permiso para consultar este dictamen.');
        }

        $reporte->loadMissing([
            'dictamenes' => fn($q) => $q->latest(),
            'categoria', 'estado', 'tecnico', 'tecnicos',
            'departamento', 'area',
        ]);

        $dictamen = $reporte->dictamenes->first();
        if (!$dictamen) {
            abort(404, 'No existe dictamen para este reporte.');
        }

        $pdf = DictamenPdfService::generarPdf($reporte, $dictamen);
        if (!$pdf) {
            abort(404, 'No se pudo generar el documento PDF del dictamen.');
        }

        // Guarda una copia física actualizada en storage/app/public/dictamenes/
        DictamenPdfService::guardarEnDisco($reporte, $dictamen);

        // Mostrar en el navegador
        return $pdf->stream("dictamen-reporte-{$reporte->id}.pdf");
    }
}