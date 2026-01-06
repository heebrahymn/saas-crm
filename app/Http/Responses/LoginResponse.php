<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $user = Auth::user();

        // Check if user belongs to a company
        if ($user && $user->company) {
            $subdomain = $user->company->subdomain;

            // Redirect to the tenant dashboard
            // We use the route() helper with the tenant_subdomain parameter
            return redirect()->route('dashboard', ['tenant_subdomain' => $subdomain]);
        }

        // Fallback or error if no company assigned
        return redirect()->intended(config('fortify.home'));
    }
}
