<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ciclo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ciclos';

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        
        // Periodo
        'periodo_mes',
        'periodo_anio',

        // Fechas Cronograma
        'fecha_inicio_lectura',
        'fecha_fin_lectura',
        'fecha_facturacion',
        'fecha_pago_oportuno',
        'fecha_vencimiento',
        'fecha_suspension',

        // Configuración
        'dia_corte_sugerido',
        'dias_vencimiento',

        // Estado y Relaciones
        'estado', // 'ABIERTO', 'EN_LECTURA', 'FACTURADO', 'CERRADO'
        'activo',
        'company_id',
        'zona_id'
    ];

    /**
     * Conversión automática de tipos de datos.
     * CRÍTICO: Esto convierte los strings de la BD a objetos Carbon (Fecha)
     */
    protected $casts = [
        'fecha_inicio_lectura' => 'date:Y-m-d',
        'fecha_fin_lectura'    => 'date:Y-m-d',
        'fecha_facturacion'    => 'date:Y-m-d',
        'fecha_pago_oportuno'  => 'date:Y-m-d',
        'fecha_vencimiento'    => 'date:Y-m-d',
        'fecha_suspension'     => 'date:Y-m-d',
        'activo'               => 'boolean',
        'periodo_mes'          => 'integer',
        'periodo_anio'         => 'integer',
    ];

    /**
     * Relación con la Empresa (SaaS)
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'company_id');
    }

    /**
     * Relación con la Zona (Opcional, si tienes tabla zonas)
     */
    public function zona()
    {
        return $this->belongsTo(Zona::class, 'zona_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Filtros rápidos)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope para filtrar por estado "ABIERTO"
     */
    public function scopeAbiertos($query)
    {
        return $query->where('estado', 'ABIERTO');
    }

    /**
     * Scope para buscar el periodo actual
     */
    public function scopeDelPeriodo($query, $mes, $anio)
    {
        return $query->where('periodo_mes', $mes)
                     ->where('periodo_anio', $anio);
    }
}