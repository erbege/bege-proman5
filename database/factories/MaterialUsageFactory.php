<?php

namespace Database\Factories;

use App\Models\MaterialUsage;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialUsageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MaterialUsage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'project_id' => Project::factory(),
            'usage_date' => $this->faker->date(),
            // 'usage_number' => MaterialUsage::generateNumber(), // Let model boot handle this
            'notes' => $this->faker->sentence,
            'created_by' => User::factory(),
        ];
    }
}
