<?php

namespace Database\Factories;

use App\Models\ScamCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ScamCategory>
 */
class ScamCategoryFactory extends Factory
{
    protected $model = ScamCategory::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        return [
            'name' => ucwords($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
        ];
    }
}
