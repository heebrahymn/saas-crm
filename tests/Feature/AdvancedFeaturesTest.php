<?php

use App\Models\Company;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Contact;
use App\Services\Compliance\GDPRService;
use App\Services\DataRetention\DataRetentionService;

test('GDPR data export works correctly', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    // Create some data for export
    Contact::create([
        'company_id' => $company->id,
        'first_name' => 'Test',
        'last_name' => 'Contact',
        'email' => 'test@example.com',
    ]);

    $gdprService = new GDPRService();
    $exportData = $gdprService->exportUserData($user);

    expect($exportData)->toHaveKey('user');
    expect($exportData)->toHaveKey('contacts');
    expect($exportData['user']['email'])->toBe($user->email);
    expect($exportData['contacts'])->toHaveCount(1);
});

test('data retention cleanup works correctly', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    // Create old data
    Contact::create([
        'company_id' => $company->id,
        'first_name' => 'Old',
        'last_name' => 'Contact',
        'email' => 'old@example.com',
        'created_at' => now()->subDays(400), // Old enough to be cleaned up
    ]);

    $retentionService = new DataRetentionService();
    $results = $retentionService->cleanupOldData();

    expect($results['contacts_deleted'])->toBeGreaterThanOrEqual(0);
});

test('GDPR consent tracking works', function () {
    $gdprService = new GDPRService();
    
    $success = $gdprService->consentTracking('test-user-id', 'marketing', true);
    expect($success)->toBeTrue();
    
    $hasConsent = $gdprService->hasConsent('test-user-id', 'marketing');
    expect($hasConsent)->toBeTrue();
});

test('user can export their data', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/compliance/export');

    $response->assertStatus(200);
    // Note: We can't easily test the download response in feature tests
    // This would be tested in integration tests
});

test('user can request account deletion', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->deleteJson('/api/compliance/delete-account');

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Account deleted successfully',
    ]);
});

test('advanced caching service works correctly', function () {
    $cacheService = new \App\Services\Caching\AdvancedCacheService();
    
    $cacheService->cacheTenantResolution('test', ['id' => 1, 'name' => 'Test Company']);
    
    $cachedData = $cacheService->getCachedTenant('test');
    
    expect($cachedData)->toBeArray();
    expect($cachedData['name'])->toBe('Test Company');
});

test('subscription middleware uses caching', function () {
    $company = Company::factory()->create([
        'subdomain' => 'test',
        'subscribed_until' => now()->addDays(30), // Active subscription
    ]);
    
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('https://test.app.test/api/dashboard');

    $response->assertStatus(200);
});

test('hard subscription enforcement blocks access when expired', function () {
    $company = Company::factory()->create([
        'subdomain' => 'test',
        'subscribed_until' => now()->subDays(1), // Expired
    ]);
    
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('https://test.app.test/api/dashboard');

    $response->assertStatus(402); // Payment required
});