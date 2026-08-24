<?php

namespace App\Notifications;

use App\Models\Reporte;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReporteEstadoNotificacion extends Notification
{
    use Queueable;

    public Reporte $reporte;
    public string $nuevoEstado;
    public string $tecnicoNombre;

    /**
     * Create a new notification instance.
     */
    public function __construct(Reporte $reporte, string $nuevoEstado, string $tecnicoNombre)
    {
        $this->reporte       = $reporte;
        $this->nuevoEstado   = $nuevoEstado;
        $this->tecnicoNombre = $tecnicoNombre;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'reporte_id'     => $this->reporte->id,
            'solicitante'    => $this->reporte->solicitante,
            'tecnico_nombre' => $this->tecnicoNombre,
            'nuevo_estado'   => $this->nuevoEstado,
            'mensaje'        => "El técnico {$this->tecnicoNombre} marcó el reporte #{$this->reporte->id} como {$this->nuevoEstado}.",
        ];
    }
}
