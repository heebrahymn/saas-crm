<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\Caching\AdvancedCacheService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function __construct(private AdvancedCacheService $cacheService) {}

    public function handle(Request $request, Closure $next): Response
    {
        // Extract subdomain from host
        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host);
        
        if (!$subdomain) {
            // No subdomain means main domain (public pages like login, register)
            $request->attributes->set('tenant', null);
            return $next($request);
        }

        // Get tenant from cache or database
        $tenant = $this->cacheService->getCachedTenant($subdomain);
        
        if (!$tenant) {
            $tenant = Company::where('subdomain', $subdomain)->first();
            if ($tenant) {
                $this->cacheService->cacheTenantResolution($subdomain, $tenant);
            }
        }

        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        // Set tenant on request for later use
        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('company_id', $tenant->id);

        // Set the tenant context globally
        app()->instance('current_tenant', $tenant);

        return $next($request);
    }

    private function extractSubdomain(string $host): ?string
    {
        $suffix = config('app.tenant_subdomain_suffix', '.app.test');
        
        if (str_ends_with($host, $suffix)) {
            return substr($host, 0, -(strlen($suffix)));
        }

        return null;
    }
}