<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CicloFacturacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ciclos';

    protected $fillable = [
        'company_id',
        'zona_id',
        'codigo',
        'nombre',
        'descripcion',
        'dia_inicio_lectura_sugerido',
        'dias_duracion_lectura',
        'dia_emision_sugerido',
        'dias_para_vencimiento',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'dia_inicio_lectura_sugerido' => 'integer',
        'dias_duracion_lectura' => 'integer',
        'dia_emision_sugerido' => 'integer',
        'dias_para_vencimiento' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function periodos()
    {
        return $this->hasMany(PeriodoFacturacion::class, 'ciclo_id');
    }

    public function zona()
    {
        return $this->belongsTo(Zona::class, 'zona_id'); // Asumiendo que existe
    }

    /*
    |--------------------------------------------------------------------------
    | Lógica de Negocio (La Magia)
    |--------------------------------------------------------------------------
    */

    /**
     * Crea el periodo de facturación para un mes/año específico
     * calculando las fechas automáticamente.
     */
    public function generarPeriodo(int $mes, int $anio)
    {
        // 1. Calcular Fecha Inicio Lectura (Ej: Día 1 del mes)
        $inicioLectura = Carbon::createFromDate($anio, $mes, $this->dia_inicio_lectura_sugerido);
        
        // 2. Calcular Fin Lectura (Inicio + Duración)
        $finLectura = $inicioLectura->copy()->addDays($this->dias_duracion_lectura);

        // 3. Calcular Fecha Emisión Factura (Ej: Día 5 del mes)
        // Nota: Si la fecha de lectura supera a la de emisión, ajustamos al día siguiente de lectura
        $fechaEmision = Carbon::createFromDate($anio, $mes, $this->dia_emision_sugerido);
        if ($fechaEmision->lte($finLectura)) {
            $fechaEmision = $finLectura->copy()->addDay();
        }

        // 4. Calcular Vencimiento (Emisión + Días Plazo)
        $vencimiento = $fechaEmision->copy()->addDays($this->dias_para_vencimiento);

        // 5. Crear el registro en la BD
        return $this->periodos()->create([
            'mes' => $mes,
            'anio' => $anio,
            'fecha_inicio_lectura' => $inicioLectura,
            'fecha_fin_lectura' => $finLectura,
            'fecha_emision' => $fechaEmision,
            'fecha_vencimiento' => $vencimiento,
            'fecha_suspension' => $vencimiento->copy()->addDays(5), // Ejemplo: 5 días tras vencer se corta
            'estado' => 'ABIERTO'
        ]);
    }
}