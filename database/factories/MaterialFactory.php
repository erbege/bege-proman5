<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Material>
 */
class MaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->regexify('[A-Z]{3}-[0-9]{3}'),
            'name' => $this->faker->word() . ' ' . $this->faker->word(),
            'category' => $this->faker->word(),
            'unit' => $this->faker->randomElement(['kg', 'm3', 'pcs', 'ton']),
            'unit_price' => $this->faker->randomFloat(2, 1000, 100000),
            'min_stock' => $this->faker->numberBetween(10, 100),
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
