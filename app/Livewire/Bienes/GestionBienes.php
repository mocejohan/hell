<?php

namespace App\Livewire\Bienes;

use App\Models\Bien;
use App\Exports\BienesExport;
use App\Exports\PlantillaBienesExport;
use App\Imports\BienesImport;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;

class GestionBienes extends Component
{
    use WithPagination;
    use WithFileUploads;

    // Filtros y Búsqueda
    public string $search = '';
    public string $equipoFiltro = '';
    public string $sortField = 'id';
    public string $sortDirection = 'desc';
    public int $perPage = 15;

    // Modales
    public bool $showModalForm = false;
    public bool $showModalDelete = false;
    public bool $showModalHistorial = false;
    public bool $showModalImport = false;

    // Formulario de Bien (Crear / Editar)
    public ?int $bienId = null;
    public string $numero_inventario = '';
    public ?string $numero_inventario_anterior = '';
    public string $equipo = '';
    public ?string $marca = '';
    public ?string $modelo = '';
    public ?string $serie = '';
    public ?string $ubicacion = '';
    public ?string $resguardatario = '';

    // Importación Masiva
    public $archivoExcel;
    public string $modoImportacion = 'upsert';
    public ?array $importStats = null;

    // Historial y Eliminación
    public ?Bien $bienSeleccionado = null;
    public $historialDictamenes = [];
    public ?Bien $bienParaEliminar = null;

    protected function rules()
    {
        return [
            'numero_inventario' => [
                'required',
                'string',
                'max:50',
                Rule::unique('bienes', 'numero_inventario')->ignore($this->bienId),
            ],
            'numero_inventario_anterior' => 'nullable|string|max:50',
            'equipo' => 'required|string|max:255',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'serie' => 'nullable|string|max:255',
            'ubicacion' => 'nullable|string|max:255',
            'resguardatario' => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'numero_inventario.required' => 'El número de inventario es obligatorio.',
        'numero_inventario.unique' => 'Este número de inventario ya está registrado.',
        'equipo.required' => 'El tipo o nombre de equipo es obligatorio.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEquipoFiltro()
    {
        $this->resetPage();
    }

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function abrirModalCrear()
    {
        $this->resetValidation();
        $this->resetForm();
        $this->showModalForm = true;
    }

    public function abrirModalEditar(int $id)
    {
        $this->resetValidation();
        $bien = Bien::findOrFail($id);
        $this->bienId = $bien->id;
        $this->numero_inventario = $bien->numero_inventario;
        $this->numero_inventario_anterior = $bien->numero_inventario_anterior;
        $this->equipo = $bien->equipo;
        $this->marca = $bien->marca;
        $this->modelo = $bien->modelo;
        $this->serie = $bien->serie;
        $this->ubicacion = $bien->ubicacion;
        $this->resguardatario = $bien->resguardatario;
        $this->showModalForm = true;
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'numero_inventario'          => trim($this->numero_inventario),
            'numero_inventario_anterior' => $this->numero_inventario_anterior ? trim($this->numero_inventario_anterior) : null,
            'equipo'                     => trim($this->equipo),
            'marca'                      => $this->marca ? trim($this->marca) : null,
            'modelo'                     => $this->modelo ? trim($this->modelo) : null,
            'serie'                      => $this->serie ? trim($this->serie) : null,
            'ubicacion'                  => $this->ubicacion ? trim($this->ubicacion) : null,
            'resguardatario'             => $this->resguardatario ? trim($this->resguardatario) : null,
        ];

        if ($this->bienId) {
            $bien = Bien::findOrFail($this->bienId);
            $bien->update($data);
            session()->flash('success', 'Bien actualizado correctamente.');
        } else {
            Bien::create($data);
            session()->flash('success', 'Bien registrado exitosamente.');
        }

        $this->showModalForm = false;
        $this->resetForm();
    }

    public function confirmarEliminar(int $id)
    {
        $this->bienParaEliminar = Bien::withCount('dictamenes')->findOrFail($id);
        $this->showModalDelete = true;
    }

    public function eliminar()
    {
        if ($this->bienParaEliminar) {
            // Si tiene dictámenes asociados, proteger la integridad
            if ($this->bienParaEliminar->dictamenes()->count() > 0) {
                session()->flash('error', 'No se puede eliminar este bien porque cuenta con dictámenes asociados.');
                $this->showModalDelete = false;
                return;
            }

            $this->bienParaEliminar->delete();
            session()->flash('success', 'Bien eliminado satisfactoriamente.');
        }

        $this->showModalDelete = false;
        $this->bienParaEliminar = null;
    }

    public function abrirModalHistorial(int $id)
    {
        $this->bienSeleccionado = Bien::with(['dictamenes.reporte.estado', 'dictamenes.reporte.departamento', 'dictamenes.reporte.tecnico'])->findOrFail($id);
        $this->historialDictamenes = $this->bienSeleccionado->dictamenes()->with(['reporte.estado', 'reporte.departamento', 'reporte.tecnico'])->orderByDesc('created_at')->get();
        $this->showModalHistorial = true;
    }

    public function abrirModalImportar()
    {
        $this->reset(['archivoExcel', 'importStats']);
        $this->resetValidation();
        $this->showModalImport = true;
    }

    public function procesarImportacion()
    {
        $this->validate([
            'archivoExcel' => 'required|file|mimes:xlsx,xls,csv,txt|max:20480', // Máximo 20MB
            'modoImportacion' => 'required|in:upsert,solo_nuevos',
        ], [
            'archivoExcel.required' => 'Debes seleccionar un archivo Excel o CSV.',
            'archivoExcel.mimes' => 'El archivo debe ser de formato .xlsx, .xls o .csv.',
            'archivoExcel.max' => 'El archivo no debe exceder los 20MB.',
        ]);

        try {
            $import = new BienesImport($this->modoImportacion);
            Excel::import($import, $this->archivoExcel->getRealPath());

            $this->importStats = [
                'creados' => $import->creados,
                'actualizados' => $import->actualizados,
                'omitidos' => $import->omitidos,
                'errores' => $import->errores,
            ];

            $this->reset('archivoExcel');
            session()->flash('success', "Importación completada: {$import->creados} creados, {$import->actualizados} actualizados.");
        } catch (\Exception $e) {
            session()->flash('error', 'Ocurrió un error al procesar el archivo: ' . $e->getMessage());
        }
    }

    public function descargarPlantilla()
    {
        return Excel::download(new PlantillaBienesExport, 'plantilla_bienes.xlsx');
    }

    public function exportar()
    {
        return Excel::download(new BienesExport($this->search, $this->equipoFiltro), 'inventario_bienes_' . date('Y-m-d_His') . '.xlsx');
    }

    public function resetForm()
    {
        $this->bienId = null;
        $this->numero_inventario = '';
        $this->numero_inventario_anterior = '';
        $this->equipo = '';
        $this->marca = '';
        $this->modelo = '';
        $this->serie = '';
        $this->ubicacion = '';
        $this->resguardatario = '';
    }

    public function render()
    {
        $tiposEquipo = Bien::query()
            ->select('equipo')
            ->whereNotNull('equipo')
            ->where('equipo', '!=', '')
            ->distinct()
            ->orderBy('equipo')
            ->pluck('equipo');

        $bienes = Bien::query()
            ->withCount('dictamenes')
            ->when($this->search, function ($query, $term) {
                $query->where(function ($q) use ($term) {
                    $q->where('numero_inventario', 'like', "%{$term}%")
                      ->orWhere('numero_inventario_anterior', 'like', "%{$term}%")
                      ->orWhere('equipo', 'like', "%{$term}%")
                      ->orWhere('marca', 'like', "%{$term}%")
                      ->orWhere('modelo', 'like', "%{$term}%")
                      ->orWhere('serie', 'like', "%{$term}%")
                      ->orWhere('ubicacion', 'like', "%{$term}%")
                      ->orWhere('resguardatario', 'like', "%{$term}%");
                });
            })
            ->when($this->equipoFiltro, fn($q, $v) => $q->where('equipo', $v))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $totalBienes = Bien::count();

        return view('livewire.bienes.gestion-bienes', [
            'bienes' => $bienes,
            'tiposEquipo' => $tiposEquipo,
            'totalBienes' => $totalBienes,
        ]);
    }
}
