<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Reporte, Comentario, DepartamentoCongreso, AreasInformatica, Categoria, User, Evento, Bien, Dictamen, DictamenVersion};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ReporteEstadoNotificacion;
use Spatie\Permission\Models\Role;

use Livewire\Attributes\On;
use App\Services\DictamenPdfService;
use App\Services\BienLookupService;

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
    public bool $isEditingDictamen = false;
    public ?int $dictamenIdEnEdicion = null;
    public string $dictamenInventario = '';
    public string $dictamenEquipo = '';
    public string $dictamenMarca = '';
    public string $dictamenModelo = '';
    public string $dictamenSerie = '';
    public string $dictamenResguardatario = '';
    public string $dictamenDiagnostico = '';
    public string $dictamenSugerencia = '';
    public string $dictamenObservaciones = '';
    public string $dictamenMotivoCambio = '';
    public array $bienesSugerencias = [];
    public ?int $selectedBienId = null;
    public ?string $bienDictamenWarning = null;
    public array $bienDictamenesHistorial = [];

    // Modal Historial de Versiones Dictamen
    public bool $showHistorialDictamenModal = false;
    public $historialDictamenReporte = null;
    public $historialVersiones = [];


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

    public function mount()
    {
        $this->categoriasFiltradas = Categoria::all();
    }

    public function updatedNuevoReporteAreaInformaticaId($areaId)
    {
        if ($areaId) {
            $this->categoriasFiltradas = Categoria::where('area_informatica_id', $areaId)->get();
        } else {
            $this->categoriasFiltradas = Categoria::all();
        }

        $this->nuevoReporte['categoria_id'] = '';
    }

    public function abrirModalAtendido($reporteId, $categoriaId)
    {
        $this->atendidoReporteId = $reporteId;
        $this->atendidoCategoriaId = $categoriaId;

        $reporte = Reporte::with('tecnicos')->findOrFail($reporteId);
        $this->atendidoTecnicoId = $reporte->tecnico_user_id;
        $this->atendidoTecnicoIds = $reporte->tecnicos->pluck('id')->toArray();

        $this->showAtendidoModal = true;
    }

    public function cerrarModalAtendido()
    {
        $this->showAtendidoModal = false;
        $this->atendidoReporteId = null;
        $this->atendidoCategoriaId = null;
        $this->atendidoTecnicoId = null;
        $this->atendidoTecnicoIds = [];
    }

    public function guardarAtendido()
    {
        $this->validate([
            'atendidoCategoriaId' => 'required|exists:categorias,id',
            'atendidoTecnicoId'   => 'required|exists:users,id',
            'atendidoTecnicoIds'  => 'array',
            'atendidoTecnicoIds.*' => 'exists:users,id',
        ], [
            'atendidoCategoriaId.required' => 'La categoría es obligatoria.',
            'atendidoTecnicoId.required'   => 'El técnico es obligatorio.',
        ]);

        $reporte = Reporte::findOrFail($this->atendidoReporteId);

        $categoriaOriginal = $reporte->categoria_id;
        $categoriaNueva    = (int) $this->atendidoCategoriaId;

        $reporte->categoria_id = $categoriaNueva;
        $reporte->tecnico_user_id = $this->atendidoTecnicoId;

        $idsSincronizar = array_unique(array_filter(array_merge(
            [$this->atendidoTecnicoId],
            $this->atendidoTecnicoIds
        )));

        $reporte->tecnicos()->sync($idsSincronizar);

        if ($reporte->estado_id == 1) {
            $reporte->estado_id = 2; // Atendido
        }

        $reporte->save();

        if ($categoriaOriginal !== $categoriaNueva) {
            $catOldName = Categoria::find($categoriaOriginal)?->name ?? 'Sin categoría';
            $catNewName = Categoria::find($categoriaNueva)?->name ?? 'Sin categoría';

            Comentario::create([
                'reporte_id' => $reporte->id,
                'user_id'    => auth()->id(),
                'comentario' => "Cambio de categoría: \"{$catOldName}\" ➜ \"{$catNewName}\"",
            ]);
        }

        $this->cerrarModalAtendido();
        $this->dispatch('refrescarComentarios', id: $reporte->id);
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

    public function confirmarCerrar()
    {
        $reporte = Reporte::findOrFail($this->cerrarReporteId);

        $reporte->estado_id = 3; // Cerrado
        $reporte->closed_at = now();
        $reporte->save();

        Comentario::create([
            'reporte_id' => $reporte->id,
            'user_id'    => auth()->id(),
            'comentario' => 'El reporte fue cerrado por la Mesa de Control.',
        ]);

        $this->cerrarModalCerrar();
        $this->dispatch('refrescarComentarios', id: $reporte->id);
    }

    public function abrirModalCancelar(int $id)
    {
        $this->cancelarReporteId = $id;
        $this->cancelarComentario = '';
        $this->showCancelarModal = true;
    }

    public function cerrarModalCancelar()
    {
        $this->showCancelarModal = false;
        $this->cancelarReporteId = null;
        $this->cancelarComentario = '';
    }

    public function confirmarCancelar()
    {
        $this->validate([
            'cancelarComentario' => 'required|string|min:3',
        ], [
            'cancelarComentario.required' => 'El motivo de cancelación es obligatorio.',
            'cancelarComentario.min' => 'El motivo debe tener al menos 3 caracteres.',
        ]);

        $reporte = Reporte::findOrFail($this->cancelarReporteId);

        $reporte->estado_id = 4; // Cancelado
        $reporte->save();

        Comentario::create([
            'reporte_id' => $reporte->id,
            'user_id'    => auth()->id(),
            'comentario' => 'El reporte fue cancelado: ' . $this->cancelarComentario,
        ]);

        $this->cerrarModalCancelar();
        $this->dispatch('refrescarComentarios', id: $reporte->id);
    }

    public function abrirModalComentario(int $id)
    {
        $this->comentarioReporteId = $id;
        $this->comentarioTexto = '';
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
            'comentarioTexto' => 'required|string|min:2',
        ], [
            'comentarioTexto.required' => 'El comentario no puede estar vacío.',
            'comentarioTexto.min'      => 'El comentario debe tener al menos 2 caracteres.',
        ]);

        Comentario::create([
            'reporte_id' => $this->comentarioReporteId,
            'user_id'    => auth()->id(),
            'comentario' => $this->comentarioTexto,
        ]);

        $this->dispatch('toast', type: 'success', msg: 'Comentario agregado');

        $reporteId = $this->comentarioReporteId;

        $this->cerrarModalComentario();

        $this->dispatch('refrescarComentarios', id: $reporteId);
    }

    public function guardar()
    {
        $this->validate();

        $numeroInventario = !empty($this->nuevoReporte['numero_inventario'])
            ? trim($this->nuevoReporte['numero_inventario'])
            : null;

        $reporte = Reporte::create([
            'departamento_congreso_id' => $this->nuevoReporte['departamento_id'],
            'solicitante'              => $this->nuevoReporte['solicitante'],
            'descripcion'              => $this->nuevoReporte['descripcion'],
            'area_informatica_id'      => $this->nuevoReporte['area_informatica_id'],
            'categoria_id'             => $this->nuevoReporte['categoria_id'],
            'tecnico_user_id'          => $this->nuevoReporte['tecnico_id'],
            'capturo_user_id'          => auth()->id(),
            'numero_copias'            => $this->nuevoReporte['numero_copias'] ?: null,
            'numero_inventario'        => $numeroInventario,
            'evento_id'                => $this->nuevoReporte['evento_id'] ?: null,
            'estado_id'                => 1, // Pendiente
        ]);

        $reporte->tecnicos()->sync([$this->nuevoReporte['tecnico_id']]);

        // Notificar a usuarios de Mesa-control
        $mesaControlUsers = User::role('Mesa-control')->get();
        if ($mesaControlUsers->isNotEmpty()) {
            Notification::send($mesaControlUsers, new ReporteEstadoNotificacion(
                reporte: $reporte,
                tipo: 'creado',
                mensaje: "Nuevo reporte #{$reporte->id} registrado por " . auth()->user()->name
            ));
        }

        // Notificar al técnico asignado (si no es el mismo que capturó)
        $tecnico = User::find($this->nuevoReporte['tecnico_id']);
        if ($tecnico && $tecnico->id !== auth()->id()) {
            $tecnico->notify(new ReporteEstadoNotificacion(
                reporte: $reporte,
                tipo: 'asignado',
                mensaje: "Se te ha asignado el reporte #{$reporte->id}: {$reporte->solicitante}"
            ));
        }

        $this->reset(['nuevoReporte', 'showCreateModal']);
        session()->flash('ok', 'Reporte creado exitosamente.');
    }

    public function updatedNuevoReporteNumeroInventario($value)
    {
        $val = trim($value);
        if (strlen($val) >= 2) {
            $bien = BienLookupService::buscarPorInventario($val);
            if ($bien && !empty($bien->ubicacion)) {
                $depto = DepartamentoCongreso::where('name', 'like', '%' . $bien->ubicacion . '%')->first();
                if ($depto) {
                    $this->nuevoReporte['departamento_id'] = $depto->id;
                }
            }
        }
    }

    #[On('echo:reportes,ReporteCreado')]
    public function onReporteCreado($payload = null)
    {
        $this->render();
    }

    #[On('echo:reportes,ReporteActualizado')]
    public function onReporteActualizado($payload = null)
    {
        $this->render();
    }

    #[On('echo:reportes,ComentarioCreado')]
    public function onComentarioCreado($payload = null)
    {
        $this->render();
    }

    public function getTecnicosDisponiblesProperty()
    {
        return User::role('Tecnico')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function abrirModalDictamen(int $id)
    {
        $reporte = Reporte::with('dictamen')->findOrFail($id);

        // Si el reporte ya fue cerrado o cancelado por la Mesa de Control, no se puede modificar
        if (in_array($reporte->estado_id, [3, 4]) || !empty($reporte->closed_at)) {
            $this->dispatch('toast', type: 'warning', msg: 'El dictamen no puede modificarse porque el reporte ya fue cerrado por la Mesa de Control.');
            return;
        }

        $this->dictamenReporteId = $id;
        $dictamen = $reporte->dictamen;

        if ($dictamen) {
            // Modo Edición / Modificación
            $this->isEditingDictamen = true;
            $this->dictamenIdEnEdicion = $dictamen->id;
            $this->dictamenInventario = $dictamen->inventario;
            $this->dictamenEquipo = $dictamen->equipo;
            $this->dictamenMarca = $dictamen->marca;
            $this->dictamenModelo = $dictamen->modelo;
            $this->dictamenSerie = $dictamen->serie;
            $this->dictamenResguardatario = $dictamen->resguardatario ?? '';
            $this->dictamenDiagnostico = $dictamen->diagnostico;
            $this->dictamenSugerencia = $dictamen->sugerencia;
            $this->dictamenObservaciones = $dictamen->observaciones ?? '';
            $this->dictamenMotivoCambio = '';
            $this->selectedBienId = $dictamen->bien_id;
        } else {
            // Modo Nuevo Dictamen
            $this->isEditingDictamen = false;
            $this->dictamenIdEnEdicion = null;
            $this->dictamenInventario = $reporte->numero_inventario ?? '';
            $this->dictamenEquipo = '';
            $this->dictamenMarca = '';
            $this->dictamenModelo = '';
            $this->dictamenSerie = '';
            $this->dictamenResguardatario = '';
            $this->dictamenDiagnostico = '';
            $this->dictamenSugerencia = '';
            $this->dictamenObservaciones = '';
            $this->dictamenMotivoCambio = '';

            if (!empty($this->dictamenInventario)) {
                $this->buscarBien($this->dictamenInventario);
            }
        }

        $this->bienesSugerencias = [];
        $this->resetValidation();
        $this->showDictamenModal = true;
    }

    public function cerrarModalDictamen()
    {
        $this->showDictamenModal = false;
        $this->dictamenReporteId = null;
        $this->dictamenIdEnEdicion = null;
        $this->isEditingDictamen = false;
        $this->dictamenMotivoCambio = '';
        $this->dictamenResguardatario = '';
        $this->bienesSugerencias = [];
        $this->selectedBienId = null;
        $this->bienDictamenWarning = null;
        $this->bienDictamenesHistorial = [];
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
            $this->selectedBienId = null;
            $this->bienDictamenWarning = null;
            $this->bienDictamenesHistorial = [];
            $this->resetValidation('dictamenInventario');
            return;
        }

        // Búsqueda híbrida: Local primero, luego Aries si no existe
        $bien = BienLookupService::buscarPorInventario($term);

        if ($bien) {
            $this->dictamenEquipo = $bien->equipo ?? '';
            $this->dictamenMarca = $bien->marca ?? '';
            $this->dictamenModelo = $bien->modelo ?? '';
            $this->dictamenSerie = $bien->serie ?? '';
            $this->dictamenResguardatario = $bien->resguardatario ?? '';
            $this->selectedBienId = $bien->id;

            // Verificar dictámenes existentes para este bien
            $this->verificarDictamenesDelBien($bien);
        } else {
            $this->selectedBienId = null;
            $this->bienDictamenWarning = null;
            $this->bienDictamenesHistorial = [];
        }

        $this->bienesSugerencias = Bien::query()
            ->where('numero_inventario', 'like', "%{$term}%")
            ->orWhere('numero_inventario_anterior', 'like', "%{$term}%")
            ->orWhere('equipo', 'like', "%{$term}%")
            ->limit(5)
            ->get(['id', 'numero_inventario', 'equipo', 'marca', 'modelo', 'serie'])
            ->toArray();

        // Marcar cuáles bienes de las sugerencias ya tienen dictamen activo
        foreach ($this->bienesSugerencias as &$sug) {
            $bienSug = Bien::find($sug['id']);
            $dictActivo = $bienSug ? $bienSug->tieneDictamenActivo($this->dictamenReporteId) : null;
            $sug['tiene_dictamen_activo'] = $dictActivo ? true : false;
            $sug['dictamen_reporte_id'] = $dictActivo ? $dictActivo->reporte_id : null;
        }
        unset($sug);

        if (empty($this->bienesSugerencias)) {
            $this->addError('dictamenInventario', 'No se encontró el bien en el inventario local ni en Aries.');
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
            $this->dictamenResguardatario = $bien->resguardatario ?? '';
            $this->selectedBienId = $bien->id;

            // Verificar dictámenes existentes para este bien
            $this->verificarDictamenesDelBien($bien);
        }
        $this->bienesSugerencias = [];
    }

    /**
     * Verifica si un bien ya tiene dictámenes y genera advertencia + historial.
     */
    private function verificarDictamenesDelBien(Bien $bien): void
    {
        $this->bienDictamenWarning = null;
        $this->bienDictamenesHistorial = [];

        // Buscar dictamen activo (excluyendo el reporte actual si estamos editando)
        $dictamenActivo = $bien->tieneDictamenActivo($this->dictamenReporteId);

        if ($dictamenActivo) {
            $reporteNum = $dictamenActivo->reporte_id;
            $fecha = $dictamenActivo->created_at->format('d/m/Y H:i');
            $this->bienDictamenWarning = "Este bien ya tiene un dictamen activo registrado en el Reporte #{$reporteNum} (generado el {$fecha}). Puedes continuar, pero revisa si es necesario generar otro dictamen para el mismo equipo.";
        }

        // Cargar historial completo de dictámenes del bien
        $historial = $bien->historialDictamenes()->get();
        if ($historial->isNotEmpty()) {
            $this->bienDictamenesHistorial = $historial->map(function ($d) {
                return [
                    'id' => $d->id,
                    'reporte_id' => $d->reporte_id,
                    'inventario' => $d->inventario,
                    'equipo' => $d->equipo,
                    'diagnostico' => \Illuminate\Support\Str::limit($d->diagnostico, 80),
                    'fecha' => $d->created_at->format('d/m/Y H:i'),
                    'estado_reporte' => $d->reporte?->estado?->name ?? 'Desconocido',
                    'es_activo' => $d->reporte && !in_array($d->reporte->estado_id, [3, 4]),
                ];
            })->toArray();
        }
    }

    public function guardarDictamen()
    {
        $this->validate([
            'dictamenReporteId'      => 'required|exists:reportes,id',
            'dictamenInventario'     => 'required|string|max:255',
            'dictamenEquipo'         => 'required|string|max:255',
            'dictamenMarca'          => 'required|string|max:255',
            'dictamenModelo'         => 'required|string|max:255',
            'dictamenSerie'          => 'required|string|max:255',
            'dictamenResguardatario' => 'nullable|string|max:255',
            'dictamenDiagnostico'    => 'required|string',
            'dictamenSugerencia'     => 'required|string',
            'dictamenObservaciones'  => 'nullable|string',
            'dictamenMotivoCambio'   => 'nullable|string|max:255',
        ], [
            'dictamenInventario.required' => 'El número de inventario es obligatorio.',
            'dictamenEquipo.required'     => 'El nombre/tipo de equipo es obligatorio.',
            'dictamenMarca.required'      => 'La marca es obligatoria.',
            'dictamenModelo.required'     => 'El modelo es obligatorio.',
            'dictamenSerie.required'      => 'El número de serie es obligatorio.',
            'dictamenDiagnostico.required'=> 'El diagnóstico técnico es obligatorio.',
            'dictamenSugerencia.required' => 'La sugerencia es obligatoria.',
        ]);

        $reporte = Reporte::findOrFail($this->dictamenReporteId);

        // Bloqueo de seguridad: Si la Mesa de Control ya cerró el reporte
        if (in_array($reporte->estado_id, [3, 4]) || !empty($reporte->closed_at)) {
            $this->cerrarModalDictamen();
            $this->dispatch('toast', type: 'error', msg: 'No se puede modificar el dictamen: el reporte fue cerrado por la Mesa de Control.');
            return;
        }

        if ($this->isEditingDictamen && $this->dictamenIdEnEdicion) {
            $dictamen = Dictamen::findOrFail($this->dictamenIdEnEdicion);

            $maxVersion = $dictamen->versiones()->max('version') ?? 0;

            // Si es la primera edición y no existía versión inicial respaldada, la respaldamos
            if ($maxVersion === 0) {
                $dictamen->versiones()->create([
                    'user_id'        => auth()->id(),
                    'version'        => 1,
                    'inventario'     => $dictamen->inventario,
                    'equipo'         => $dictamen->equipo,
                    'marca'          => $dictamen->marca,
                    'modelo'         => $dictamen->modelo,
                    'serie'          => $dictamen->serie,
                    'resguardatario' => $dictamen->resguardatario,
                    'diagnostico'    => $dictamen->diagnostico,
                    'sugerencia'     => $dictamen->sugerencia,
                    'observaciones'  => $dictamen->observaciones,
                    'motivo_cambio'  => 'Versión inicial original',
                    'created_at'     => $dictamen->created_at,
                ]);
                $maxVersion = 1;
            }

            $nuevaVersion = $maxVersion + 1;

            // Guardar la nueva versión en el historial
            $dictamen->versiones()->create([
                'user_id'        => auth()->id(),
                'version'        => $nuevaVersion,
                'inventario'     => $this->dictamenInventario,
                'equipo'         => $this->dictamenEquipo,
                'marca'          => $this->dictamenMarca,
                'modelo'         => $this->dictamenModelo,
                'serie'          => $this->dictamenSerie,
                'resguardatario' => $this->dictamenResguardatario,
                'diagnostico'    => $this->dictamenDiagnostico,
                'sugerencia'     => $this->dictamenSugerencia,
                'observaciones'  => $this->dictamenObservaciones,
                'motivo_cambio'  => $this->dictamenMotivoCambio ?: "Modificación técnica (Versión {$nuevaVersion})",
            ]);

            // Actualizar el dictamen actual
            $dictamen->update([
                'bien_id'        => $this->selectedBienId,
                'inventario'     => $this->dictamenInventario,
                'equipo'         => $this->dictamenEquipo,
                'marca'          => $this->dictamenMarca,
                'modelo'         => $this->dictamenModelo,
                'serie'          => $this->dictamenSerie,
                'resguardatario' => $this->dictamenResguardatario,
                'diagnostico'    => $this->dictamenDiagnostico,
                'sugerencia'     => $this->dictamenSugerencia,
                'observaciones'  => $this->dictamenObservaciones,
            ]);

            session()->flash('ok', "Dictamen técnico actualizado exitosamente (Versión {$nuevaVersion}).");
        } else {
            // Creación inicial
            $dictamen = Dictamen::create([
                'reporte_id'     => $this->dictamenReporteId,
                'bien_id'        => $this->selectedBienId,
                'inventario'     => $this->dictamenInventario,
                'equipo'         => $this->dictamenEquipo,
                'marca'          => $this->dictamenMarca,
                'modelo'         => $this->dictamenModelo,
                'serie'          => $this->dictamenSerie,
                'resguardatario' => $this->dictamenResguardatario,
                'diagnostico'    => $this->dictamenDiagnostico,
                'sugerencia'     => $this->dictamenSugerencia,
                'observaciones'  => $this->dictamenObservaciones,
            ]);

            // Registrar Versión 1 en el historial
            $dictamen->versiones()->create([
                'user_id'        => auth()->id(),
                'version'        => 1,
                'inventario'     => $this->dictamenInventario,
                'equipo'         => $this->dictamenEquipo,
                'marca'          => $this->dictamenMarca,
                'modelo'         => $this->dictamenModelo,
                'serie'          => $this->dictamenSerie,
                'resguardatario' => $this->dictamenResguardatario,
                'diagnostico'    => $this->dictamenDiagnostico,
                'sugerencia'     => $this->dictamenSugerencia,
                'observaciones'  => $this->dictamenObservaciones,
                'motivo_cambio'  => 'Creación y registro inicial',
            ]);

            if ($reporte->estado_id == 1) {
                $reporte->estado_id = 2; // Atendido
                $reporte->save();
            }

            session()->flash('ok', 'Dictamen técnico registrado exitosamente (Versión 1).');
        }

        // Guardar físicamente el PDF actualizado en disco (storage/app/public/dictamenes/)
        DictamenPdfService::guardarEnDisco($reporte, $dictamen);

        $this->dispatch('refrescarComentarios', id: $reporte->id);
        $this->cerrarModalDictamen();
    }

    public function abrirHistorialDictamen(int $id)
    {
        $reporte = Reporte::with(['dictamen.versiones.user'])->findOrFail($id);
        $this->historialDictamenReporte = $reporte;
        $this->historialVersiones = $reporte->dictamen?->versiones ?? collect();
        $this->showHistorialDictamenModal = true;
    }

    public function cerrarHistorialDictamen()
    {
        $this->showHistorialDictamenModal = false;
        $this->historialDictamenReporte = null;
        $this->historialVersiones = [];
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
