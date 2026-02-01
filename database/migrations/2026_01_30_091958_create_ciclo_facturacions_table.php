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

            
            // 2. Identificación Visual
            $table->string('codigo', 50)->comment('Identificador interno, ej: C01');
            $table->string('nombre', 100)->comment('Nombre descriptivo, ej: Ciclo Norte Residencial');
            $table->text('descripcion')->nullable();
            
            // 3. Reglas de Cronograma (PLANIFICACIÓN)
            // Estas reglas definen el "Deber ser" de cada mes.

            $table->unsignedTinyInteger('dia_inicio_lectura_sugerido')
                  ->default(1)
                  ->comment('Día del mes ideal para iniciar lecturas (1-28)');

                  $table->unsignedTinyInteger('dias_duracion_lectura')
                  ->default(3)
                  ->comment('Días hábiles estimados para completar la ruta');
                  
            $table->unsignedTinyInteger('dia_emision_sugerido')
                  ->default(5)
                  ->comment('Día del mes ideal para generar la factura (PDF)');

                  $table->unsignedTinyInteger('dias_para_vencimiento')
                  ->default(15)
                  ->comment('Días plazo para pago (se suma a la fecha de emisión)');
                  
                  
                  // 4. Control
                  $table->boolean('activo')->default(true);
                  $table->timestamps();
                  $table->softDeletes();
                  
                  // 1. Relaciones y Organización
                 
             $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();
             $table->foreignId('zona_id')
                ->nullable()
                ->constrained('zonas')
                ->nullOnDelete();
                  // RESTRICCIÓN: Código único por empresa
            $table->unique(['company_id', 'codigo'], 'unique_ciclo_empresa');
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