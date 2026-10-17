<?php

namespace Database\Seeders;

use App\Models\Moneda;
use App\Models\TasaCambio;
use Illuminate\Database\Seeder;

class TasaCambioSeeder extends Seeder
{
    /**
     * Seed conversion rates for non-base monedas.
     */
    public function run(): void
    {
        $tasas = [
            'USD' => 36.0000,
            'EUR' => 39.5000,
            'COP' => 0.0093,
        ];

        foreach ($tasas as $codigo => $tasa) {
            $moneda = Moneda::where('codigo', $codigo)->first();

            if ($moneda === null) {
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
