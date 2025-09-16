<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProjectAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if project functionality is globally disabled
        if (get_static_option('project_enable_disable') == 'disable') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Project functionality is currently disabled.',
                ], 403);
            }

            toastr_warning(__('Project functionality is currently disabled.'));

            return redirect()->route('freelancer.dashboard');
        }

        // Check if SecurityManage module exists and user access
        if (moduleExists('SecurityManage')) {
            $user = Auth::guard('web')->user();

            if ($user && $user->freeze_project == 'freeze') {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Your project access has been frozen. Please contact support.',
                    ], 403);
                }

                toastr_warning(__('Your project access has been frozen. Please contact support.'));

                return redirect()->route('freelancer.dashboard');
            }
        }

        return $next($request);
    }
}
