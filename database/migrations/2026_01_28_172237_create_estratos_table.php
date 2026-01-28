<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('estratos', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('codigo', 10)->unique(); // E1, E2...
            $table->string('descripcion');
            $table->string('clase_uso', 60); // RESIDENCIAL
            $table->unsignedTinyInteger('estrato_nivel'); // 1 - 6
            $table->string('codigo_clase_uso', 50)->nullable();
            $table->string('factor_produccion', 50)->nullable();

            // Servicios (JSON)
            $table->json('acueducto')->nullable();
            $table->json('alcantarillado')->nullable();
            $table->json('aseo')->nullable();

            // Códigos SUI
            $table->string('sui_acueducto', 10)->nullable();
            $table->string('sui_alcantarillado', 10)->nullable();
            $table->string('sui_aseo', 10)->nullable();

            // Otros
            $table->string('tipo_productor', 50)->nullable();
            $table->boolean('residencial')->default(true);
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->nullOnDelete();
            $table->unique(['company_id', 'codigo']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estratos');
    }
};
