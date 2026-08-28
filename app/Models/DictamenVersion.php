<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DictamenVersion extends Model
{
    use HasFactory;

    protected $table = 'dictamen_versiones';

    protected $fillable = [
        'dictamen_id',
        'user_id',
        'version',
        'inventario',
        'equipo',
        'marca',
        'modelo',
        'serie',
        'diagnostico',
        'sugerencia',
        'observaciones',
        'motivo_cambio',
    ];

    public function dictamen()
    {
        return $this->belongsTo(Dictamen::class, 'dictamen_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
