<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Gateway;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'gateway_id' => Gateway::factory(),
            'external_id' => 'trans-'.$this->faker->unique()->numberBetween(100, 999),
            'status' => 'paid',
            'amount' => $this->faker->numberBetween(1000, 5000),
            'card_last_numbers' => $this->faker->numberBetween(1000, 9999),
        ];
    }
}
