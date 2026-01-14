<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]
class Departamento extends Model
{
    protected $table = "departamentos";
    protected $primaryKey = "id_departamento";

    public function municipios(): HasMany
    {
        return $this->hasMany(Municipio::class, 'departamento_id');
    }
}
