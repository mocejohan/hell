<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dictamen extends Model
{
    use HasFactory;

    protected $table = 'dictamenes';

    protected $fillable = [
        'reporte_id',
        'bien_id',
        'inventario',
        'equipo',
        'marca',
        'modelo',
        'serie',
        'diagnostico',
        'sugerencia',
        'observaciones',
    ];

    public function reporte()
    {
        return $this->belongsTo(Reporte::class, 'reporte_id');
    }

    public function bien()
    {
        return $this->belongsTo(Bien::class, 'bien_id');
    }

    public function versiones()
    {
        return $this->hasMany(DictamenVersion::class, 'dictamen_id')->orderBy('version', 'desc');
    }

    /**
     * Determina si el dictamen puede ser modificado por un técnico.
     * Solo es editable mientras el reporte no esté cerrado ni cancelado por la Mesa de Control.
     */
    public function puedeModificarse(): bool
    {
        if (!$this->reporte) {
            return true;
        }

        // Si el reporte está en estado Cerrado (3), Cancelado (4) o tiene closed_at
        if (in_array($this->reporte->estado_id, [3, 4]) || !empty($this->reporte->closed_at)) {
            return false;
        }

        return true;
    }
}
