<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Métodos de pago que se liquidan en una moneda distinta a la base.
     *
     * @var array<string, string>
     */
    private const MONEDA_POR_METODO = [
        'efectivo' => 'VES',
        'transferencia' => 'VES',
        'punto de venta' => 'VES',
        'pago móvil' => 'VES',
        'pago movil' => 'VES',
        'zelle' => 'USD',
        'divisa' => 'USD',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('metodos_pago', function (Blueprint $table) {
            $table->foreignId('moneda_id')->nullable()->after('nombre')->constrained('monedas');
        });

        $this->asignarMonedaAMetodosPago();

        Schema::table('metodos_pago', function (Blueprint $table) {
            $table->foreignId('moneda_id')->nullable(false)->change();
        });

        Schema::table('pagos_orden_compra', function (Blueprint $table) {
            $table->dropConstrainedForeignId('moneda_id');
        });

        Schema::table('pagos_orden_venta', function (Blueprint $table) {
            $table->dropConstrainedForeignId('moneda_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos_orden_venta', function (Blueprint $table) {
            $table->foreignId('moneda_id')->nullable()->constrained('monedas');
        });

        Schema::table('pagos_orden_compra', function (Blueprint $table) {
            $table->foreignId('moneda_id')->nullable()->constrained('monedas');
        });

        Schema::table('metodos_pago', function (Blueprint $table) {
            $table->dropConstrainedForeignId('moneda_id');
        });
    }

    /**
     * Asigna a cada método de pago la moneda con la que se liquida.
     */
    private function asignarMonedaAMetodosPago(): void
    {
        $monedaBaseId = DB::table('monedas')->where('es_moneda_base', true)->orderBy('id')->value('id')
            ?? DB::table('monedas')->orderBy('id')->value('id');

        if ($monedaBaseId === null) {
            return;
        }

        DB::table('metodos_pago')->orderBy('id')->each(function (object $metodo) use ($monedaBaseId): void {
            $nombre = mb_strtolower(trim($metodo->nombre));
            $codigo = self::MONEDA_POR_METODO[$nombre] ?? null;

            $monedaId = $codigo !== null
                ? DB::table('monedas')->where('codigo', $codigo)->value('id')
                : null;

            DB::table('metodos_pago')->where('id', $metodo->id)->update([
                'moneda_id' => $monedaId ?? $monedaBaseId,
            ]);
        });
    }
};
