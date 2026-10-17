<?php

namespace Database\Factories;

use App\Models\Moneda;
use App\Models\TasaCambio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TasaCambio>
 */
class TasaCambioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'moneda_id' => Moneda::factory(),
            'tasa' => fake()->randomFloat(4, 0.01, 100.0000),
            'fecha_efectiva' => fake()->dateTimeThisYear(),
        ];
    }
}
