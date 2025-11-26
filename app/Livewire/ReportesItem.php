<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Reporte;

class ReportesItem extends Component
{
    public Reporte $reporte;

    public bool $mostrarFooter = true;
    public bool $mostrarEstado = true;

    public function atender()
    {
        $this->dispatch('abrirModalAtendido', id: $this->reporte->id);
    }

    public function cerrar()
    {
        // Abrir modal de confirmación en el padre
        $this->dispatch('abrirModalCerrar', id: $this->reporte->id);
    }

    public function cancelar()
    {
        $this->dispatch('abrirModalCancelar', id: $this->reporte->id);
    }

    public function comentar()
    {
        $this->dispatch('abrirModalComentario', id: $this->reporte->id);
    }

    public function dictamen(){
        
    }

    protected $listeners = ['refrescarComentarios'];

    public function refrescarComentarios(int $id)
    {
        if ($id === $this->reporte->id) {
            $this->reporte->refresh()->load(['categoria', 'estado','tecnico','tecnicos','comentarios.user']);
        }
    }

    public function render()
    {
        return view('livewire.reportes-item');
    }
}
