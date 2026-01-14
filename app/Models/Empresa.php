<?php

namespace App\Models;
use ApiPlatform\Metadata\ApiResource;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

#[ApiResource]
class Empresa extends Model
{
    protected $table = "companies";
    protected $fillable = [
        'logo',
        'escudo',
        'firma',
        'tipo_identificacion',
        'numero_identificacion',
        'dv',
        'razon_social',
        'responsabilidad',
        'nombre_comercial',
        'direccion',
        'ubicacion',
        'telefono',
        'correo',
        'sitio_web',
        'factura_defecto',
        'emitir_estandar',
        'emitir_exportacion',
        'emitir_aiu',
        'emitir_salud',
        'mandato_ingresos',
        'regimen_simple',
        'gran_contribuyente',
        'autorretenedor',
        'agente_retencion_iva',
        'representante_nombre',
        'representante_cedula',
        'representante_cargo',
        'representante_celular',
        'representante_correo',
        'usuario_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
