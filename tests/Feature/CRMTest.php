<?php

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use App\Models\UserRole;

test('users can create contacts', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'staff',
    ]);

    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('https://test.app.test/api/contacts', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

    $response->assertStatus(201);
    $response->assertJson([
        'message' => 'Contact created successfully',
    ]);

    $this->assertDatabaseHas('contacts', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'company_id' => $company->id,
    ]);
});

test('users can create leads', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'staff',
    ]);

    $contact = Contact::create([
        'company_id' => $company->id,
        'first_name' => 'Test',
        'last_name' => 'Contact',
        'email' => 'test@example.com',
    ]);

    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('https://test.app.test/api/leads', [
            'contact_id' => $contact->id,
            'title' => 'Test Lead',
            'description' => 'Test lead description',
        ]);

    $response->assertStatus(201);
    $response->assertJson([
        'message' => 'Lead created successfully',
    ]);

    $this->assertDatabaseHas('leads', [
        'title' => 'Test Lead',
        'contact_id' => $contact->id,
        'company_id' => $company->id,
    ]);
});

test('users can create deals', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'manager',
    ]);

    $contact = Contact::create([
        'company_id' => $company->id,
        'first_name' => 'Test',
        'last_name' => 'Contact',
        'email' => 'test@example.com',
    ]);

    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('https://test.app.test/api/deals', [
            'contact_id' => $contact->id,
            'title' => 'Test Deal',
            'value' => 1000,
        ]);

    $response->assertStatus(201);
    $response->assertJson([
        'message' => 'Deal created successfully',
    ]);

    $this->assertDatabaseHas('deals', [
        'title' => 'Test Deal',
        'contact_id' => $contact->id,
        'value' => 1000,
        'company_id' => $company->id,
    ]);
});

test('users can create tasks', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'staff',
    ]);

    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('https://test.app.test/api/tasks', [
            'title' => 'Test Task',
            'assigned_to' => $user->id,
        ]);

    $response->assertStatus(201);
    $response->assertJson([
        'message' => 'Task created successfully',
    ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Test Task',
        'assigned_to' => $user->id,
        'company_id' => $company->id,
    ]);
});

test('users can view dashboard stats', function () {
    $company = Company::factory()->create(['subdomain' => 'test']);
    $user = User::factory()->create([
        'company_id' => $company->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user->id,
        'company_id' => $company->id,
        'role' => 'staff',
    ]);

    // Create some test data
    Contact::factory()->count(3)->create(['company_id' => $company->id]);
    Lead::factory()->count(2)->create(['company_id' => $company->id]);
    Deal::factory()->count(1)->create(['company_id' => $company->id]);
    Task::factory()->count(5)->create(['company_id' => $company->id]);

    $token = $user->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('https://test.app.test/api/dashboard');

    $response->assertStatus(200);
    $response->assertJson([
        'stats' => [
            'contacts' => 3,
            'leads' => 2,
            'deals' => 1,
            'tasks' => 5,
        ]
    ]);
});

test('policies prevent cross-tenant access to CRM data', function () {
    $company1 = Company::factory()->create(['subdomain' => 'test1']);
    $company2 = Company::factory()->create(['subdomain' => 'test2']);
    
    $user1 = User::factory()->create([
        'company_id' => $company1->id,
        'password' => bcrypt('password123'),
    ]);

    UserRole::create([
        'user_id' => $user1->id,
        'company_id' => $company1->id,
        'role' => 'staff',
    ]);

    $contact2 = Contact::create([
        'company_id' => $company2->id,
        'first_name' => 'Test',
        'last_name' => 'Contact',
        'email' => 'test@example.com',
    ]);

    $token = $user1->createToken('test')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson("https://test1.app.test/api/contacts/{$contact2->id}");

    $response->assertStatus(404); // Should not find the contact due to tenant isolation
});