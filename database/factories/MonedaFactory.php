<?php

namespace Database\Factories;

use App\Models\Moneda;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Moneda>
 */
class MonedaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => strtoupper(fake()->unique()->lexify('???')),
            'nombre' => fake()->words(2, true),
            'simbolo' => fake()->randomElement(['$', '€', 'Bs.', 'COL $']),
            'es_moneda_base' => false,
        ];
    }
}
