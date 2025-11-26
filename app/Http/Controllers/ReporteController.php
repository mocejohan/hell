<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Reporte $reporte)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reporte $reporte)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reporte $reporte)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reporte $reporte)
    {
        //
    }

    public function showBasic($id)
    {
        $reporte = Reporte::with(['departamento', 'estado', 'tecnico'])
            ->find($id);

        if (!$reporte) {
            return response()->json(['ok' => false, 'message' => 'Reporte no encontrado.'], 404);
        }

        return response()->json([
            'ok'   => true,
            'data' => [
                'id'                       => $reporte->id,
                'departamento_congreso_id' => $reporte->departamento_congreso_id,
                'departamento_nombre'      => optional($reporte->departamento)->name,
                'solicitante'              => $reporte->solicitante,
                'tecnico_user_id'          => $reporte->tecnico_user_id,
                'tecnico_nombre'           => optional($reporte->tecnico)->name,
                'numero_inventario'        => $reporte->numero_inventario,
                'estado_id'                => $reporte->estado_id,
                'estado_nombre'            => optional($reporte->estado)->name,
                'created_at'               => optional($reporte->created_at)?->format('d/m/Y H:i'),
                'updated_at'               => optional($reporte->updated_at)?->format('d/m/Y H:i'),
            ],
        ]);
    }
}
