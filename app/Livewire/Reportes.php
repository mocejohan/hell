<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Reporte, Comentario, DepartamentoCongreso, AreasInformatica, Categoria, User, Evento, Bien, Dictamen};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ReporteEstadoNotificacion;
use Spatie\Permission\Models\Role;

use Livewire\Attributes\On;

class Reportes extends Component
{

    use WithPagination;

    // Modal
    public bool $showCreateModal = false;

    public int $totalPendientes = 0;
    public int $totalAtendidos = 0;

    // Modal Atendido
    public ?int $atendidoReporteId = null;
    public ?int $atendidoCategoriaId = null;

    public bool $showAtendidoModal = false;
    public ?int $atendidoTecnicoId = null;

    public array $atendidoTecnicoIds = [];
    // Modal Dictamen
    public bool $showDictamenModal = false;
    public ?int $dictamenReporteId = null;
    public string $dictamenInventario = '';
    public string $dictamenEquipo = '';
    public string $dictamenMarca = '';
    public string $dictamenModelo = '';
    public string $dictamenSerie = '';
    public string $dictamenDiagnostico = '';
    public string $dictamenSugerencia = '';
    public string $dictamenObservaciones = '';
    public array $bienesSugerencias = [];


    // --- estado del modal Cerrar ---
    public bool $showCerrarModal = false;
    public ?int $cerrarReporteId = null;

    // 🆕 Modal comentar
    public bool $showComentarioModal = false;
    public ?int $comentarioReporteId = null;
    public string $comentarioTexto = '';

    // Modal Cancelar
    public bool $showCancelarModal = false;
    public ?int $cancelarReporteId = null;
    public string $cancelarComentario = '';

    public $categoriasFiltradas = [];

    // Formulario
    public array $nuevoReporte = [
        'departamento_id'   => '',
        'solicitante'   => '',
        'descripcion'   => '',
        'area_informatica_id' => '',
        'categoria_id'  => '',
        'tecnico_id'    => '',
        'numero_copias'  => '',
        'numero_inventario'  => '',
        'evento_id'  => '',
    ];

    public function rules()
    {
        return [
            'nuevoReporte.departamento_id'      => 'required|exists:departamento_congreso,id',
            'nuevoReporte.solicitante'          => 'required|string|max:255',
            'nuevoReporte.descripcion'          => 'required|string|min:3',
            'nuevoReporte.area_informatica_id'  => 'required|exists:area_informatica,id',
            'nuevoReporte.categoria_id'         => 'required|exists:categorias,id',
            'nuevoReporte.tecnico_id'           => 'required|exists:users,id',
            'nuevoReporte.numero_copias'        => 'nullable|integer|min:1',
            'nuevoReporte.numero_inventario'    => 'nullable|string|max:10',
            'nuevoReporte.evento_id'            => 'nullable|exists:eventos,id',
        ];
    }


    protected $listeners = ['abrirModalAtendido', 'cerrarModalAtendido', 'guardarAtendido', 'abrirModalComentario', 'refrescarComentarios', 'abrirModalCerrar', 'abrirModalCancelar', 'abrirModalDictamen'];

    public function abrirModalAtendido(int $id)
    {
        $reporte = Reporte::with('tecnicos')->findOrFail($id);

        $this->atendidoReporteId   = $id;
        $this->atendidoCategoriaId = $reporte->categoria_id;     // preselecciona la actual
        $this->atendidoTecnicoId   = $reporte->tecnico_user_id;  // preselecciona el actual

        $this->atendidoTecnicoIds = $reporte->tecnicos->pluck('id')->toArray();

        $this->resetValidation();
        $this->showAtendidoModal = true;
    }

    public function cerrarModalAtendido()
    {
        $this->showAtendidoModal = false;
        $this->atendidoReporteId = null;
        $this->atendidoCategoriaId = null;
        $this->atendidoTecnicoId = null;
    }

    public function guardarAtendido()
    {

        $this->validate([
            'atendidoCategoriaId' => 'required|exists:categorias,id',
            'atendidoTecnicoIds'  => 'required|array|min:1',
            'atendidoTecnicoIds.*' => 'exists:users,id',
        ], [
            'atendidoTecnicoIds.required'  => 'Debes seleccionar al menos un técnico.',
            'atendidoTecnicoIds.min'       => 'Debes seleccionar al menos un técnico.',
            'atendidoTecnicoIds.*.exists'  => 'Uno de los técnicos seleccionados no es válido.',
        ]);

        $reporte = Reporte::findOrFail($this->atendidoReporteId);

        // Estado + categoría
        $reporte->estado_id    = 2; // Atendido
        $reporte->categoria_id = $this->atendidoCategoriaId;

        // (opcional) setear técnico principal al primero del checklist, si hay alguno
        $reporte->tecnico_user_id = !empty($this->atendidoTecnicoIds)
            ? $this->atendidoTecnicoIds[0]
            : $reporte->tecnico_user_id; // o null si quieres limpiarlo

        $reporte->save();

        // Sincronizar pivote con los técnicos seleccionados
        $reporte->tecnicos()->sync($this->atendidoTecnicoIds ?? []);

        // refrescar la card del hijo
        $this->dispatch('refrescarComentarios', id: $reporte->id);

        // Notificar a usuarios de Mesa-control
        $usuariosMesa = User::role('Mesa-control')->get();
        Notification::send($usuariosMesa, new ReporteEstadoNotificacion($reporte, 'Atendido', auth()->user()->name));

        $this->cerrarModalAtendido();
        session()->flash('ok', 'Reporte marcado como Atendido. Categoría y técnicos actualizados.');
    }

    public function abrirModalCrear()
    {
        $this->resetValidation();
        $this->showCreateModal = true;
    }

    public function cerrarModalCrear()
    {
        $this->showCreateModal = false;
    }

    public function guardarNuevoReporte()
    {

        $this->validate();

        DB::transaction(function () {
            // 1) Crear el reporte
            $reporte = Reporte::create([
                'departamento_congreso_id' => $this->nuevoReporte['departamento_id'],
                'solicitante'              => $this->nuevoReporte['solicitante'],
                'descripcion'              => $this->nuevoReporte['descripcion'],
                'area_informatica_id'      => $this->nuevoReporte['area_informatica_id'],
                'categoria_id'             => $this->nuevoReporte['categoria_id'],
                'tecnico_user_id'          => $this->nuevoReporte['tecnico_id'] ?: null, // principal
                'capturo_user_id'          => auth()->id(),
                'estado_id'                => 1,
                'numero_copias'            => $this->nuevoReporte['numero_copias'] ?: null,
                'numero_inventario'        => $this->nuevoReporte['numero_inventario'] ?: null,
                'evento_id'                => $this->nuevoReporte['evento_id'] ?: null,
            ]);

            // 2) Guardar también en la pivote (si viene técnico)
            if (!empty($this->nuevoReporte['tecnico_id'])) {
                // evita duplicados si existe unique(reporte_id,user_id)
                $reporte->tecnicos()->syncWithoutDetaching([
                    $this->nuevoReporte['tecnico_id'],
                ]);
            }

            // (Opcional) Si se capturan múltiples técnicos en el form:
            // $reporte->tecnicos()->sync($this->nuevoReporte['tecnico_ids'] ?? []);
        });

        $this->reset('nuevoReporte');
        $this->cerrarModalCrear();
        session()->flash('ok', 'Reporte creado con éxito.');
        $this->resetPage();
    }


    public function abrirModalComentario(int $id)
    {
        $this->comentarioReporteId = $id;
        $this->comentarioTexto = '';
        $this->resetValidation();
        $this->showComentarioModal = true;
    }

    public function cerrarModalComentario()
    {
        $this->showComentarioModal = false;
        $this->comentarioReporteId = null;
        $this->comentarioTexto = '';
    }

    public function guardarComentario()
    {
        $this->validate([
            'comentarioTexto' => 'required|string|min:2|max:2000',
            'comentarioReporteId' => 'required|exists:reportes,id',
        ], [
            'comentarioTexto.required' => 'Escribe tu comentario.',
            'comentarioTexto.min'      => 'El comentario es muy corto.',
        ]);

        Comentario::create([
            'reporte_id' => $this->comentarioReporteId,
            'user_id'    => auth()->id(),
            'comentario' => $this->comentarioTexto,
        ]);

        // Notificar al hijo para refrescar su lista de comentarios
        $this->dispatch('refrescarComentarios', id: $this->comentarioReporteId);

        $this->cerrarModalComentario();
        session()->flash('ok', 'Comentario agregado.');
    }

    public function abrirModalCerrar(int $id)
    {
        $this->cerrarReporteId = $id;
        $this->showCerrarModal = true;
    }

    public function cerrarModalCerrar()
    {
        $this->showCerrarModal = false;
        $this->cerrarReporteId = null;
    }

    public function confirmarCierre()
    {
        $reporte = Reporte::findOrFail($this->cerrarReporteId);

        // si ya está cerrado, no hagas doble cierre
        if ($reporte->estado_id !== 3) {
            $reporte->estado_id = 3;         // Cerrado
            $reporte->closed_at = now();
            $reporte->save();
        }

        // refresca la card que corresponde
        $this->dispatch('refrescarComentarios', id: $reporte->id);

        $this->cerrarModalCerrar();
        session()->flash('ok', 'Reporte cerrado correctamente.');
    }


    public function abrirModalCancelar(int $id)
    {
        $this->cancelarReporteId = $id;
        $this->showCancelarModal = true;
    }

    public function cerrarModalCancelar()
    {
        $this->showCancelarModal = false;
        $this->cancelarReporteId = null;
    }

    public function confirmarCancelar()
    {
        $reporte = Reporte::findOrFail($this->cancelarReporteId);

        if ($reporte->estado_id !== 4) { // 4 = Cancelado
            $reporte->estado_id = 4;
            $reporte->save();
        }

        // Guardar comentario ligado al reporte
        Comentario::create([
            'reporte_id' => $reporte->id,
            'user_id'    => auth()->id(),
            'comentario' => '[Cancelación] ' . $this->cancelarComentario,
        ]);

        // refrescar la card del hijo
        $this->dispatch('refrescarComentarios', id: $reporte->id);

        // Notificar a usuarios de Mesa-control
        $usuariosMesa = User::role('Mesa-control')->get();
        Notification::send($usuariosMesa, new ReporteEstadoNotificacion($reporte, 'Cancelado', auth()->user()->name));

        $this->cerrarModalCancelar();
        session()->flash('ok', 'Reporte cancelado correctamente.');
    }

    public function updatedNuevoReporteAreaInformaticaId($areaId)
    {
        // dd($areaId);
        $this->nuevoReporte['categoria_id'] = ''; // reset selección

        if (empty($areaId)) {
            $this->categoriasFiltradas = [];
            return;
        }

        $this->categoriasFiltradas = Categoria::where('area_informatica_id', $areaId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    
    public function abrirModalDictamen(int $id)
    {
        $reporte = Reporte::findOrFail($id);

        $this->dictamenReporteId = $id;
        $this->dictamenInventario = $reporte->numero_inventario ?? '';
        $this->dictamenEquipo = '';
        $this->dictamenMarca = '';
        $this->dictamenModelo = '';
        $this->dictamenSerie = '';
        $this->dictamenDiagnostico = '';
        $this->dictamenSugerencia = '';
        $this->dictamenObservaciones = '';
        $this->bienesSugerencias = [];

        if (!empty($this->dictamenInventario)) {
            $this->buscarBien($this->dictamenInventario);
        }

        $this->resetValidation();
        $this->showDictamenModal = true;
    }

    public function cerrarModalDictamen()
    {
        $this->showDictamenModal = false;
        $this->dictamenReporteId = null;
        $this->bienesSugerencias = [];
    }

    public function updatedDictamenInventario($value)
    {
        $this->buscarBien($value);
    }

    public function buscarBien($value)
    {
        $term = trim($value);
        if (strlen($term) < 2) {
            $this->bienesSugerencias = [];
            $this->resetValidation('dictamenInventario');
            return;
        }

        $bien = Bien::where('numero_inventario', $term)
            ->orWhere('numero_inventario_anterior', $term)
            ->first();

        if ($bien) {
            $this->dictamenEquipo = $bien->equipo ?? '';
            $this->dictamenMarca = $bien->marca ?? '';
            $this->dictamenModelo = $bien->modelo ?? '';
            $this->dictamenSerie = $bien->serie ?? '';
        }

        $this->bienesSugerencias = Bien::query()
            ->where('numero_inventario', 'like', "%{$term}%")
            ->orWhere('numero_inventario_anterior', 'like', "%{$term}%")
            ->orWhere('equipo', 'like', "%{$term}%")
            ->limit(5)
            ->get(['id', 'numero_inventario', 'equipo', 'marca', 'modelo', 'serie'])
            ->toArray();

        if (empty($this->bienesSugerencias)) {
            $this->addError('dictamenInventario', 'No se encontró ningún bien en el inventario con ese número.');
        } else {
            $this->resetValidation('dictamenInventario');
        }
    }

    public function seleccionarBien(int $bienId)
    {
        $bien = Bien::find($bienId);
        if ($bien) {
            $this->dictamenInventario = $bien->numero_inventario;
            $this->dictamenEquipo = $bien->equipo;
            $this->dictamenMarca = $bien->marca ?? '';
            $this->dictamenModelo = $bien->modelo ?? '';
            $this->dictamenSerie = $bien->serie ?? '';
        }
        $this->bienesSugerencias = [];
    }

    public function guardarDictamen()
    {
        $this->validate([
            'dictamenReporteId'  => 'required|exists:reportes,id',
            'dictamenInventario' => 'required|string|max:255',
            'dictamenEquipo'     => 'required|string|max:255',
            'dictamenMarca'      => 'required|string|max:255',
            'dictamenModelo'     => 'required|string|max:255',
            'dictamenSerie'      => 'required|string|max:255',
            'dictamenDiagnostico'=> 'required|string',
            'dictamenSugerencia' => 'required|string',
            'dictamenObservaciones' => 'nullable|string',
        ], [
            'dictamenInventario.required' => 'El número de inventario es obligatorio.',
            'dictamenEquipo.required'     => 'El nombre/tipo de equipo es obligatorio.',
            'dictamenMarca.required'      => 'La marca es obligatoria.',
            'dictamenModelo.required'     => 'El modelo es obligatorio.',
            'dictamenSerie.required'      => 'El número de serie es obligatorio.',
            'dictamenDiagnostico.required'=> 'El diagnóstico técnico es obligatorio.',
            'dictamenSugerencia.required' => 'La sugerencia es obligatoria.',
        ]);

        Dictamen::create([
            'reporte_id'   => $this->dictamenReporteId,
            'inventario'   => $this->dictamenInventario,
            'equipo'       => $this->dictamenEquipo,
            'marca'        => $this->dictamenMarca,
            'modelo'       => $this->dictamenModelo,
            'serie'        => $this->dictamenSerie,
            'diagnostico'  => $this->dictamenDiagnostico,
            'sugerencia'   => $this->dictamenSugerencia,
            'observaciones'=> $this->dictamenObservaciones,
        ]);

        $reporte = Reporte::findOrFail($this->dictamenReporteId);
        if ($reporte->estado_id == 1) {
            $reporte->estado_id = 2; // Atendido
            $reporte->save();
        }

        $this->dispatch('refrescarComentarios', id: $reporte->id);

        $this->cerrarModalDictamen();
        session()->flash('ok', 'Dictamen técnico registrado exitosamente.');
    }

    public function render()
    {
        $departamentos     = DepartamentoCongreso::orderBy('name')->get();
        $areasInformatica  = AreasInformatica::orderBy('name')->get();
        $tecnicos          = User::orderBy('name')->get();
        $eventos           = Evento::orderBy('date', 'desc')->activos()->get();
        $todasCategorias   = Categoria::select('id', 'name')->orderBy('name')->get();

        $user  = auth()->user();
        $uid   = $user->id;

        // Base: solo reportes abiertos, con relaciones
        $baseQuery = Reporte::with(['categoria', 'tecnico', 'estado', 'comentarios.user'])
            ->withCount('dictamenes')
            ->abiertos()   // tu scope: no cerrados/ni cancelados
            ->latest();

        if ($user->hasRole('Mesa-control')) {
            // 1) Mesa-Control: ve TODOS los abiertos
            $reportes = $baseQuery->paginate(5);
        } elseif ($user->hasRole('Tecnico')) {
            // 2) Tecnico: ve SOLO los abiertos asignados a él
            //    - como técnico principal (tecnico_user_id)
            //    - o asignado en la pivote (relación tecnicos)
            $reportes = $baseQuery
                ->where(function ($q) use ($uid) {
                    $q->where('tecnico_user_id', $uid)
                        ->orWhereHas('tecnicos', fn($t) => $t->where('users.id', $uid));
                })
                ->paginate(5);
        } else {
            // (Opcional) Sin rol reconocido: no mostrar nada
            $reportes = $baseQuery->whereRaw('1=0')->paginate(5);
        }

        // Totales (si quieres que también respeten el rol, aplica el mismo filtro que arriba)
        $this->totalPendientes = Reporte::whereHas('estado', fn($q) => $q->where('name', 'Pendiente'))->count();
        $this->totalAtendidos  = Reporte::whereHas('estado', fn($q) => $q->where('name', 'Atendido'))->count();

        return view('livewire.reportes', compact(
            'reportes',
            'departamentos',
            'areasInformatica',
            'tecnicos',
            'eventos',
            'todasCategorias'
        ));
    }


    public function messages()
    {
        return [
            'nuevoReporte.departamento_id.required'      => 'El área del Congreso es obligatoria.',
            'nuevoReporte.departamento_id.exists'        => 'El área seleccionada no es válida.',

            'nuevoReporte.solicitante.required'          => 'El campo solicitante es obligatorio.',
            'nuevoReporte.solicitante.max'               => 'El solicitante no puede tener más de 255 caracteres.',

            'nuevoReporte.descripcion.required'          => 'La descripción es obligatoria.',
            'nuevoReporte.descripcion.min'               => 'La descripción debe tener al menos 3 caracteres.',

            'nuevoReporte.area_informatica_id.required'  => 'El área de informática es obligatoria.',
            'nuevoReporte.area_informatica_id.exists'    => 'El área de informática seleccionada no es válida.',

            'nuevoReporte.categoria_id.required'         => 'La categoría es obligatoria.',
            'nuevoReporte.categoria_id.exists'           => 'La categoría seleccionada no es válida.',

            'nuevoReporte.tecnico_id.required'           => 'El técnico es obligatorio.',
            'nuevoReporte.tecnico_id.exists'             => 'El técnico seleccionado no es válido.',

            'nuevoReporte.numero_copias.integer'         => 'El número de copias debe ser un número entero.',
            'nuevoReporte.numero_copias.min'             => 'El número de copias debe ser al menos 1.',
        ];
    }
}
