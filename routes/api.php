<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\OnboardingController;
use App\Http\Controllers\AuthController;

// Public routes (no tenant context)
Route::post('/register', [OnboardingController::class, 'register']);
Route::get('/verify/{token}', [OnboardingController::class, 'verify']);
Route::post('/login', [AuthController::class, 'login']);

// Tenant-specific routes (require tenant context)
Route::middleware(['tenant'])->group(function () {
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