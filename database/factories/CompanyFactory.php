<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'subdomain' => fake()->unique()->slug(2),
            'email' => fake()->unique()->safeEmail(),
            'trial_ends_at' => now()->addDays(14),
        ];
    }
}