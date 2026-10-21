<?php

namespace Database\Factories;

use App\Enums\EntrySymbol;
use App\Models\Calendar;
use App\Models\Entry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entry>
 */
class EntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'calendar_id' => Calendar::factory(),
            // Unique, because a calendar holds at most one entry per day.
            'date' => fake()->unique()->date(),
            'text' => fake()->sentence(),
            'pen_color' => fake()->hexColor(),
            'symbol' => fake()->randomElement(EntrySymbol::cases()),
            'mark_color' => null,
        ];
    }
}
