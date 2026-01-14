<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]
class Municipio extends Model
{
        protected $table = "municipios";
        protected $primaryKey = "id_municipio";

    public function departamento(): BelongsTo
    {
        return $this->belongsTo(Departamento::class, 'departamento_id');
    }

}
