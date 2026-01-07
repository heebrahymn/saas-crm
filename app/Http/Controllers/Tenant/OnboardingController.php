<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class OnboardingController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'subdomain' => 'required|string|alpha_dash|max:50|unique:companies,subdomain',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Create company
        $company = Company::create([
            'name' => $validated['company_name'],
            'subdomain' => $validated['subdomain'],
            'email' => $validated['email'],
            'trial_ends_at' => now()->addDays(14), // 14-day free trial
        ]);

        // Create owner user
        $user = User::create([
            'company_id' => $company->id,
            'name' => $validated['company_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign admin role
        UserRole::create([
            'user_id' => $user->id,
            'company_id' => $company->id,
            'role' => 'admin',
        ]);

        // Login the user and return token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Company created successfully',
            'company' => $company,
            'user' => $user,
            'token' => $token,
            'subdomain' => $company->subdomain,
            'redirect_url' => $this->getTenantUrl($company->subdomain)
        ]);
    }

    public function verify($token)
    {
        // This would be for email verification if needed
        return response()->json(['message' => 'Verification logic would go here']);
    }

    private function getTenantUrl(string $subdomain): string
    {
        $suffix = config('app.tenant_subdomain_suffix', '.app.test');
        return "https://{$subdomain}{$suffix}";
    }
}