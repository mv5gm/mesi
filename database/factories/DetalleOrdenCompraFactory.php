<?php

namespace Database\Factories;

use App\Models\DetalleOrdenCompra;
use App\Models\OrdenCompra;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleOrdenCompra>
 */
class DetalleOrdenCompraFactory extends Factory
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
            'producto_id' => Producto::factory(),
            'cantidad' => fake()->numberBetween(1, 100),
            'costo_unitario' => fake()->randomFloat(2, 1.00, 500.00),
        ];
    }
}
