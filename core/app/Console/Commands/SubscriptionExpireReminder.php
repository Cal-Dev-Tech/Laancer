<?php

namespace App\Console\Commands;

use App\Events\ProjectEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Modules\Subscription\Entities\UserSubscription;

class SubscriptionExpireReminder extends Command
{

    protected $signature = 'subscription:expire-reminder';
    protected $description = 'Command description';


        public function handle()
    {
        // Get all active subscriptions expiring within 3 days
        $reminder_days = get_static_option('subscription_expire_days') ?? 3;

        $active_subscriptions = UserSubscription::where('payment_status', 'complete')
            ->where('status', 1)
            ->whereBetween('expire_date', [Carbon::now(), Carbon::now()->addDays($reminder_days)])
            ->get(['id', 'user_id', 'limit', 'expire_date', 'created_at']);


        foreach ($active_subscriptions as $subscription) {
            $user = User::where('id',$subscription->user_id)->select('id','user_type')->first();

            if ($user) {
                // Send notification based on user type
                if ($user->user_type == 1) {
                    event(new ProjectEvent(__('Your subscription will expire at '). $subscription->expire_date ,$subscription->user_id));
                    client_notification($subscription->id, $subscription->user_id, 'Subscription', __('Your subscription will expire at '). $subscription->expire_date);
                } else {
                    freelancer_notification($subscription->id, $subscription->user_id, 'Subscription', __('Your subscription will expire at '). $subscription->expire_date);
                    event(new ProjectEvent(__('Your subscription will expire at '). $subscription->expire_date ,$subscription->user_id));
                }
            }
        }

        return 0;
    }
}