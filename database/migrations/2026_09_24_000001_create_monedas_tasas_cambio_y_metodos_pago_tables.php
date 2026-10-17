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
        Schema::create('monedas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique();
            $table->string('nombre', 50);
            $table->string('simbolo', 5);
            $table->boolean('es_moneda_base')->default(false);
        });

        Schema::create('tasas_cambio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('moneda_id')->constrained('monedas');
            $table->decimal('tasa', 16, 4);
            $table->dateTime('fecha_efectiva')->useCurrent();
            $table->index(['moneda_id', 'fecha_efectiva'], 'idx_tasas_moneda_fecha');
        });

        Schema::create('metodos_pago', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->boolean('requiere_referencia')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metodos_pago');
        Schema::dropIfExists('tasas_cambio');
        Schema::dropIfExists('monedas');
    }
};
