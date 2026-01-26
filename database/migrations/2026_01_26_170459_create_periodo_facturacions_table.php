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
             $table->string('codigo', 10);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->date('fecha_lectura')->nullable(true);
            $table->date('fecha_facturacion')->nullable(true);
            $table->date('fecha_vencimiento')->nullable(true);
            $table->enum('estado',['ABIERTO','EN LECTURA','FACTURADO','CERRADO'])->default('ABIERTO');
            // --- Relación ---
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();
            $table->timestamps();
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
