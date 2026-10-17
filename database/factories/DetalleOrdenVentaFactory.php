<?php

namespace Database\Factories;

use App\Models\DetalleOrdenVenta;
use App\Models\OrdenVenta;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleOrdenVenta>
 */
class DetalleOrdenVentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'orden_venta_id' => OrdenVenta::factory(),
            'producto_id' => Producto::factory(),
            'cantidad' => fake()->numberBetween(1, 50),
            'precio_unitario' => fake()->randomFloat(2, 1.00, 1000.00),
        ];
    }
}
