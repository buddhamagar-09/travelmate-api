<?php

namespace Database\Factories;

use App\Models\Package;
use Illuminate\Database\Eloquent\Factories\Factory;

class PackageFactory extends Factory
{
    protected $model = Package::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug,
            'location' => fake()->city,
            'duration' => fake()->numberBetween(1, 15) . ' days',
            'price' => fake()->randomFloat(2, 100, 5000),
            'group_size' => fake()->numberBetween(2, 20) . ' people',
            'max_altitude' => fake()->numberBetween(1000, 8000) . 'm',
            'difficulty' => fake()->randomElement(['Easy', 'Moderate', 'Hard', 'Extreme']),
            'best_season' => fake()->randomElement(['Spring', 'Summer', 'Autumn', 'Winter']),
            'short_description' => fake()->sentence,
            'long_description' => fake()->paragraph,
            'featured_image' => 'packages/default.jpg',
            'is_featured' => fake()->boolean,
            'status' => 'active',
        ];
    }
}
