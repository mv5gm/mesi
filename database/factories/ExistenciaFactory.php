<?php

namespace Database\Factories;

use App\Models\Almacen;
use App\Models\Existencia;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Existencia>
 */
class ExistenciaFactory extends Factory
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
            'cantidad' => fake()->numberBetween(0, 500),
        ];
    }
}
