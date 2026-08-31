<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bien extends Model
{
    use HasFactory;

    protected $table = 'bienes';

    protected $fillable = [
        'numero_inventario',
        'numero_inventario_anterior',
        'equipo',
        'marca',
        'modelo',
        'serie',
        'ubicacion',
    ];

    /**
     * Dictámenes asociados a este bien.
     */
    public function dictamenes()
    {
        return $this->hasMany(Dictamen::class, 'bien_id');
    }

    /**
     * Verifica si el bien tiene un dictamen activo (reporte no cerrado ni cancelado).
     * Opcionalmente excluye un reporte específico (para edición del mismo reporte).
     *
     * @return Dictamen|null  El dictamen activo encontrado, o null si no hay.
     */
    public function tieneDictamenActivo(?int $exceptoReporteId = null): ?Dictamen
    {
        return $this->dictamenes()
            ->whereHas('reporte', fn($q) => $q->whereNotIn('estado_id', [3, 4]))
            ->when($exceptoReporteId, fn($q) => $q->where('reporte_id', '!=', $exceptoReporteId))
            ->with('reporte')
            ->first();
    }

    /**
     * Obtiene todos los dictámenes (activos e inactivos) de este bien,
     * con su reporte y estado, para mostrar en el historial.
     */
    public function historialDictamenes()
    {
        return $this->dictamenes()
            ->with(['reporte.estado', 'reporte.departamento'])
            ->orderByDesc('created_at');
    }
}
