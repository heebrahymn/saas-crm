<?php

namespace App\Services\Caching;

use Illuminate\Support\Facades\Cache;

class AdvancedCacheService
{
    public function cacheTenantResolution(string $subdomain, $tenant, int $ttl = 300): void
    {
        Cache::put("tenant:{$subdomain}", $tenant, $ttl);
    }

    public function getCachedTenant(string $subdomain)
    {
        return Cache::get("tenant:{$subdomain}");
    }

    public function cacheSubscriptionStatus(int $companyId, bool $status, int $ttl = 60): void
    {
        Cache::put("tenant:{$companyId}:subscribed", $status, $ttl);
    }

    public function getCachedSubscriptionStatus(int $companyId): ?bool
    {
        return Cache::get("tenant:{$companyId}:subscribed");
    }

    public function cacheUserPermissions(int $userId, array $permissions, int $ttl = 900): void
    {
        Cache::put("user:{$userId}:permissions", $permissions, $ttl);
    }

    public function getCachedUserPermissions(int $userId): ?array
    {
        return Cache::get("user:{$userId}:permissions");
    }

    public function cacheCRMData(string $cacheKey, $data, int $ttl = 300): void
    {
        Cache::put($cacheKey, $data, $ttl);
    }

    public function getCachedCRMData(string $cacheKey)
    {
        return Cache::get($cacheKey);
    }

    public function invalidateTenantCache(string $subdomain, int $companyId): void
    {
        // Clear all tenant-related caches
        Cache::forget("tenant:{$subdomain}");
        Cache::forget("tenant:{$companyId}:subscribed");
        
        // Clear all user permissions for this tenant
        // This would require knowing all user IDs in the company
        // For now, we'll just log this for manual cleanup
        \Log::info('Tenant cache invalidated', [
            'subdomain' => $subdomain,
            'company_id' => $companyId,
        ]);
    }

    public function bulkInvalidateCaches(array $keys): void
    {
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    public function getCacheStats(): array
    {
        // Note: This is a simplified version. Actual cache stats depend on the driver used.
        return [
            'driver' => config('cache.default'),
            'prefix' => config('cache.prefix'),
        ];
    }
}