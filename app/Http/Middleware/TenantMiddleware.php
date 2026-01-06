<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Path-based tenancy: get subdomain from route parameter
        $subdomain = $request->route('tenant_subdomain');

        if (!$subdomain) {
            // No tenant param, move along (shouldn't happen if properly grouped)
            return $next($request);
        }

        // Get tenant from cache or database
        $tenant = Cache::remember("tenant:{$subdomain}", 300, function () use ($subdomain) {
            return Company::where('subdomain', $subdomain)->first();
        });

        if (!$tenant) {
            abort(404, 'Tenant not found');
        }

        // Set tenant on request for later use
        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('company_id', $tenant->id);


        // Set default route parameter for URL generation
        URL::defaults(['tenant_subdomain' => $subdomain]);

        // Set the tenant context globally
        app()->instance('current_tenant', $tenant);

        // Forget the parameter so it doesn't mess with controller signatures if not requested
        $request->route()->forgetParameter('tenant_subdomain');

        return $next($request);
    }
}
