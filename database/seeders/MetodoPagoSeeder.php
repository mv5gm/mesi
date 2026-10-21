<?php

namespace Database\Seeders;

use App\Models\MetodoPago;
use App\Models\Moneda;
use Illuminate\Database\Seeder;

class MetodoPagoSeeder extends Seeder
{
    /**
     * Seed the available payment methods.
     */
    public function run(): void
    {
        $monedas = Moneda::pluck('id', 'codigo');

        $metodos = [
            ['nombre' => 'Efectivo', 'requiere_referencia' => false, 'moneda' => 'VES'],
            ['nombre' => 'Transferencia', 'requiere_referencia' => true, 'moneda' => 'VES'],
            ['nombre' => 'Punto de Venta', 'requiere_referencia' => false, 'moneda' => 'VES'],
            ['nombre' => 'Pago Móvil', 'requiere_referencia' => true, 'moneda' => 'VES'],
            ['nombre' => 'Zelle', 'requiere_referencia' => true, 'moneda' => 'USD'],
            ['nombre' => 'Divisa', 'requiere_referencia' => false, 'moneda' => 'USD'],
        ];

        $monedaBaseId = Moneda::where('es_moneda_base', true)->first()?->id;

        foreach ($metodos as $metodo) {
            MetodoPago::updateOrCreate(
                ['nombre' => $metodo['nombre']],
                [
                    'requiere_referencia' => $metodo['requiere_referencia'],
                    'moneda_id' => $monedas[$metodo['moneda']] ?? $monedaBaseId,
                ]
            );
        }
    }
}
