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
}
