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
        Schema::create('ciclos', function (Blueprint $table) {
            $table->id();
            
            // 1. Identificación y Configuración (Se repetirán cada mes)
            // Nota: Quitamos el ->unique() individual de 'codigo' porque se repetirá en cada periodo
            $table->string('codigo', 50)->comment('Código del ciclo, ej: C01. Se repite cada mes.');
            $table->string('nombre', 100)->comment('Nombre descriptivo, ej: Ciclo 1 - Casco Urbano');
            $table->text('descripcion')->nullable();
            
            // 2. Definición del Periodo (El factor tiempo)
            $table->unsignedTinyInteger('periodo_mes')->comment('Mes del 1 al 12');
            $table->year('periodo_anio')->comment('Año fiscal, ej: 2026');

            // 3. Fechas Operativas Reales (Cronograma)
            $table->date('fecha_inicio_lectura')->comment('Fecha inicio toma de lecturas');
            $table->date('fecha_fin_lectura')->nullable()->comment('Fecha fin toma de lecturas');
            $table->date('fecha_facturacion')->comment('Fecha de generación de la factura');
            $table->date('fecha_pago_oportuno')->nullable()->comment('Fecha sugerida de pago');
            $table->date('fecha_vencimiento')->comment('Fecha límite legal');
            $table->date('fecha_suspension')->comment('Fecha de corte por no pago');

            // 4. Parámetros de configuración (Snapshot de reglas)
            $table->tinyInteger('dia_corte_sugerido')->nullable()->comment('Día base para calcular fechas futuras');
            $table->tinyInteger('dias_vencimiento')->default(15)->comment('Días dados para pagar');

            // 5. Control de Estado y Auditoría
            $table->enum('estado', ['ABIERTO', 'EN_LECTURA', 'FACTURADO', 'CERRADO'])
                  ->default('ABIERTO')
                  ->comment('Control del flujo de trabajo');
            
            $table->boolean('activo')->default(true);
            $table->unsignedBigInteger('company_id')->nullable()->index()->comment('ID de la empresa (SaaS)');
            $table->unsignedBigInteger('zona_id')->nullable()->index()->comment('ID de la zona');
            
            $table->timestamps();
            $table->softDeletes();

            // 6. Restricción Única Compuesta (CRÍTICO EN TABLA ÚNICA)
            // Esto evita que crees el ciclo "C01" dos veces para "Enero 2026" en la misma empresa.
            $table->unique(
                ['company_id', 'codigo', 'periodo_mes', 'periodo_anio'], 
                'unique_ciclo_periodo_empresa'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ciclos');
    }
};