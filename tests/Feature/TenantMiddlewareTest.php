<?php

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

test('tenant middleware resolves tenant from subdomain', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    
    $response = $this->get('https://test.app.test/api/health');
    
    $response->assertStatus(200);
});

test('tenant middleware caches resolved tenant', function () {
    $company = Company::factory()->create(['subdomain' => 'cached']);
    
    // First request - should hit database
    $this->get('https://cached.app.test/api/health');
    
    // Second request - should hit cache
    $this->get('https://cached.app.test/api/health');
    
    $cachedTenant = Cache::get("tenant:cached");
    expect($cachedTenant)->toBeInstanceOf(Company::class);
});

test('tenant middleware returns 404 for non-existent tenant', function () {
    $response = $this->get('https://nonexistent.app.test/api/health');
    
    $response->assertStatus(404);
});

test('subscription middleware blocks requests for expired subscriptions', function () {
    $company = Company::factory()->create([
        'subdomain' => 'expired',
        'subscribed_until' => now()->subDays(1),
    ]);
    
    $response = $this->get('https://expired.app.test/api/protected');
    
    $response->assertStatus(402);
    $response->assertJson([
        'message' => 'Subscription expired. Please renew your subscription to continue.',
        'status' => 'subscription_expired'
    ]);
});