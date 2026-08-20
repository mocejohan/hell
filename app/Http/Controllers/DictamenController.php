<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reporte;
use App\Models\Dictamen;

class DictamenController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Dictamen::with(['reporte.categoria', 'reporte.departamento', 'reporte.tecnico', 'reporte.tecnicos', 'reporte.estado'])
            ->latest();

        // Si es Técnico (y no Mesa-control), ver únicamente los dictámenes de sus reportes
        if ($user->hasRole('Tecnico') && !$user->hasRole('Mesa-control')) {
            $uid = $user->id;
            $query->whereHas('reporte', function ($q) use ($uid) {
                $q->where('tecnico_user_id', $uid)
                  ->orWhereHas('tecnicos', fn($t) => $t->where('users.id', $uid));
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('inventario', 'like', "%{$search}%")
                  ->orWhere('equipo', 'like', "%{$search}%")
                  ->orWhere('marca', 'like', "%{$search}%")
                  ->orWhere('serie', 'like', "%{$search}%")
                  ->orWhereHas('reporte', function ($rq) use ($search) {
                      $rq->where('solicitante', 'like', "%{$search}%")
                         ->orWhere('id', 'like', "%{$search}%");
                  });
            });
        }

        $dictamenes = $query->paginate(10)->withQueryString();

        return view('dictamen', compact('dictamenes'));
    }

    public function create()
    {
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
            'required' => 'El campo :attribute es obligatorio.',
            'max'      => 'El campo :attribute no debe exceder :max caracteres.',
            'exists'   => 'El :attribute seleccionado no es válido.',
            'string'   => 'El campo :attribute debe ser texto.',
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

        $reporte = Reporte::find($request->reporte_id);
        if ($reporte && $reporte->estado_id == 1) {
            $reporte->estado_id = 2; // Atendido
            $reporte->save();
        }

        return redirect()
            ->route('dictamen')
            ->with('ok', 'Dictamen registrado correctamente.');
    }
}
