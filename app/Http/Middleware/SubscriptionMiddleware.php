<?php

namespace App\Http\Middleware;

use App\Services\Caching\AdvancedCacheService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    public function __construct(private AdvancedCacheService $cacheService) {}

    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->attributes->get('tenant');

        if (!$tenant) {
            // No tenant context, this might be a public route
            return $next($request);
        }

        // Check subscription status with caching
        $isSubscribed = $this->cacheService->getCachedSubscriptionStatus($tenant->id);
        
        if (is_null($isSubscribed)) {
            $isSubscribed = $tenant->isSubscribed() || $tenant->isOnTrial();
            $this->cacheService->cacheSubscriptionStatus($tenant->id, $isSubscribed);
        }

        if (!$isSubscribed) {
            // Hard enforcement - block all tenant features
            return response()->json([
                'message' => 'Subscription expired. Please renew your subscription to continue.',
                'status' => 'subscription_expired'
            ], 402);
        }

        return $next($request);
    }
}