<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * La moneda base pasa a ser el dólar. A partir de ahora la tasa de cada moneda
     * indica cuántas unidades de esa moneda equivalen a 1 dólar (por ejemplo VES = 950),
     * y los montos en moneda base se calculan dividiendo el monto entre la tasa.
     */
    public function up(): void
    {
        $monedas = DB::table('monedas')->get()->keyBy('codigo');
        $dolar = $monedas->get('USD');
        $baseAnterior = DB::table('monedas')->where('es_moneda_base', true)->first();

        if ($dolar === null) {
            return;
        }

        if ($baseAnterior !== null && $baseAnterior->id !== $dolar->id) {
            $this->convertirTasas($baseAnterior->id, $dolar->id);
        }

        DB::table('monedas')->update(['es_moneda_base' => false]);
        DB::table('monedas')->where('id', $dolar->id)->update(['es_moneda_base' => true]);

        DB::table('productos')->update(['moneda_id' => $dolar->id]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $monedas = DB::table('monedas')->get()->keyBy('codigo');
        $dolar = $monedas->get('USD');
        $baseAnterior = DB::table('monedas')->where('codigo', 'VES')->first();

        if ($dolar !== null && $baseAnterior !== null) {
            $this->convertirTasas($dolar->id, $baseAnterior->id);
        }

        DB::table('monedas')->update(['es_moneda_base' => false]);

        if ($baseAnterior !== null) {
            DB::table('monedas')->where('id', $baseAnterior->id)->update(['es_moneda_base' => true]);
        }
    }

    /**
     * Reexpresa las tasas tomando como base la moneda indicada.
     *
     * Las tasas del dólar pasan a ser las de la base anterior y el resto de las
     * monedas se recalculan para que equivalgan a 1 dólar.
     */
    private function convertirTasas(int $baseActualId, int $baseNuevaId): void
    {
        $tasaBase = (float) DB::table('tasas_cambio')
            ->where('moneda_id', $baseNuevaId)
            ->orderByDesc('fecha_efectiva')
            ->value('tasa');

        if ($tasaBase <= 0) {
            return;
        }

        DB::table('tasas_cambio')
            ->where('moneda_id', $baseNuevaId)
            ->update(['moneda_id' => $baseActualId]);

        DB::table('tasas_cambio')
            ->whereNotIn('moneda_id', [$baseActualId, $baseNuevaId])
            ->orderBy('id')
            ->each(function (object $tasa) use ($tasaBase): void {
                DB::table('tasas_cambio')->where('id', $tasa->id)->update([
                    'tasa' => round($tasaBase / (float) $tasa->tasa, 4),
                ]);
            });
    }
};
