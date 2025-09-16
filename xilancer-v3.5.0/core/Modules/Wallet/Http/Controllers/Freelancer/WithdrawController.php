<?php

namespace Modules\Wallet\Http\Controllers\Freelancer;

use App\Helper\LogActivity;
use App\Models\FreelancerNotification;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\CurrencySwitcher\App\Models\SelectedCurrencyList;
use Modules\StripeConnect\App\Models\StripePayout;
use Modules\Wallet\Entities\Wallet;
use Modules\Wallet\Entities\WithdrawRequest;
use Modules\Wallet\Http\Requests\FreelancerHandleWithdrawRequest;

class WithdrawController extends Controller
{
    public function withdraw_request(FreelancerHandleWithdrawRequest $request)
    {
        $data = $request->validated();

        $wallet = Wallet::where("user_id", $data["user_id"])->first();

        $withdraw_fee = 0;
        if(get_static_option('withdraw_fee_type') == 'percentage'){
            $withdraw_fee = ($data["amount"]*get_static_option('withdraw_fee'))/100;
        }else{
            $withdraw_fee = get_static_option('withdraw_fee');
        }

        if(moduleExists('CurrencySwitcher')){
            $get_user_currency = SelectedCurrencyList::where('currency', get_currency_according_to_user())->first() ?? null;
            $amount = ($data["amount"]+$withdraw_fee)/$get_user_currency->conversion_rate;
            $withdraw_fee = $withdraw_fee/$get_user_currency->conversion_rate;
            if($amount < get_static_option('minimum_withdraw_amount') || $amount > get_static_option('maximum_withdraw_amount')){
                return back()->with(toastr_warning(__("Please enter a valid amount between ".float_amount_with_currency_symbol(get_static_option('minimum_withdraw_amount')). '-' .float_amount_with_currency_symbol(get_static_option('maximum_withdraw_amount')))));
            }
            if($wallet->balance >= $amount){
                $withdraw = WithdrawRequest::create($data);
                Wallet::where('user_id',$withdraw->user_id)->update([
                    'balance'=> $wallet->balance - $amount,
                    'remaining_balance'=> $wallet->balance - $amount,
                    'withdraw_amount'=> $wallet->withdraw_amount + $amount
                ]);

                //security manage
                if(moduleExists('SecurityManage')){
                    LogActivity::addToLog('Withdraw request','Freelancer');
                }

                WithdrawRequest::where('id', $withdraw->id)->update(['amount'=> ($amount-$withdraw_fee)]);
                notificationToAdmin($withdraw->id, $withdraw->user_id, 'Withdraw', 'New withdraw request');
                return back()->with(toastr_success(__("Successfully sent your request")));
            }
        }else{
            $amount = $data["amount"]+$withdraw_fee;
            if($amount < get_static_option('minimum_withdraw_amount') || $amount > get_static_option('maximum_withdraw_amount')){
                return back()->with(toastr_warning(__("Please enter a valid amount between ".float_amount_with_currency_symbol(get_static_option('minimum_withdraw_amount')). '-' .float_amount_with_currency_symbol(get_static_option('maximum_withdraw_amount')))));
            }
            if($wallet->balance >= $data["amount"]){
                $withdraw = WithdrawRequest::create($data);
                Wallet::where('user_id',$withdraw->user_id)->update([
                    'balance'=> $wallet->balance - $amount,
                    'remaining_balance'=> $wallet->balance - $amount,
                    'withdraw_amount'=> $wallet->withdraw_amount + $amount
                ]);

                //security manage
                if(moduleExists('SecurityManage')){
                    LogActivity::addToLog('Withdraw request','Freelancer');
                }

                notificationToAdmin($withdraw->id, $withdraw->user_id, 'Withdraw', 'New withdraw request');
                return back()->with(toastr_success(__("Successfully sent your request")));
            }
        }

        return back()->with(toastr_warning('Your requested amount is greater than your wallet balance'));
    }

    public function withdraw_history()
    {
        if(moduleExists('StripeConnect')){
            $all_request = StripePayout::where('user_id',auth()->user()->id)->latest()->paginate(10);
            FreelancerNotification::where('freelancer_id',Auth::guard('web')->user()->id)
                ->where('is_read','unread')
                ->where('type','Withdraw')
                ->update(['is_read' => 'read']);
            // dd($all_request);
            return view('wallet::freelancer.withdraw.requests',compact('all_request'));
        }
        $all_request  = WithdrawRequest::where('user_id',auth()->user()->id)->latest()->paginate(10);
        FreelancerNotification::where('freelancer_id',Auth::guard('web')->user()->id)
            ->where('is_read','unread')
            ->where('type','Withdraw')
            ->update(['is_read' => 'read']);
        return view('wallet::freelancer.withdraw.requests',compact('all_request'));
    }


    // pagination
    function pagination(Request $request)
    {
        if($request->ajax()){
            $all_request = WithdrawRequest::where('user_id',auth()->user()->id)->latest()->paginate(10);
            return view('wallet::freelancer.withdraw.search-result', compact('all_request'))->render();
        }
    }
}
