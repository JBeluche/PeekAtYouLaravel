<?php

namespace Database\Factories;

use App\Enums\EntrySymbol;
use App\Models\Calendar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Entry>
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
            'date' => fake()->dateTimeThisYear()->format('Y-m-d'),
            'text' => fake()->sentence(),
            'pen_color' => fake()->hexColor(),
            'symbol' => fake()->randomElement(EntrySymbol::cases()),
            'label_id' => null,
        ];
    }
}
