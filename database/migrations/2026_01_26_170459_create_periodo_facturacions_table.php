<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('periodo_facturacion', function (Blueprint $table) {
            $table->id();

            // 1. Vinculación con la Regla
            $table->foreignId('ciclo_id')
                  ->constrained('ciclos')
                  ->onDelete('cascade') // Si borras el ciclo, borras su historia (o usa restrict)
                  ->comment('Hereda las reglas configuradas en el ciclo');

            // 2. Definición Temporal
            $table->unsignedTinyInteger('mes')->comment('Mes calendario (1-12)');
            $table->year('anio')->comment('Año fiscal, ej: 2026');

            // 3. Fechas Calendario REALES (EJECUCIÓN)
            // Estas son las fechas que van impresas en el recibo.

            $table->date('fecha_inicio_lectura')->comment('Inicio real de toma de lecturas');
            $table->date('fecha_fin_lectura')->nullable()->comment('Fin real de toma de lecturas');
            
            $table->date('fecha_emision')->comment('Fecha de generación de la factura');
            $table->date('fecha_vencimiento')->comment('Fecha límite de pago');
            $table->date('fecha_suspension')->nullable()->comment('Fecha programada de corte');

            // 4. Estado del Proceso
            $table->enum('estado', ['ABIERTO', 'EN_LECTURA', 'FACTURADO', 'CERRADO', 'ANULADO'])
                  ->default('ABIERTO')
                  ->index()
                  ->comment('Estado actual del flujo de facturación');

            $table->timestamps();
            $table->softDeletes();

            // RESTRICCIÓN CRÍTICA:
            // Evita duplicar la facturación del mismo mes para el mismo ciclo.
            $table->unique(['ciclo_id', 'mes', 'anio'], 'unique_periodo_ciclo_mes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodo_facturacion');
    }
};