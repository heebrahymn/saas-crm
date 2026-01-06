<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $request->attributes->get('tenant');

        if (!$tenant) {
            // No tenant context, this might be a public route
            return $next($request);
        }

        // Check subscription status with caching
        $isSubscribed = Cache::remember(
            "tenant:{$tenant->id}:subscribed", 
            60, // Cache for 1 minute
            function () use ($tenant) {
                return $tenant->isSubscribed() || $tenant->isOnTrial();
            }
        );

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