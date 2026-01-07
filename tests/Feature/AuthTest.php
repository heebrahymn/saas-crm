<?php

use App\Models\Company;
use App\Models\User;
use App\Models\UserRole;

test('user can register a new company', function () {
    $response = $this->postJson('/api/register', [
        'company_name' => 'Test Company',
        'subdomain' => 'test',
        'email' => 'owner@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Company created successfully',
    ]);

    // Verify company and user were created
    $this->assertDatabaseHas('companies', [
        'name' => 'Test Company',
        'subdomain' => 'test',
    ]);

    $this->assertDatabaseHas('users', [
        'email' => 'owner@test.com',
    ]);

    $this->assertDatabaseHas('user_roles', [
        'role' => 'admin',
    ]);
});

test('user can login with valid credentials', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Login successful',
    ]);
});

test('user cannot login with invalid credentials', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'admin',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401);
    $response->assertJson([
        'message' => 'Invalid credentials',
    ]);
});

test('admin can invite team members', function () {
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

    $token = $admin->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('https://test.app.test/api/team/invite', [
            'email' => 'newmember@test.com',
            'role' => 'staff',
        ]);

    $response->assertStatus(200);
    $response->assertJson([
        'message' => 'Invitation sent successfully',
    ]);

    $this->assertDatabaseHas('invitations', [
        'email' => 'newmember@test.com',
        'role' => 'staff',
    ]);
});

test('non-admin cannot invite team members', function () {
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
        ->postJson('https://test.app.test/api/team/invite', [
            'email' => 'newmember@test.com',
            'role' => 'staff',
        ]);

    $response->assertStatus(403);
});