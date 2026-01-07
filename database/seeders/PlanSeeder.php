<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Free',
            'stripe_price_id' => 'price_free',
            'stripe_product_id' => 'prod_free',
            'description' => 'Perfect for getting started',
            'features' => [
                'contacts_limit' => 100,
                'users_limit' => 1,
                'storage_limit' => 1,
                'api_calls_limit' => 1000,
                'support' => 'Email',
            ],
            'price' => 0,
            'currency' => 'usd',
            'interval' => 'month',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Plan::create([
            'name' => 'Pro',
            'stripe_price_id' => 'price_pro',
            'stripe_product_id' => 'prod_pro',
            'description' => 'Perfect for growing businesses',
            'features' => [
                'contacts_limit' => 1000,
                'users_limit' => 5,
                'storage_limit' => 10,
                'api_calls_limit' => 10000,
                'support' => 'Priority',
                'advanced_features' => true,
            ],
            'price' => 2900,
            'currency' => 'usd',
            'interval' => 'month',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Plan::create([
            'name' => 'Business',
            'stripe_price_id' => 'price_business',
            'stripe_product_id' => 'prod_business',
            'description' => 'Perfect for established businesses',
            'features' => [
                'contacts_limit' => 10000,
                'users_limit' => 20,
                'storage_limit' => 50,
                'api_calls_limit' => 50000,
                'support' => '24/7',
                'advanced_features' => true,
                'custom_reports' => true,
            ],
            'price' => 7900,
            'currency' => 'usd',
            'interval' => 'month',
            'is_active' => true,
            'sort_order' => 3,
        ]);
    }
}