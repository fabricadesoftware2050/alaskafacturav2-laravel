<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // --- Archivos ---
            $table->longText('logo')->nullable();
            $table->longText('escudo')->nullable();
            $table->longText('firma')->nullable();

            // --- Bloque 1: Identificación y Ubicación ---
            $table->string('tipo_identificacion', 100)->default('NIT');
            $table->string('numero_identificacion', 30);
            $table->string('dv', 5)->nullable();
            $table->string('razon_social');
            $table->string('responsabilidad', 100);
            $table->string('nombre_comercial')->nullable();
            $table->string('direccion');
            $table->string('ubicacion');
            $table->string('telefono', 50);
            $table->string('correo');
            $table->string('sitio_web')->nullable();

            // --- Bloque 2: Facturación y Tributos ---
            $table->string('factura_defecto')->default('Factura de venta');
            $table->boolean('emitir_estandar')->default(true);
            $table->boolean('emitir_exportacion')->default(false);
            $table->boolean('emitir_aiu')->default(false);
            $table->boolean('emitir_salud')->default(false);
            $table->boolean('mandato_ingresos')->default(false);
            $table->boolean('regimen_simple')->default(false);
            $table->boolean('gran_contribuyente')->default(false);
            $table->boolean('autorretenedor')->default(true);
            $table->boolean('agente_retencion_iva')->default(true);

            // --- Bloque 3: Representante Legal ---
            $table->string('representante_nombre',100);
            $table->string('representante_cedula', 30);
            $table->string('representante_cargo')->default('GERENTE GENERAL');
            $table->string('representante_celular', 25);
            $table->string('representante_correo',200);

            // --- Relación ---
            $table->foreignId('usuario_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
