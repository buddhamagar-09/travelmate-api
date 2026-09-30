<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\Trekhighlight;
use Illuminate\Database\Eloquent\Factories\Factory;

class TrekHighlightFactory extends Factory
{
    protected $model = Trekhighlight::class;

    public function definition(): array
    {
        return [
            'package_id' => Package::factory(),
            'highlight' => fake()->sentence(4),
        ];
    }
}
