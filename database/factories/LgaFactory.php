<?php

namespace Database\Factories;

use App\Models\Lga;
use Illuminate\Database\Eloquent\Factories\Factory;

class LgaFactory extends Factory
{
    protected $model = Lga::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->city(),
            'code' => 'KOG-' . strtoupper($this->faker->unique()->lexify('???')),
        ];
    }
}
