<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CanContactFreelancer;

class CanContactFreelancerController extends Controller
{
    public function checkCanContactFreelancer(Request $request)
    {
        if ($request->isMethod('post')) {
    
            $status = $request->check_contact_permission;
            $record=CanContactFreelancer::first();
            if($record)
            {
                $record->can_contact_freelancer=$status;
                $record->save();
            }
            else
            {
                CanContactFreelancer::create([
                    'can_contact_freelancer'=>$status,
                ]);
            }
            
            return back()->with(toastr_success(__('Change Successfully')));
        }
        $record=CanContactFreelancer::first();
        return view("backend.pages.user_messaging_access.setting",compact('record'));
        
    }

    public function showContactMeButtonBeforeLogin(Request $request)
    {
        if ($request->isMethod('post')) {
    
            $status = $request->show_button_before_login;
            $record=CanContactFreelancer::first();
            if($record)
            {
                $record->show_contact_me_before_login=$status;
                $record->save();
            }
            else
            {
                CanContactFreelancer::create([
                    'show_contact_me_before_login'=>$status,
                ]);
            }
            
            return back()->with(toastr_success(__('Change Successfully')));
        }
        
    }

}
