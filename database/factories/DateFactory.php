<?php

namespace Database\Factories;

use App\Models\Calendar;
use App\Models\Color;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Date>
 */
class DateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'info' => $this->faker->text(),
            'date' => $this->faker->date(),
            'calendar_id' => Calendar::all()->random()->id, 
            'color_id' => Color::all()->random()->id, 
        ];
    }
}
