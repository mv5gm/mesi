<?php

namespace Database\Seeders;

use App\Models\Moneda;
use Illuminate\Database\Seeder;

class MonedaSeeder extends Seeder
{
    /**
     * Seed the application's monedas.
     */
    public function run(): void
    {
        $monedas = [
            ['codigo' => 'USD', 'nombre' => 'Dólar Estadounidense', 'simbolo' => '$', 'es_moneda_base' => false],
            ['codigo' => 'EUR', 'nombre' => 'Euro', 'simbolo' => '€', 'es_moneda_base' => false],
            ['codigo' => 'VES', 'nombre' => 'Bolívar', 'simbolo' => 'Bs.', 'es_moneda_base' => true],
            ['codigo' => 'COP', 'nombre' => 'Peso Colombiano', 'simbolo' => 'COL $', 'es_moneda_base' => false],
        ];

        foreach ($monedas as $moneda) {
            Moneda::updateOrCreate(['codigo' => $moneda['codigo']], $moneda);
        }
    }
}
