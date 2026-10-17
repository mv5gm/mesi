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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('correo', 150)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->text('direccion')->nullable();
        });

        Schema::create('ordenes_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('usuario_id')->constrained('users');
            $table->foreignId('moneda_id')->constrained('monedas');
            $table->decimal('tasa_cambio', 16, 4);
            $table->decimal('monto_total', 12, 2)->default(0.00);
            $table->decimal('monto_total_base', 12, 2)->default(0.00);
            $table->enum('estado', ['PENDIENTE', 'COMPLETADA', 'CANCELADA'])->default('PENDIENTE');
            $table->enum('estado_pago', ['PENDIENTE', 'PARCIAL', 'PAGADO'])->default('PENDIENTE');
            $table->dateTime('fecha_orden')->useCurrent();
        });

        Schema::create('detalles_orden_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_venta_id')->constrained('ordenes_venta')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 12, 2);
        });

        Schema::create('pagos_orden_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_venta_id')->constrained('ordenes_venta')->cascadeOnDelete();
            $table->foreignId('metodo_pago_id')->constrained('metodos_pago');
            $table->foreignId('moneda_id')->constrained('monedas');
            $table->decimal('monto', 12, 2);
            $table->decimal('tasa_cambio', 16, 4);
            $table->decimal('monto_base', 12, 2);
            $table->string('numero_referencia', 100)->nullable();
            $table->dateTime('fecha_pago')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_orden_venta');
        Schema::dropIfExists('detalles_orden_venta');
        Schema::dropIfExists('ordenes_venta');
        Schema::dropIfExists('clientes');
    }
};
