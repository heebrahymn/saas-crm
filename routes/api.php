<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\OnboardingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Team\InvitationController;

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
    // Team management routes (admin only)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/team/invite', [InvitationController::class, 'invite']);
    });

    // Subscription protected routes
    Route::middleware(['subscribed'])->group(function () {
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