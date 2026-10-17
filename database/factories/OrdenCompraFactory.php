<?php

namespace Database\Factories;

use App\Enums\EstadoOrdenCompra;
use App\Enums\EstadoPago;
use App\Models\Moneda;
use App\Models\OrdenCompra;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrdenCompra>
 */
class OrdenCompraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'proveedor_id' => Proveedor::factory(),
            'usuario_id' => User::factory(),
            'moneda_id' => Moneda::factory(),
            'tasa_cambio' => 1.0000,
            'monto_total' => fake()->randomFloat(2, 100.00, 50000.00),
            'monto_total_base' => fn (array $attributes): float => round($attributes['monto_total'] * $attributes['tasa_cambio'], 2),
            'estado' => fake()->randomElement(EstadoOrdenCompra::cases()),
            'estado_pago' => fake()->randomElement(EstadoPago::cases()),
            'fecha_orden' => fake()->dateTimeThisMonth(),
        ];
    }
}
