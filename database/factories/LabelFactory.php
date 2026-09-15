<?php

namespace Database\Factories;

use App\Models\Calendar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Label>
 */
class LabelFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'calendar_id' => Calendar::factory(),
            'color' => fake()->unique()->hexColor(),
            'name' => fake()->word(),
        ];
    }
}
