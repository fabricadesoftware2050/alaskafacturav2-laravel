<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estrato extends Model
{
    protected $table = 'estratos';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'company_id',
        'codigo',
        'descripcion',
        'clase_uso',
        'estrato_nivel',
        'codigo_clase_uso',
        'factor_produccion',
        'acueducto',
        'alcantarillado',
        'aseo',
        'sui_acueducto',
        'sui_alcantarillado',
        'sui_aseo',
        'tipo_productor',
        'residencial',
    ];


    /**
     * Casts automáticos
     */
    protected $casts = [
        'residencial'   => 'boolean',

        'acueducto'     => 'array',
        'alcantarillado'=> 'array',
        'aseo'          => 'array',
    ];
}
