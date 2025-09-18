<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Subscription\Entities\Subscription;
use App\Http\Services\Backend\TransactionService;

class TransactionController extends Controller
{
    //global commission settings
    public function commission_settings(Request $request)
    {
        if($request->isMethod('post')){
            return (new TransactionService())->commission_settings($request);
        }
        $subscriptions = Subscription::with('subscription_type')->where('status', 1)->get();
        return view('backend.pages.transaction.admin-commission-settings', compact('subscriptions'));
    }

    // Update commission settings for a specific subscription
    public function updateSubscriptionCommission(Request $request, $subscriptionId)
    {
        $request->validate([
            'commission_type' => 'nullable|in:percentage,fixed',
            'commission_rate' => 'nullable|numeric|min:0|max:500'
        ]);

        $subscription = Subscription::findOrFail($subscriptionId);
        
        if (empty($request->commission_type) && empty($request->commission_rate)) {
            $subscription->update([
                'commission_type' => null,
                'commission_rate' => null
            ]);
            toastr_success(__('Subscription commission reset to global settings.'));
        }
        elseif (empty($request->commission_type) || empty($request->commission_rate)) {
            toastr_error(__('Both commission type and rate are required for custom settings.'));
        }
        else {
            $subscription->update([
                'commission_type' => $request->commission_type,
                'commission_rate' => $request->commission_rate
            ]);
            toastr_success(__('Subscription commission updated successfully.'));
        }

        return back();
    }

    //transaction fee settings
    public function transaction_fee_settings(Request $request)
    {
        if($request->isMethod('post')) {
            return (new TransactionService())->transaction_fee_settings($request);
        }
        return view('backend.pages.transaction.transaction-fee-settings');
    }

    //withdraw fee settings
    public function withdraw_fee_settings(Request $request)
    {
        if($request->isMethod('post')) {
            return (new TransactionService())->withdraw_fee_settings($request);
        }
        return view('backend.pages.transaction.withdraw-fee-settings');
    }



}
