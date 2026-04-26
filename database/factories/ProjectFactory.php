<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
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
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'client_name' => $this->faker->company(),
            'type' => $this->faker->randomElement(['construction', 'architecture', 'interior', 'exterior']),
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => $this->faker->dateTimeBetween('+2 months', '+1 year'),
            'contract_value' => $this->faker->randomFloat(2, 100000000, 5000000000),
            'status' => 'active',
            'location' => $this->faker->address(),
            'notes' => $this->faker->text(),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
