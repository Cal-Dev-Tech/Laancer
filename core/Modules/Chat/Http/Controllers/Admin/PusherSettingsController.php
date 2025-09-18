<?php

namespace Modules\Chat\Http\Controllers\Admin;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PusherSettingsController extends Controller
{

    public function pusher_settings(Request $request)
    {
        
        if ($request->isMethod('post')) {
            $validationRules = [
                'BROADCAST_DRIVER' => 'required|in:pusher,reverb',
            ];
            
            // Add validation rules based on selected driver
            if ($request->BROADCAST_DRIVER === 'pusher') {
                $validationRules += [
                    'PUSHER_APP_ID' => 'required',
                    'PUSHER_APP_KEY' => 'required',
                    'PUSHER_APP_SECRET' => 'required',
                    'PUSHER_APP_CLUSTER' => 'required',
                ];
            } elseif ($request->BROADCAST_DRIVER === 'reverb') {
                $validationRules += [
                    'REVERB_APP_ID' => 'required',
                    'REVERB_APP_KEY' => 'required',
                    'REVERB_APP_SECRET' => 'required',
                    'REVERB_HOST' => 'required',
                    'REVERB_PORT' => 'required|numeric',
                    'REVERB_SCHEME' => 'required|in:http,https',
                ];
            }
            
            $validated = $request->validate($validationRules);
            
            setEnvValue($validated);
            
            toastr_success(__('Broadcasting Settings Updated Successfully.'));
            return back();
        }

        return view('chat::admin.pusher-settings');
    }

}
