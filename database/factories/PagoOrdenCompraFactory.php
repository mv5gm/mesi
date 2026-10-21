<?php

namespace Database\Factories;

use App\Models\MetodoPago;
use App\Models\OrdenCompra;
use App\Models\PagoOrdenCompra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PagoOrdenCompra>
 */
class PagoOrdenCompraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'orden_compra_id' => OrdenCompra::factory(),
            'metodo_pago_id' => MetodoPago::factory(),
            'monto' => fake()->randomFloat(2, 1.00, 50000.00),
            'tasa_cambio' => 1.0000,
            'monto_base' => fn (array $attributes): float => round($attributes['monto'] * $attributes['tasa_cambio'], 2),
            'numero_referencia' => fake()->optional()->bothify('REF-?????-#####'),
            'fecha_pago' => fake()->dateTimeThisMonth(),
        ];
    }
}
