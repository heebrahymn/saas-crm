<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Tenant\OnboardingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Team\InvitationController;
use App\Http\Controllers\User\UserManagementController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\Tenant\CompanyController;
use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Billing\StripeWebhookController;
use App\Http\Controllers\CRM\ContactController;
use App\Http\Controllers\CRM\LeadController;
use App\Http\Controllers\CRM\DealController;
use App\Http\Controllers\CRM\TaskController;

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
            Route::put('/users/{user}/activate', [UserManagementController::class, 'activate');
        });
    });

    // Billing routes (no subscription required for these)
    Route::prefix('/billing')->group(function () {
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/plans', [BillingController::class, 'plans']);
            Route::post('/subscribe', [BillingController::class, 'subscribe']);
            Route::post('/unsubscribe', [BillingController::class, 'unsubscribe']);
            Route::post('/change-plan', [BillingController::class, 'changePlan']);
            Route::get('/subscription', [BillingController::class, 'currentSubscription']);
            Route::get('/invoices', [BillingController::class, 'invoices']);
            Route::post('/sync-status', [BillingController::class, 'syncStatus']);
        });
    });

    // CRM routes (subscription required)
    Route::middleware(['auth:sanctum', 'subscribed'])->group(function () {
        Route::apiResource('contacts', ContactController::class);
        Route::apiResource('leads', LeadController::class);
        Route::apiResource('deals', DealController::class);
        Route::apiResource('tasks', TaskController::class);

        // Lead-specific routes
        Route::post('/leads/{lead}/convert', [LeadController::class, 'convertToDeal']);

        // Deal-specific routes
        Route::post('/deals/{deal}/close', [DealController::class, 'close']);

        // Task-specific routes
        Route::post('/tasks/{task}/complete', [TaskController::class, 'markComplete']);
        Route::post('/tasks/{task}/incomplete', [TaskController::class, 'markIncomplete']);

        
        /// Replace the simple dashboard route with a controller
Route::get('/dashboard', [DashboardController::class, 'index']);

    });
});

// Stripe webhook (public endpoint)
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handleWebhook']);
