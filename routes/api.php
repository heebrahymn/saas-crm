<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\OnboardingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Team\InvitationController;
use App\Http\Controllers\User\UserManagementController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Tenant\CompanyController;

// Public routes (no tenant context)
Route::post('/register', [OnboardingController::class, 'register']);
Route::get('/verify/{token}', [OnboardingController::class, 'verify']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes (require token but no tenant context)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Team invitation routes (no tenant context but token required)
Route::get('/invitations/{token}', [InvitationController::class, 'accept']);
Route::post('/invitations/{token}', [InvitationController::class, 'complete']);

// Tenant-specific routes (require tenant context)
Route::middleware(['tenant'])->group(function () {
    // Profile management (no subscription required)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
        Route::put('/profile/settings', [ProfileController::class, 'updateSettings']);
        
        // Company information
        Route::get('/company', [CompanyController::class, 'show']);
        Route::put('/company', [CompanyController::class, 'update']);
        Route::get('/company/settings', [CompanyController::class, 'getSettings']);
        Route::put('/company/settings', [CompanyController::class, 'updateSettings']);
        
        // Team management routes (admin only)
        Route::post('/team/invite', [InvitationController::class, 'invite']);
        
        // User management (admin/manager only)
        Route::middleware(['subscribed'])->group(function () {
            Route::apiResource('users', UserManagementController::class)->except(['store', 'create']);
            Route::put('/users/{user}/role', [UserManagementController::class, 'updateRole']);
            Route::put('/users/{user}/deactivate', [UserManagementController::class, 'deactivate']);
            Route::put('/users/{user}/activate', [UserManagementController::class, 'activate']);
        });
    });

    // Subscription protected routes
    Route::middleware(['auth:sanctum', 'subscribed'])->group(function () {
        // CRM routes will go here
        Route::get('/dashboard', function () {
            return response()->json(['message' => 'Tenant dashboard']);
        });
    });
    
    // Routes that don't require subscription (like subscription management)
    Route::prefix('/billing')->group(function () {
        // Billing routes
    });
});