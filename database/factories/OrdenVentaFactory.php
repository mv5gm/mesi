<?php

namespace Database\Factories;

use App\Enums\EstadoOrdenVenta;
use App\Enums\EstadoPago;
use App\Models\Cliente;
use App\Models\Moneda;
use App\Models\OrdenVenta;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrdenVenta>
 */
class OrdenVentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'usuario_id' => User::factory(),
            'moneda_id' => Moneda::factory(),
            'tasa_cambio' => 1.0000,
            'monto_total' => fake()->randomFloat(2, 10.00, 50000.00),
            'monto_total_base' => fn (array $attributes): float => round($attributes['monto_total'] * $attributes['tasa_cambio'], 2),
            'estado' => fake()->randomElement(EstadoOrdenVenta::cases()),
            'estado_pago' => fake()->randomElement(EstadoPago::cases()),
            'fecha_orden' => fake()->dateTimeThisMonth(),
        ];
    }
}
