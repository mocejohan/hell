<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reporte;
use App\Models\Dictamen;

class DictamenController extends Controller
{
    public function create()
    {
        // Puedes filtrar a “abiertos” o “cerrados”, según tu flujo
        $reportes = Reporte::orderByDesc('created_at')->get(['id', 'descripcion']);
        return view('dictamenes.create', compact('reportes'));
    }

    public function store(Request $request)
    {
    $rules = [
        'reporte_id'   => ['required', 'exists:reportes,id'],
        'inventario'   => ['required', 'string', 'max:255'],
        'equipo'       => ['required', 'string', 'max:255'],
        'marca'        => ['required', 'string', 'max:255'],
        'modelo'       => ['required', 'string', 'max:255'],
        'serie'        => ['required', 'string', 'max:255'],
        'diagnostico'  => ['required', 'string'],
        'sugerencia'   => ['required', 'string'],
        'observaciones'=> ['nullable', 'string'],
    ];

    $messages = [
        'required'           => 'El campo :attribute es obligatorio.',
        'max'                => 'El campo :attribute no debe exceder :max caracteres.',
        'exists'             => 'El :attribute seleccionado no es válido.',
        'string'             => 'El campo :attribute debe ser texto.',
    ];

    $attributes = [
        'reporte_id'   => 'reporte',
        'inventario'   => 'inventario',
        'equipo'       => 'equipo',
        'marca'        => 'marca',
        'modelo'       => 'modelo',
        'serie'        => 'serie',
        'diagnostico'  => 'diagnóstico',
        'sugerencia'   => 'sugerencia',
        'observaciones'=> 'observaciones',
    ];

    $validated = $request->validate($rules, $messages, $attributes);

    Dictamen::create($validated);

    return redirect()
        ->route('dictamen')
        ->with('ok', 'Dictamen registrado correctamente.');
    }
}
