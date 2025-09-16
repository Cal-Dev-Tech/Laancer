<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UserEmailVerify
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
        if(!empty(get_static_option('user_email_verify_settings_enable_disable')) && get_static_option('user_email_verify_settings_enable_disable') =='disable'){
            if (auth('web')->check()){
                return $next($request);
            }
        }else{
            if (auth('web')->check() && auth('web')->user()->is_email_verified == 0){
                return redirect()->route('email.verify');
            }
        }

        return $next($request);
    }
}
