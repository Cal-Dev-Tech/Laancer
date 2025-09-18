<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserIdentityVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // if identity verification setting is disabled just pass
        if (
            !empty(get_static_option('user_identity_verify_enable_disable')) &&
            get_static_option('user_identity_verify_enable_disable') == 'disable'
        ) {
            if (auth('web')->check()) {
                return $next($request);
            }
        } else {
            // identity verification is enabled
            if (auth('web')->check()) {
                $user = auth('web')->user();
                $user_type = $user->user_type == 1 ? 'client' : 'freelancer';

                if ($user->user_verified_status == 0) {
                    if ($user_type === 'client') {
                        return redirect()->route('client.identity.verification');
                    }

                    if ($user_type === 'freelancer') {
                        return redirect()->route('freelancer.identity.verification');
                    }
                    return redirect()->route('identity.verification');
                }
            }
        }

        return $next($request);
    }
}
