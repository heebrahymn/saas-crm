<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Free', 'Pro', 'Business', 'Enterprise']),
            'stripe_price_id' => 'price_' . $this->faker->uuid(),
            'stripe_product_id' => 'prod_' . $this->faker->uuid(),
            'description' => $this->faker->sentence(),
            'features' => [
                'contacts_limit' => $this->faker->numberBetween(100, 10000),
                'users_limit' => $this->faker->numberBetween(1, 50),
                'storage_limit' => $this->faker->numberBetween(1, 100),
                'api_calls_limit' => $this->faker->numberBetween(1000, 100000),
            ],
            'price' => $this->faker->randomElement([0, 2900, 7900, 14900]), // in cents
            'currency' => 'usd',
            'interval' => $this->faker->randomElement(['month', 'year']),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}