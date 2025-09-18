<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Modules\Subscription\Entities\UserSubscription;

class DashboardController extends Controller
{
    public function dashboard()
    {
        Artisan::call('permission:cache-reset');
        $total_job = JobPost::whereHas('job_creator')->count();
        $total_client = User::where('user_type',1)->count();
        $total_freelancer = User::where('user_type',2)->count();
        $total_order_revenue = Order::where('status',3)->sum('commission_amount');
        $total_subscription_revenue = UserSubscription::where('payment_status','complete')->sum('price');
        $total_promotion_revenue = 0;
        if (moduleExists('PromoteFreelancer')) {
            $total_promotion_revenue = \Modules\PromoteFreelancer\Entities\PromotionProjectList::where('payment_status', 'complete')->sum('price');
        }
        $orders = Order::whereHas('user')->whereHas('freelancer')->latest()->take(10)->get();
        if(moduleExists('FakeDataGenerator')){
            //get from support
            $total_revenue = Order::where('status',3)->where('created_at' , '>', '2025-05-03')->sum('commission_amount');
        }else{
            $total_revenue = $total_order_revenue + $total_subscription_revenue + $total_promotion_revenue;
        }

        for($i=11; $i>=0;$i--){
            $month_list[] = Carbon::now()->subMonth($i)->format('M');
            $monthly_income_from_order[$i] = Order::where('status',3)
            ->whereYear('created_at', Carbon::now()->subMonth($i)->year)
            ->whereMonth('created_at',Carbon::now()
                ->subMonth($i)->month)
            ->sum('commission_amount');

            $monthly_income_from_subscription[$i] = UserSubscription::where('payment_status','complete')
                ->whereYear('created_at', Carbon::now()->subMonth($i)->year)
                ->whereMonth('created_at',Carbon::now()
                    ->subMonth($i)->month)
                ->sum('price');
            
            $monthly_income_from_promotion[$i] = 0;
            if (moduleExists('PromoteFreelancer')) {
                $monthly_income_from_promotion[$i] = \Modules\PromoteFreelancer\Entities\PromotionProjectList::where('payment_status', 'complete')
                    ->whereYear('created_at', Carbon::now()->subMonth($i)->year)
                    ->whereMonth('created_at', Carbon::now()->subMonth($i)->month)
                    ->sum('price');
            }
            
            $monthly_income[$i]=$monthly_income_from_order[$i] + $monthly_income_from_subscription[$i] + $monthly_income_from_promotion[$i];
        }
        return view('backend.pages.dashboard.dashboard',compact([
            'total_job',
            'total_client',
            'total_freelancer',
            'total_order_revenue',
            'total_subscription_revenue',
            'total_promotion_revenue',
            'total_revenue',
            'orders',
            'month_list',
            'monthly_income',
        ]));
    }
}
