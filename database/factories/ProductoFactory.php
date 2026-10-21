<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Moneda;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'categoria_id' => Categoria::factory(),
            'moneda_id' => Moneda::where('codigo', 'USD')->value('id') ?? Moneda::factory(),
            'sku' => fake()->unique()->bothify('SKU-#####'),
            'codigo_barras' => fake()->unique()->numerify('750#############'),
            'nombre' => fake()->words(3, true),
            'descripcion' => fake()->sentence(),
            'precio_costo' => fake()->randomFloat(2, 0.50, 500.00),
            'precio_venta' => fake()->randomFloat(2, 1.00, 1000.00),
            'stock_minimo' => fake()->numberBetween(0, 10),
            'stock_maximo' => fake()->numberBetween(50, 500),
            'unidad_medida' => fake()->randomElement(['PZA', 'CAJA', 'LT', 'KG']),
        ];
    }
}
