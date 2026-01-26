<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodoFacturacion extends Model
{
    protected $table = "periodo_facturacion";

    protected $fillable = [
        "nombre",
        "fecha_inicio",
        "fecha_fin",
        "fecha_lectura",
        "fecha_facturacion",
        "fecha_vencimiento",
        "estado",
        "company_id",
    ];
}
