<?php

namespace Database\Factories;

use App\Models\Rating;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rating>
 */
class RatingFactory extends Factory
{
    protected $model = Rating::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'report_id' => Report::inRandomOrder()->first()?->id ?? Report::factory(),
            'score' => $this->faker->numberBetween(1, 10),
        ];
    }
}
