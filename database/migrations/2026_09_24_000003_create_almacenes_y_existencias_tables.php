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
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('ubicacion', 255)->nullable();
            $table->boolean('esta_activo')->default(true);
        });

        Schema::create('existencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->integer('cantidad')->default(0);
            $table->timestamp('ultima_actualizacion')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['producto_id', 'almacen_id'], 'unq_existencias_producto_almacen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('existencias');
        Schema::dropIfExists('almacenes');
    }
};
