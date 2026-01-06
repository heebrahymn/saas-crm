<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define the roles to assign to the 3 users for each tenant
        $roles = ['admin', 'manager', 'staff'];

        // Create 2 tenants (Companies)
        Company::factory(2)->create()->each(function (Company $company) use ($roles) {
            // Create 3 users for each company
            foreach ($roles as $index => $role) {
                $user = User::factory()->create([
                    'company_id' => $company->id,
                    'name' => "{$company->name} " . ucfirst($role),
                    'email' => strtolower(str_replace(' ', '.', $company->name)) . ".{$role}@example.com",
                    'password' => Hash::make('password'),
                ]);

                // Record the role in user_roles table
                UserRole::create([
                    'user_id' => $user->id,
                    'company_id' => $company->id,
                    'role' => $role,
                ]);
            }
        });
    }
}
