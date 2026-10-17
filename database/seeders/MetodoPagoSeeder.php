<?php

namespace Database\Seeders;

use App\Models\MetodoPago;
use Illuminate\Database\Seeder;

class MetodoPagoSeeder extends Seeder
{
    /**
     * Seed the available payment methods.
     */
    public function run(): void
    {
        $metodos = [
            ['nombre' => 'Efectivo', 'requiere_referencia' => false],
            ['nombre' => 'Transferencia', 'requiere_referencia' => true],
            ['nombre' => 'Punto de Venta', 'requiere_referencia' => false],
            ['nombre' => 'Zelle', 'requiere_referencia' => true],
            ['nombre' => 'Pago Móvil', 'requiere_referencia' => true],
        ];

        foreach ($metodos as $metodo) {
            MetodoPago::updateOrCreate(['nombre' => $metodo['nombre']], $metodo);
        }
    }
}
