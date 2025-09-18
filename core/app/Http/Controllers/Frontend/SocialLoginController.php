<?php

namespace App\Http\Controllers\Frontend;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    public function facebook_redirect(Request $request)
    {
        $userType = $request->input('user_type', 1);

        if (!in_array($userType, [1, 2])) {
            return redirect()->route('user.login')->with([
                'msg' => __('Invalid user type selected.'),
                'type' => 'danger'
            ]);
        }

        return Socialite::driver('facebook')->with(['state' => $userType])->redirect();
    }

    public function facebook_callback()
    {
        try {
            $user_fb_details = Socialite::driver('facebook')->stateless()->user();

            $user_details = User::where('email', $user_fb_details->getEmail())->first();

            if ($user_details) {
                Auth::login($user_details);
                if ($user_details->user_type == 1) {
                    return redirect()->intended('client/dashboard/info');
                } else {
                    return redirect()->intended('freelancer/dashboard/info');
                }
            } else {

                $fullName = trim($user_fb_details->getName());
                if (!empty($user_fb_details->user['first_name']) && !empty($user_fb_details->user['last_name'])) {
                    $firstName = $user_fb_details->user['first_name'];
                    $lastName  = $user_fb_details->user['last_name'];
                } else {
                    $nameParts = preg_split('/\s+/', $fullName, 2);
                    $firstName = $nameParts[0] ?? 'Unknown';
                    $lastName  = $nameParts[1] ?? $firstName;
                }
                $user_type = request('state', 1);

                $new_user = User::create([
                    'username' => 'fb_' . explode('@', $user_fb_details->getEmail())[0],
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $user_fb_details->getEmail(),
                    'user_type' => $user_type,
                    'is_email_verified' => 1,
                    'facebook_id' => $user_fb_details->getId(),
                    'password' => Hash::make(\Illuminate\Support\Str::random(8))
                ]);

                Auth::login($new_user);

                if ($user_type == 1) {
                    return redirect()->intended('client/dashboard/info');
                } else {
                    return redirect()->intended('freelancer/dashboard/info');
                }
            }
        } catch (\Exception $e) {
            Log::error("Facebook callback error", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->intended('login')->with([
                'msg' => $e->getMessage(),
                'type' => 'danger'
            ]);
        }
    }

    public function google_redirect(Request $request)
    {
        $userType = $request->input('user_type', 1);
        if (!in_array($userType, [1, 2])) {
            return redirect()->route('user.login')->with([
                'msg' => __('Invalid user type selected.'),
                'type' => 'danger'
            ]);
        }
        return Socialite::driver('google')->with(['state' => $userType])->redirect();
    }

    public function google_callback()
    {
        try {
            $user_go_details = Socialite::driver('google')->stateless()->user();
            $user_details = User::where('email', $user_go_details->getEmail())->first();

            if ($user_details) {
                Auth::login($user_details);
                if ($user_details->user_type == 1) {
                    return redirect()->intended('client/dashboard/info');
                } else {
                    return redirect()->intended('freelancer/dashboard/info');
                }
            } else {
                $fullName = trim($user_go_details->getName());
                $nameParts = preg_split('/\s+/', $fullName, 2);

                $firstName = $nameParts[0] ?? 'Unknown';
                $lastName  = $nameParts[1] ?? $firstName;
                $user_type = request('state', 1);
                $new_user = User::create([
                    'username' => 'go_' . explode('@', $user_go_details->getEmail())[0],
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $user_go_details->getEmail(),
                    'user_type' => $user_type,
                    'is_email_verified' => 1,
                    'google_id' => $user_go_details->getId(),
                    'password' => Hash::make(\Illuminate\Support\Str::random(8))
                ]);

                Auth::login($new_user);

                // Redirect based on user type
                if ($user_type == 1) {
                    return redirect()->intended('client/dashboard/info');
                } else {
                    return redirect()->intended('freelancer/dashboard/info');
                }
            }
        } catch (\Exception $e) {
            Log::error("Google callback error", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->intended('login')->with(['msg' => $e->getMessage(), 'type' => 'danger']);
        }
    }
}
