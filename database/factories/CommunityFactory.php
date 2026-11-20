<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommunityFactory extends Factory
{
    protected $model = Community::class;

    public function definition(): array
    {
        $households = $this->faker->numberBetween(35, 250);
        return [
            'ward_id' => Ward::factory(),
            'name' => $this->faker->citySuffix() . ' Community',
            'description' => $this->faker->sentence(),
            'latitude' => $this->faker->randomFloat(6, 7.10, 7.90),
            'longitude' => $this->faker->randomFloat(6, 6.05, 6.80),
            'estimated_population' => $households * 6,
            'estimated_households' => $households,
            'electricity_status_enum' => $this->faker->randomElement(['no_electricity', 'unreliable', 'inadequate', 'reliable']),
            'is_verified' => true,
        ];
    }
}
