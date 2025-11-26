<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Barryvdh\DomPDF\Facade\Pdf;

class DictamenPdfController extends Controller
{
    public function show(Reporte $reporte)
    {
        // carga el logo
        // Carga el reporte con su dictamen (ej. el más reciente)
        $reporte->load([
            'dictamenes' => fn($q) => $q->latest(), // o filtra como necesites
            'categoria', 'estado', 'tecnico', 'tecnicos',
            'departamento', 'area', // si tienes estas relaciones
        ]);

        $dictamen = $reporte->dictamenes->first();
        if (!$dictamen) {
            // Sin dictamen → 404 o redirige con mensaje
            abort(404, 'No existe dictamen para este reporte.');
        }

        $pdf = Pdf::loadView('pdf.dictamen', [
                'reporte'  => $reporte,
                'dictamen' => $dictamen,
            ])
            ->setPaper('letter'); // 'a4', 'letter', orientación 'portrait'/'landscape' si quieres

        // Mostrar en el navegador
        return $pdf->stream("dictamen-reporte-{$reporte->id}.pdf");

        // Si prefieres descargar:
        // return $pdf->download("dictamen-reporte-{$reporte->id}.pdf");
    }
}
