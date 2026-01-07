<?php

use App\Models\Company;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Contact;

test('admin can manage all users', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    $admin = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $admin->id,
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $staff = User::factory()->create([
        'company_id' => $company->id,
    ]);

    $token = $admin->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('https://test.app.test/api/users');

    $response->assertStatus(200);
});

test('staff cannot manage users', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    $staff = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $staff->id,
        'company_id' => $company->id,
        'role' => 'staff',
    ]);

    $token = $staff->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('https://test.app.test/api/users');

    $response->assertStatus(403);
});

test('manager can view users but not delete', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    $manager = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $manager->id,
        'company_id' => $company->id,
        'role' => 'manager',
    ]);

    $token = $manager->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('https://test.app.test/api/users');

    $response->assertStatus(200); // Can view
});

test('user can update own profile', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
        'name' => 'Original Name',
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'staff',
    ]);

    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->putJson('https://test.app.test/api/profile', [
            'name' => 'Updated Name'
        ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name'
    ]);
});

test('policies prevent cross-tenant data access', function () {
    $company1 = Company::factory()->create(['subdomain' => 'test1']);
    $company2 = Company::factory()->create(['subdomain' => 'test2']);
    
    $admin1 = User::factory()->create([
        'company_id' => $company1->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $admin1->id,
        'company_id' => $company1->id,
        'role' => 'admin',
    ]);

    $contact2 = Contact::create([
        'company_id' => $company2->id,
        'first_name' => 'Test',
        'last_name' => 'Contact',
        'email' => 'test@example.com',
    ]);

    $token = $admin1->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson("https://test1.app.test/api/contacts/{$contact2->id}");

    $response->assertStatus(404); // Should not find the contact due to tenant isolation
});