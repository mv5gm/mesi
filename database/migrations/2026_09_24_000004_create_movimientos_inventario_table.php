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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->foreignId('usuario_id')->constrained('users');
            $table->enum('tipo_movimiento', ['ENTRADA', 'SALIDA', 'TRANSFERENCIA', 'AJUSTE']);
            $table->integer('cantidad');
            $table->string('tipo_referencia', 50)->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->timestamp('creado_en')->useCurrent();
            $table->text('notas')->nullable();
            $table->index('creado_en', 'idx_movimientos_creado_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
