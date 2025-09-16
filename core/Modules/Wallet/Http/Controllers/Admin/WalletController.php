<?php

namespace Modules\Wallet\Http\Controllers\Admin;


use App\Models\User;
use App\Mail\BasicMail;
use App\Events\AdminEvent;
use App\Events\ProjectEvent;
use Illuminate\Http\Request;
use App\Models\AdminNotification;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Wallet\Entities\Wallet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Modules\Wallet\Entities\WalletHistory;
use Illuminate\Contracts\Support\Renderable;

class WalletController extends Controller
{
    //deposit amount settings
    public function deposit_settings(Request $request)
    {
        $request->validate(
            [
                'deposit_amount_limitation_for_user' => 'numeric|gt:0|max:500000000',
            ],
            [
                'deposit_amount_limitation_for_user.numeric' => 'Please enter only numeric value.'
            ]
        );
        if ($request->isMethod('post')) {
            $fields = ['deposit_amount_limitation_for_user'];
            foreach ($fields as $field) {
                update_static_option($field, $request->$field);
            }
            toastr_success(__('Update Success'));
            return back();
        }
        return view('wallet::admin.wallet.deposit-settings');
    }

    //display wallet history
    public function wallet_history()
    {
        $all_histories = WalletHistory::whereHas('user')->latest()->paginate(10);
        return view('wallet::admin.wallet.wallet-history', compact('all_histories'));
    }

    public function history_details($id)
    {
        $history_details = WalletHistory::whereHas('user')->where('id', $id)->first();
        AdminNotification::where('identity', $id)->update(['is_read' => 'read']);
        return !empty($history_details) ? view('wallet::admin.wallet.history-details', compact('history_details')) : back();
    }

    // pagination
    function pagination(Request $request)
    {
        if ($request->ajax()) {
            $all_histories = WalletHistory::whereHas('user')->latest()->paginate(10);
            return view('wallet::admin.wallet.search-result', compact('all_histories'))->render();
        }
    }

    // search category
    public function search_history(Request $request)
    {
        $all_histories = WalletHistory::whereHas('user')->where('created_at', 'LIKE', "%" . strip_tags($request->string_search) . "%")
            ->paginate(10);
        if ($all_histories->total() >= 1) {
            return view('wallet::admin.wallet.search-result', compact('all_histories'))->render();
        } else {
            return response()->json([
                'status' => __('nothing')
            ]);
        }
    }

    // change history status
    public function change_status(Request $request, $id)
    {
        $history = WalletHistory::select(['id', 'user_id', 'payment_gateway', 'payment_status', 'amount'])
            ->where('id', $id)
            ->firstOrFail();

        if ($history->payment_status != 'pending') {
            return back()->with(toastr_error(__('Action not allowed')));
        }

        $action = $request->input('cancel_or_decline_order');

        if ($action === 'complete') {
            $user_wallet = Wallet::where('user_id', $history->user_id)->first();
            $history->update(['payment_status' => 'complete']);
            $user_wallet->update(['balance' => $user_wallet->balance + $history->amount]);

            $message = 'Wallet successfully deposited';
        } elseif ($action === 'reject') {
            $history->update(['payment_status' => 'reject']);
            $message = 'Deposit request rejected';
        } elseif ($action === 'cancel') {
            $history->update(['payment_status' => 'cancel']);
            $message = 'Deposit request canceled';
        } else {
            return back()->with(toastr_error(__('Invalid action')));
        }

        // send notifications
        $user_type = User::select(['id', 'user_type'])->where('id', $history->user_id)->first();
        if ($user_type->user_type == 1) {
            client_notification($history->id, $history->user_id, 'Deposit', $message);
        } else {
            freelancer_notification($history->id, $history->user_id, 'Deposit', $message);
        }

        event(new ProjectEvent(__($message), $history->user_id));

        return redirect()->back()->with(toastr_success(__('Status Successfully Changed')));
    }

    // credit freelancer/client wallet from admin
    public function creditUserWallet(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount'  => 'required|numeric|min:1',
                'note'    => 'nullable|string|max:255',
            ]);

            $user = User::findOrFail($request->user_id);

            // Find or create wallet
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'remaining_balance' => 0, 'status' => 1]
            );

            // Update balance
            $wallet->balance += $request->amount;
            $wallet->remaining_balance += $request->amount;
            $wallet->save();

            // Log history
            $history = WalletHistory::create([
                'user_id'         => $user->id,
                'payment_gateway' => 'manual_admin',
                'payment_status'  => 'complete',
                'amount'          => $request->amount,
                'note'            => $request->note,
                'transaction_id'  => 'ADMIN-' . uniqid(),
                'status'          => 1,
            ]);

            // Send email 
            try {
                $message = get_static_option('admin_credit_wallet_message')
                    ?? __('Hello @name, an amount of @amount has been credited to your wallet by the admin. Reference ID: @deposit_id. @note');

                $message = str_replace(
                    ["@name", "@deposit_id", "@amount", "@note"],
                    [$user->first_name . ' ' . $user->last_name, $history->id, $request->amount, $request->note ?? ''],
                    $message
                );

                Mail::to($user->email)->send(new BasicMail([
                    'subject' => get_static_option('admin_credit_wallet_subject') ?? __('Wallet Credited by Admin'),
                    'message' => $message
                ]));

                $history->update(['email_send' => 1]);
            } catch (\Exception $e) {
                Log::error('Wallet credit email failed: ' . $e->getMessage());
            }

            // Notify user (include note if exists)
            $notificationMessage = __('Your wallet has been credited by admin. Amount: ') . $request->amount;
            if ($request->note) {
                $notificationMessage .= ' - ' . $request->note;
            }

            if ($user->user_type == 1) {
                client_notification(0, $user->id, 'Deposit', $notificationMessage);
            } else {
                freelancer_notification(0, $user->id, 'Deposit', $notificationMessage);
            }

            return response()->json([
                'status' => 'success',
                'message' => __('Wallet credited successfully.')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors
            return response()->json([
                'status' => 'error',
                'message' => implode(' ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            // Other errors
            Log::error('Wallet credit failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('Something went wrong, please try again.')
            ], 500);
        }
    }

    // deduct freelancer/client wallet from admin
    public function deductUserWallet(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|exists:users,id',
                'amount'  => 'required|numeric|min:1',
                'note'    => 'nullable|string|max:255',
            ]);

            $user = User::findOrFail($request->user_id);

            // Find or create wallet
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0, 'remaining_balance' => 0, 'status' => 1]
            );

            if ($wallet->balance < $request->amount) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('User wallet balance is insufficient.')
                ], 422);
            }

            // Deduct balance
            $wallet->balance -= $request->amount;
            $wallet->remaining_balance -= $request->amount;
            $wallet->save();

            // Log history
            $history = WalletHistory::create([
                'user_id'         => $user->id,
                'payment_gateway' => 'manual_admin',
                'payment_status'  => 'complete',
                'amount'          => $request->amount,
                'transaction_id'  => 'ADMIN-' . uniqid(),
                'note'            => $request->note,
                'type'            => 'deduction',
                'status'          => 1,
            ]);

            // Send email with note if exists
            try {
                $message = __('Hello @name, an amount of @amount has been deducted from your wallet by the admin. Reference ID: @transaction_id. @note');
                $message = str_replace(
                    ["@name", "@transaction_id", "@amount", "@note"],
                    [$user->first_name . ' ' . $user->last_name, $history->transaction_id, $request->amount, $request->note ?? ''],
                    $message
                );

                Mail::to($user->email)->send(new BasicMail([
                    'subject' => __('Wallet Deducted by Admin'),
                    'message' => $message
                ]));

                $history->update(['email_send' => 1]);
            } catch (\Exception $e) {
                Log::error('Wallet deduction email failed: ' . $e->getMessage());
            }

            // Notify user
            if ($user->user_type == 1) {
                client_notification(0, $user->id, 'Deposit', __('Amount deducted: ') . $request->amount . ($request->note ? ' - ' . $request->note : ''));
            } else {
                freelancer_notification(0, $user->id, 'Deposit', __('Amount deducted: ') . $request->amount . ($request->note ? ' - ' . $request->note : ''));
            }

            return response()->json([
                'status' => 'success',
                'message' => __('Amount successfully deducted from user wallet.')
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => implode(' ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Wallet deduction failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => __('Something went wrong, please try again.')
            ], 500);
        }
    }


    public function searchUsers(Request $request)
    {
        $q = $request->get('q');

        $users = User::query()
            ->where('user_active_inactive_status', 1)
            ->when($q, function ($query, $q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('first_name', 'like', "%$q%")
                        ->orWhere('last_name', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%");
                });
            })
            ->select('id', 'first_name', 'last_name', 'email')
            ->orderBy('first_name')
            ->limit(20)
            ->get();

        return response()->json($users->map(function ($u) {
            return [
                'id'   => $u->id,
                'text' => $u->first_name . ' ' . $u->last_name . ' (' . $u->email . ')',
            ];
        }));
    }
}
