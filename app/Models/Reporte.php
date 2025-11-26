<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Reporte extends Model
{
    use HasFactory;
    protected $table = 'reportes';

    protected $fillable = [
        'solicitante',
        'descripcion',
        'categoria_id',
        'estado_id',
        'departamento_congreso_id',
        'capturo_user_id',
        'area_informatica_id',
        'tecnico_user_id',
        'numero_copias',
        'numero_inventario',
        'closed_at',
        'evento_id',
    ];

    public function area()
    {
        return $this->belongsTo(AreasInformatica::class, 'area_informatica_id');
    }

    public function capturista()
    {
        return $this->belongsTo(User::class, 'capturo_user_id');
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class, 'reporte_id')->latest();
    }

    public function departamento()
    {
        return $this->belongsTo(DepartamentoCongreso::class, 'departamento_congreso_id');
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class, 'estado_id');
    }

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'evento_id');
    }

    public function tecnico()
    {
        return $this->belongsTo(User::class, 'tecnico_user_id');
    }

    public function tecnicos()
    {
        return $this->belongsToMany(\App\Models\User::class, 'reporte_user')
            ->withTimestamps();
        // return $this->belongsToMany(User::class, 'reporte_user')
        //     ->withPivot(['rol', 'asignado_at'])
        //     ->withTimestamps();
    }

    // (Opcional) atajo: cantidad de comentarios
    public function getComentariosCountAttribute()
    {
        return $this->comentarios()->count();
    }

    public function getFechaCreacionAttribute()
    {
        return $this->created_at->format('d/m/Y');
    }

    public function getTiempoTranscurridoAttribute()
    {
        $minutes = $this->created_at->diffInMinutes();
        $hours   = $this->created_at->diffInHours();
        $days    = $this->created_at->diffInDays();
        $weeks   = $this->created_at->diffInWeeks();
        $months  = $this->created_at->diffInMonths();

        if ($minutes < 60) {
            return $minutes . 'm';
        } elseif ($hours < 24) {
            return $hours . 'h';
        } elseif ($days < 7) {
            return $days . 'd';
        } elseif ($weeks < 5) {
            return $weeks . 'sem'; // semanas
        } else {
            return $months . 'ms'; // meses
        }
    }

    public function getEventoNombreAttribute(): string
    {
        return $this->evento?->nombre ?? '';
    }

    public function scopeAbiertos($q)
    {
        return $q->whereNotIn('estado_id', [3, 4]); // ajusta IDs
    }

    public function getColorHeaderAttribute(): string
    {
        $name = Str::of($this->estado->name ?? '')->trim()->lower();

        if ($name == 'atendido') {
            return $this->created_at->lt(now()->subDays(3)) ? 'bg-green-200' : 'bg-green-100';
        }

        if ($name == 'pendiente') {
            return $this->created_at->lt(now()->subDays(3)) ? 'bg-vino-100' : 'bg-slate-100';
        }

        return 'bg-slate-100';
    }

    public function dictamen(){
        return $this->hasOne(Dictamen::class);
    }

    public function dictamenes()
{
    return $this->hasMany(\App\Models\Dictamen::class);
}
}
