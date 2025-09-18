<?php

namespace Modules\Wallet\Http\Controllers\Freelancer;

use App\Events\AdminEvent;
use App\Helper\PaymentGatewayRequestHelper;
use App\Mail\BasicMail;
use App\Models\AdminNotification;
use App\Models\FreelancerNotification;
use App\Models\UserEarning;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Modules\CurrencySwitcher\App\Models\SelectedCurrencyList;
use Modules\Wallet\Entities\Wallet;
use Modules\Wallet\Entities\WalletHistory;
use Modules\Wallet\Entities\WithdrawGateway;
use Session;

class WalletController extends Controller
{
    private const CANCEL_ROUTE = 'freelancer.wallet.deposit.payment.cancel.static';

    public function deposit_payment_cancel_static()
    {
        return view('wallet::freelancer.wallet.cancel');
    }
    //display wallet history
    public function wallet_history(Request $request)
    {
        $user_id = Auth::guard('web')->user()->id;

        // Filter by type
        $type = $request->get('type');

        $all_histories = WalletHistory::where('user_id', $user_id)
            ->when($type, function($query, $type){
                $query->where('type', $type);
            })
            ->latest()
            ->paginate(10);

        $wallet = Wallet::where('user_id', $user_id)->first();
        $total_wallet_balance = $wallet->balance ?? 0;

        $total_deposit = WalletHistory::where('user_id', $user_id)
            ->where('type', 'deposit')
            ->sum('amount');

        $total_earning = WalletHistory::where('user_id', $user_id)
            ->where('type', 'earning')->sum('amount');
        $total_earning = $total_earning + $total_wallet_balance - $total_deposit;

        $withdraw_gateways = WithdrawGateway::where('status',1)->get();

        if(!$request->ajax()) {
            if ($request->has('mark_as_read') && $request->mark_as_read == 'true') {
                FreelancerNotification::where('freelancer_id', $user_id)
                    ->where('is_read', 'unread')
                    ->where('type', 'Deposit')
                    ->update(['is_read' => 'read']);
            }
        }

        return view('wallet::freelancer.wallet.wallet-history', compact([
            'all_histories',
            'total_wallet_balance',
            'total_earning',
            'total_deposit',
            'withdraw_gateways'
        ]));
    }

    // pagination
    function pagination(Request $request)
    {
        if($request->ajax()){
            $user_id = Auth::guard('web')->user()->id;
            $all_histories = WalletHistory::where('user_id',$user_id)->latest()->paginate(10);
            return view('wallet::freelancer.wallet.search-result', compact('all_histories'))->render();
        }
    }

    // search history
    public function search_history(Request $request)
    {
        $all_histories = WalletHistory::where('user_id',Auth::guard('web')->user()->id)->where('created_at', 'LIKE', "%". strip_tags($request->string_search) ."%")->paginate(10);
        return $all_histories->total() >= 1
            ? view('wallet::freelancer.wallet.search-result', compact('all_histories'))->render()
            : response()->json(['status'=>__('nothing')]);
    }

    public function wallet_filter(Request $request)
    {
        $user_id = Auth::guard('web')->user()->id;

        $query = WalletHistory::where('user_id', $user_id);

        // Filter by type (earning / deposit)
        if ($request->has('type') && in_array($request->type, ['earning', 'deposit', 'deduction'])) {
            $query->where('type', $request->type);
        }

        // Search by date
        if ($request->has('string_search') && !empty($request->string_search)) {
            $query->whereDate('created_at', $request->string_search);
        }

        $all_histories = $query->latest()->paginate(10);

        if ($all_histories->count() == 0) {
            return response()->json(['status' => 'nothing']);
        }

        // Return the Blade view for the table rows only
        $html = view('wallet::freelancer.wallet.search-result', compact('all_histories'))->render();

        return response()->json($html);
    }


    public function downloadReport(Request $request)
    {
        $user_id = Auth::guard('web')->user()->id;

        $histories = WalletHistory::where('user_id', $user_id)
            ->when($request->type, fn($q)=>$q->where('type', $request->type))
            ->get();

        $csv = "Type,Amount,Payment Gateway,Status,Date\n";
        foreach($histories as $h){
            $csv .= "{$h->type},{$h->amount},{$h->payment_gateway},{$h->payment_status},{$h->created_at}\n";
        }

        $fileName = 'wallet_report_'.time().'.csv';
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$fileName}");
    }

    //deposit balance to wallet
    public function deposit(Request $request)
    {
        $request->validate([
            'amount'=>'required|numeric|gt:0',
        ]);

        $all_gateway = [
            'wallet','paypal','manual_payment','mollie','paytm','stripe','razorpay',
            'flutterwave','paystack','marcadopago','instamojo','cashfree','payfast',
            'midtrans','squareup','cinetpay','paytabs','billplz','zitopay','sitesway',
            'toyyibpay','authorize_dot_net','kineticpay','awdpay','iyzipay','yoomoney',
            'coinpayments','sslcommerce','xendit'
        ];

        if (empty($request->selected_payment_gateway)) {
            return back()->with(toastr_warning(__('Please select a payment gateway before making a deposit')));
        }

        if (!in_array($request->selected_payment_gateway, $all_gateway)) {
            return back()->with(toastr_warning(__('Invalid payment gateway selected for deposit')));
        }

        if($request->selected_payment_gateway === 'manual_payment') {
            $request->validate([
                'manual_payment_image' => 'required|mimes:jpg,jpeg,png,pdf'
            ]);
        }

        //deposit amount
        $user = Auth::guard('web')->user();
        $user_id = $user->id;
        session()->put('user_id',$user_id);
        $amount = $request->amount;
        $name = $user->first_name.' '.$user->last_name;
        $email = $user->email;
        $user_type = $user->user_type == 1 ? 'client' : 'freelancer';
        $payment_status = $request->selected_payment_gateway === 'manual_payment' ? 'pending' : '';
        $user = Wallet::where('user_id',$user_id)->first();
        if(empty($user)){
            Wallet::create([
                'user_id' => $user_id,
                'balance' => 0,
                'status' => 1,
            ]);
        }

        // calculate transaction fee
        $transaction_type   = get_static_option('transaction_fee_type') ?? '';
        $transaction_charge = get_static_option('transaction_fee_charge') ?? 0;

        $transaction_fee  = transaction_amount($amount, $transaction_type, $transaction_charge);
        $total    = $amount + $transaction_fee;


        $deposit = WalletHistory::create([
            'user_id' => $user_id,
            'amount' => $amount,
            'transaction_fee' => $transaction_fee,
            'total' => $total,
            'payment_gateway' => $request->selected_payment_gateway,
            'payment_status' => $payment_status,
            'status' => 1,
        ]);
        
        $last_deposit_id = $deposit->id;
        $title = __('Deposit To Wallet');
        $description = sprintf(__('Order id #%1$d Email: %2$s, Name: %3$s'),$last_deposit_id,$email,$name);

        if($request->selected_payment_gateway === 'manual_payment') {
            if($request->hasFile('manual_payment_image')){
                $manual_payment_image = $request->manual_payment_image;
                $img_ext = $manual_payment_image->extension();

                $manual_payment_image_name = 'manual_attachment_'.time().'.'.$img_ext;
                if(in_array($img_ext,['jpg','jpeg','png','pdf'])){
                    $manual_image_path = 'assets/uploads/manual-payment/';
                    $manual_payment_image->move($manual_image_path,$manual_payment_image_name);
                    WalletHistory::where('id',$last_deposit_id)->update([
                        'manual_payment_image'=>$manual_payment_image_name
                    ]);
                }else{
                    return back()->with(toastr_warning(__('Image type not supported')));
                }
            }

            try {
                $message_body = __('Hello a').' '.$user_type. __('just deposit to his wallet. Please check and confirm').'</br>'.'<span class="verify-code">'.__('Deposit ID: ').$last_deposit_id.'</span>';
                Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                    'subject' => __('Deposit Confirmation'),
                    'message' => $message_body
                ]));
                Mail::to($email)->send(new BasicMail([
                    'subject' => __('Deposit Confirmation'),
                    'message' => __('Manual deposit success. Your wallet will credited after admin approval').'</br>'.'<span class="verify-code">'.__('Deposit ID: ').$last_deposit_id.'</span>'
                ]));
            } catch (\Exception $e) {
                //
            }
            AdminNotification::create([
                'identity'=>$last_deposit_id,
                'user_id'=>$user_id,
                'type'=>__('Deposit Amount'),
                'message'=>__('User wallet deposit'),
            ]);
            event(new AdminEvent(__('User wallet deposit')));
            toastr_success('Manual deposit success. Your wallet will credited after admin approval');
            return back();

        }
        else
        {
            if ($request->selected_payment_gateway === 'paypal') {
                try {
                    return PaymentGatewayRequestHelper::paypal()->charge_customer($this->buildPaymentArg($this->conversion_method($total),$title,$description,$last_deposit_id,$email,$name,route('freelancer.paypal.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'paytm'){
                try {
                    return PaymentGatewayRequestHelper::paytm()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.paytm.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif ($request->selected_payment_gateway === 'mollie'){
                try {
                    return PaymentGatewayRequestHelper::mollie()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.mollie.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'stripe'){
                try {
                    return PaymentGatewayRequestHelper::stripe()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.stripe.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'razorpay'){
                try {
                    return PaymentGatewayRequestHelper::razorpay()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.razorpay.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'flutterwave'){
                try {
                    return PaymentGatewayRequestHelper::flutterwave()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.flutterwave.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'paystack'){
                try {
                    return PaymentGatewayRequestHelper::paystack()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('paystack.ipn.all'),'freelancer-wallet'));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'xendit'){
                try {
                    return PaymentGatewayRequestHelper::xendit()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('xendit.ipn.all'),'freelancer-wallet'));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'sslcommerce'){
                try {
                    return PaymentGatewayRequestHelper::sslcommerz()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('sslcommerce.ipn.all'),'freelancer-wallet'));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'payfast'){
                try {
                    return PaymentGatewayRequestHelper::payfast()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.payfast.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'cashfree'){
                try {
                    return PaymentGatewayRequestHelper::cashfree()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.cashfree.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'instamojo'){
                try {
                    return PaymentGatewayRequestHelper::instamojo()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.instamojo.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'marcadopago'){
                try {
                    return PaymentGatewayRequestHelper::marcadopago()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.marcadopago.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }

            }
            elseif($request->selected_payment_gateway === 'midtrans'){
                try {
                    return PaymentGatewayRequestHelper::midtrans()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.midtrans.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'squareup'){
                try {
                    return PaymentGatewayRequestHelper::squareup()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.squareup.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'cinetpay'){
                try {
                    return PaymentGatewayRequestHelper::cinetpay()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.cinetpay.ipn.wallet'),'freelancer-wallet'));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'paytabs'){

                try {
                    return PaymentGatewayRequestHelper::paytabs()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.paytabs.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'billplz'){
                try {
                    return PaymentGatewayRequestHelper::billplz()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.billplz.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'zitopay'){
                try {
                    return PaymentGatewayRequestHelper::zitopay()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.zitopay.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'toyyibpay'){
                try {
                    return PaymentGatewayRequestHelper::toyyibpay()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.toyyibpay.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'authorize_dot_net'){
                try {
                    return PaymentGatewayRequestHelper::authorizenet()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.authorize.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'pagali'){
                try {
                    return PaymentGatewayRequestHelper::pagalipay()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.pagali.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'sitesway'){
                try {
                    return PaymentGatewayRequestHelper::sitesway()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.siteways.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }

            elseif($request->selected_payment_gateway === 'iyzipay'){
                try {
                    return PaymentGatewayRequestHelper::iyzipay()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.iyzipay.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }

            elseif($request->selected_payment_gateway === 'kineticpay'){
                try {
                    return PaymentGatewayRequestHelper::kineticpay()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.kineticpay.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }

            elseif($request->selected_payment_gateway === 'awdpay'){
                try {
                    return PaymentGatewayRequestHelper::awdpay()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('freelancer.awdpay.ipn.wallet')));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }

            elseif($request->selected_payment_gateway === 'yoomoney'){
                try {
                    return PaymentGatewayRequestHelper::yoomoney()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('yoomoney.ipn.all'),'freelancer-wallet'));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }

            elseif($request->selected_payment_gateway === 'coinpayments'){
                try {
                    return PaymentGatewayRequestHelper::coinpayments()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('coinpayment.ipn.all'),'freelancer-wallet'));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }

            elseif($request->selected_payment_gateway === 'airwallex'){
                try {
                    return PaymentGatewayRequestHelper::airwallex()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('airwallex.ipn.all'),'client-wallet'));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
            elseif($request->selected_payment_gateway === 'cryptomus'){
                try {
                    return PaymentGatewayRequestHelper::cryptomus()->charge_customer($this->buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,route('cryptomus.ipn.all'),'client-wallet'));
                }catch (\Exception $e){
                    toastr_error($e->getMessage());
                    return back();
                }
            }
        }
    }

    private function buildPaymentArg($total,$title,$description,$last_deposit_id,$email,$name,$ipn_route,$source=null)
    {
        $type = $source == 'freelancer-wallet' ? 'freelancer' : 'client';
        $route = route($type.'.wallet.history');

        return [
            'amount' => $total,
            'title' => $title,
            'description' => $description,
            'ipn_url' => $ipn_route,
            'order_id' => $last_deposit_id,
            'track' => \Str::random(36),
            'cancel_url' => route(self::CANCEL_ROUTE,$last_deposit_id),
            'success_url' => $route,
            'email' => $email,
            'name' => $name,
            'payment_type' => $source,
        ];
    }

    private static function conversion_method($amount)
    {
        if(moduleExists('CurrencySwitcher')){
            $user_currency = Session::get('user_current_currency') ?? get_currency_according_to_user();
            $get_user_currency = \Modules\CurrencySwitcher\App\Models\SelectedCurrencyList::where('currency', $user_currency)
                ->where('status', 1)
                ->first();
            $rate = $get_user_currency->conversion_rate ?? 1;
            return $amount/$rate;
        }else{
            return $amount;
        }
    }
}
