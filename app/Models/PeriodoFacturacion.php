<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodoFacturacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'periodo_facturacion';

    protected $fillable = [
        'ciclo_id',
        'mes',
        'anio',
        'fecha_inicio_lectura',
        'fecha_fin_lectura',
        'fecha_emision',
        'fecha_vencimiento',
        'fecha_suspension',
        'estado', // 'ABIERTO', 'EN_LECTURA', 'FACTURADO', 'CERRADO', 'ANULADO'
    ];

    /**
     * Conversión automática de fechas para poder usar métodos como ->format('d/m/Y')
     */
    protected $casts = [
        'fecha_inicio_lectura' => 'date:Y-m-d',
        'fecha_fin_lectura'    => 'date:Y-m-d',
        'fecha_emision'        => 'date:Y-m-d',
        'fecha_vencimiento'    => 'date:Y-m-d',
        'fecha_suspension'     => 'date:Y-m-d',
        'mes'                  => 'integer',
        'anio'                 => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class, 'ciclo_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Filtros útiles)
    |--------------------------------------------------------------------------
    */

    // Uso: PeriodoFacturacion::abiertos()->get();
    public function scopeAbiertos($query)
    {
        return $query->where('estado', 'ABIERTO');
    }

    // Uso: PeriodoFacturacion::delMes(2, 2026)->get();
    public function scopeDelMes($query, $mes, $anio)
    {
        return $query->where('mes', $mes)->where('anio', $anio);
    }

    /*
    |--------------------------------------------------------------------------
    | Accesors (Atributos virtuales)
    |--------------------------------------------------------------------------
    */

    // Uso: $periodo->nombre_completo // "Febrero 2026"
    public function getNombreCompletoAttribute()
    {
        // Necesitas tener configurado setLocale en español en AppServiceProvider
        return \Carbon\Carbon::createFromDate($this->anio, $this->mes, 1)->translatedFormat('F Y');
    }
}