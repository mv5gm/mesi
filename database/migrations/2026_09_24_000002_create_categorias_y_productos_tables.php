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
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_padre_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('moneda_id')->constrained('monedas');
            $table->string('sku', 50)->unique();
            $table->string('codigo_barras', 100)->unique()->nullable();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio_costo', 12, 2)->default(0.00);
            $table->decimal('precio_venta', 12, 2)->default(0.00);
            $table->integer('stock_minimo')->default(0);
            $table->integer('stock_maximo')->nullable();
            $table->string('unidad_medida', 20)->default('PZA');
            $table->timestamp('creado_en')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
        Schema::dropIfExists('categorias');
    }
};
