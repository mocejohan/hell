<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Reporte;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportesAtendidosExport;
use Carbon\Carbon;
use App\Models\User;

class ReportesAtendidos extends Component
{
    use WithPagination;

    public ?string $fechainicial = null;
    public ?string $fechafinal   = null;
    public bool $aplicar = false;
    public ?int $tecnicoId = null;

    protected $queryString = [
        'fechainicial' => ['except' => ''],
        'fechafinal'   => ['except' => ''],
        'tecnicoId'    => ['except' => ''],
        'aplicar'      => ['except' => false],
        'page'         => ['except' => 1],
    ];

    public function rules()
    {
        return [
            'fechainicial' => 'nullable|date',
            'fechafinal'   => 'nullable|date|after_or_equal:fechainicial',
            'tecnicoId'    => 'nullable|exists:users,id',
        ];
    }

    public function updatingFechainicial()
    {
        $this->resetPage();
    }
    public function updatingFechafinal()
    {
        $this->resetPage();
    }

    public function aceptar()
    {
        $this->validate();
        $this->aplicar = true;
        $this->resetPage();
    }

    public function updatingTecnicoId()
    {
        $this->aplicar = true; 
        $this->resetPage();
    }

    protected function baseQuery()
    {
        return Reporte::with([
            'estado',
            'categoria',
            'tecnico',
            'departamento',
            'area',
            'tecnicos'
        ])
            ->whereHas('estado', fn($q) => $q->where('name', 'Cerrado'))
            ->withCount('dictamenes')
            ->when($this->fechainicial, fn($q) => $q->whereDate('created_at', '>=', $this->fechainicial))
            ->when($this->fechafinal,   fn($q) => $q->whereDate('created_at', '<=', $this->fechafinal))
            ->when($this->tecnicoId, function ($q, $id) {
                $q->where(function ($qq) use ($id) {
                    $qq->where('tecnico_user_id', $id)
                        ->orWhereHas('tecnicos', fn($t) => $t->where('users.id', $id));
                });
            })
            ->orderByDesc('created_at');
    }


    public function exportarExcel()
    {
        // Validar antes de exportar
        $this->validate();

        // nombre de archivo
        $sufijoTec = $this->tecnicoId ? ('_tec' . $this->tecnicoId) : '';
        $file = 'reportes_atendidos_' . $this->fechainicial . '_' . $this->fechafinal . $sufijoTec . '.xlsx';

        return Excel::download(
            new ReportesAtendidosExport($this->fechainicial, $this->fechafinal, $this->tecnicoId), // <-- pasa técnico
            $file
        );
    }

    public function mount()
    {
        // Formato que acepta <input type="date">
        $hoy = now()->format('Y-m-d');

        // Si no vienen en la URL ni fueron seteadas, usa hoy
        if (empty($this->fechainicial)) {
            $this->fechainicial = $hoy;
        }

        if (empty($this->fechafinal)) {
            $this->fechafinal = $hoy;
        }
    }


    public function render()
    {
        $reportes = $this->aplicar
            ? $this->baseQuery()->paginate(6) // 10 por página en grid
            : collect();

        $tecnicos = User::orderBy('name')->get(['id', 'name']);
        return view('livewire.reportes-atendidos', compact('reportes', 'tecnicos'));
    }
}
