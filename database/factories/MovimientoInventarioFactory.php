<?php

namespace Database\Factories;

use App\Enums\TipoMovimiento;
use App\Models\Almacen;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovimientoInventario>
 */
class MovimientoInventarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'almacen_id' => Almacen::factory(),
            'usuario_id' => User::factory(),
            'tipo_movimiento' => fake()->randomElement(TipoMovimiento::cases()),
            'cantidad' => fake()->numberBetween(1, 100),
            'tipo_referencia' => fake()->randomElement(['ORDEN_COMPRA', 'ORDEN_VENTA', 'AJUSTE_MANUAL']),
            'referencia_id' => null,
            'notas' => fake()->sentence(),
        ];
    }
}
