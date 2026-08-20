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

    
    public function dictaminar()
    {
        $this->dispatch('abrirModalDictamen', id: $this->reporte->id);
    }

    public function comentar()
    {
        $this->dispatch('abrirModalComentario', id: $this->reporte->id);
    }

    // public function dictamen()
    // {
    //     // Seguridad adicional del lado del servidor (opcional si ya restringiste en ruta):
    //     if (!auth()->user()->can('ImprimirDictamen')) {
    //         $this->dispatch('toast', type: 'error', msg: 'No tienes permiso para imprimir el dictamen.');
    //         return;
    //     }

    //     // Verifica que exista al menos un dictamen
    //     if ($this->reporte->dictamenes()->doesntExist()) {
    //         $this->dispatch('toast', type: 'warning', msg: 'Este reporte no tiene dictamen.');
    //         return;
    //     }

    //     $url = route('reportes.dictamen.pdf', $this->reporte->id);
    //     // Dispara un evento para que el front abra la URL en nueva pestaña
    //     $this->dispatch('abrir-url', url: $url);
    // }


    protected $listeners = ['refrescarComentarios'];

    public function refrescarComentarios(int $id)
    {
        if ($id === $this->reporte->id) {
            $this->reporte->refresh()->load(['categoria', 'estado', 'tecnico', 'tecnicos', 'comentarios.user']);
        }
    }

    public function render()
    {
        return view('livewire.reportes-item');
    }
}
