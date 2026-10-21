<?php

namespace Database\Seeders;

use App\Models\Moneda;
use App\Models\TasaCambio;
use Illuminate\Database\Seeder;

class TasaCambioSeeder extends Seeder
{
    /**
     * Seed conversion rates for non-base monedas.
     *
     * La tasa indica cuántas unidades de la moneda equivalen a 1 dólar.
     */
    public function run(): void
    {
        $tasas = [
            'VES' => 950.0000,
            'EUR' => 0.9200,
            'COP' => 3800.0000,
        ];

        foreach ($tasas as $codigo => $tasa) {
            $moneda = Moneda::where('codigo', $codigo)->first();

            if ($moneda === null) {
                continue;
            }

            $tasaCambio = TasaCambio::query()
                ->where('moneda_id', $moneda->id)
                ->orderByDesc('fecha_efectiva')
                ->first();

            if ($tasaCambio !== null) {
                $tasaCambio->update(['tasa' => $tasa]);

                continue;
            }

            TasaCambio::create([
                'moneda_id' => $moneda->id,
                'tasa' => $tasa,
                'fecha_efectiva' => now(),
            ]);
        }
    }
}
