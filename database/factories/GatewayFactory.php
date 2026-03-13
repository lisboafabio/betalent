<?php

namespace Database\Factories;

use App\Models\Gateway;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Gateway>
 */
class GatewayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Gateway '.$this->faker->unique()->numberBetween(1, 100),
            'is_active' => true,
            'priority' => $this->faker->numberBetween(1, 10),
        ];
    }
}
