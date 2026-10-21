<?php

namespace Database\Factories;

use App\Models\MetodoPago;
use App\Models\Moneda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MetodoPago>
 */
class MetodoPagoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->randomElement(['Efectivo', 'Transferencia', 'Punto de Venta', 'Zelle', 'Pago Móvil']),
            'requiere_referencia' => fake()->boolean(),
            'moneda_id' => Moneda::factory(),
        ];
    }
}
