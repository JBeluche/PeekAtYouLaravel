<?php

namespace Database\Factories;

use App\Enums\DayMarkStyle;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'note_font_size' => fake()->numberBetween(10, 24),
            'note_font' => fake()->randomElement(['sans', 'serif', 'mono']),
            'day_mark_style' => fake()->randomElement(DayMarkStyle::cases()),
        ];
    }
}
