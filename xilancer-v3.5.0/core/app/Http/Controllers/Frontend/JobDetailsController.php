<?php

namespace App\Http\Controllers\Frontend;

use App\Events\ProjectEvent;
use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobPost;
use App\Models\JobProposal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Modules\CurrencySwitcher\App\Models\SelectedCurrencyList;
use Modules\Subscription\Entities\Subscription;
use Modules\Subscription\Entities\UserSubscription;

class JobDetailsController extends Controller
{
    public function job_details($username = null, $slug = null)
    {
        $job_details = JobPost::with(['job_creator' => function ($q) {
            $q->where('user_active_inactive_status', 1);
        }, 'job_skills', 'job_proposals'])
            ->where('slug', $slug)
            ->first();

        if (!empty($job_details) && $job_details->job_creator) {
            $user = $job_details->job_creator->load('user_country');
            return view('frontend.pages.job-details.job-details', compact('job_details', 'user'));
        }

        return back();
    }

    //job proposal
    public function job_proposal_send(Request $request)
    {
        $request->validate([
            'client_id' => 'required',
            'amount' => 'required|numeric|gt:0',
            'duration' => 'required',
            'revision' => 'required|min:0|max:100',
            'cover_letter' => 'required|min:10|max:1000',
        ]);

        $freelancer_id = Auth::guard('web')->user()->id;
        $check_freelancer_proposal = JobProposal::where('freelancer_id', $freelancer_id)->where('job_id', $request->job_id)->first();
        if ($check_freelancer_proposal) {
            return back()->with(toastr_warning(__('You can not send one more proposal.')));
        }
        if (Auth::guard('web')->user()->is_suspend == 1) {
            return back()->with(toastr_warning(__('You can not send job proposal because your account is suspended. please try to contact admin')));
        }

        if (moduleExists('CurrencySwitcher')) {
            $get_user_currency = SelectedCurrencyList::where('currency', get_currency_according_to_user())->first() ?? null;
            $amount = ($request->amount / $get_user_currency->conversion_rate) ?? 0;
            $currency = $get_user_currency->currency ?? 0;
            $conversion_rate = $get_user_currency->conversion_rate ?? 0;
            $symbol = $get_user_currency->symbol ?? null;
        } else {
            $amount = $request->amount;
        }

        if (get_static_option('subscription_enable_disable') != 'disable') {
            $freelancer_subscription = UserSubscription::select(['id', 'user_id', 'limit', 'expire_date', 'created_at'])
                ->where('payment_status', 'complete')
                ->where('status', 1)
                ->where('user_id', $freelancer_id)
                ->where("limit", '>=', get_static_option('limit_settings'))
                ->whereDate('expire_date', '>', Carbon::now())->first();
            $total_limit = UserSubscription::where('user_id', $freelancer_id)->where('payment_status', 'complete')->whereDate('expire_date', '>', Carbon::now())->sum('limit');
            if ($total_limit >= get_static_option('limit_settings') ?? 2 && !empty($freelancer_subscription)) {
                $attachment_name = '';

                $upload_folder = 'jobs/proposal';
                $storage_driver = Storage::getDefaultDriver();
                $extensions = array('png', 'jpg', 'jpeg', 'bmp', 'gif', 'tiff', 'svg');

                if (cloudStorageExist() && in_array(Storage::getDefaultDriver(), ['s3', 'cloudFlareR2', 'wasabi'])) {
                    if ($attachment = $request->file('attachment')) {
                        $request->validate([
                            'attachment' => 'required|mimes:png,jpg,jpeg,bmp,gif,tiff,svg,csv,txt,xlx,xls,pdf,docx|max:2048',
                        ]);
                        $attachment_name = time() . '-' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                        if (in_array($attachment->getClientOriginalExtension(), $extensions)) {
                            add_frontend_cloud_image_if_module_exists($upload_folder, $attachment, $attachment_name, 'public');
                        } else {
                            add_frontend_cloud_image_if_module_exists($upload_folder, $attachment, $attachment_name, 'public');
                        }
                    }
                } else {
                    if ($attachment = $request->file('attachment')) {
                        $request->validate([
                            'attachment' => 'required|mimes:png,jpg,jpeg,bmp,gif,tiff,svg,csv,txt,xlx,xls,pdf,docx|max:2048',
                        ]);
                        $attachment_name = time() . '-' . uniqid() . '.' . $attachment->getClientOriginalExtension();

                        if (in_array($attachment->getClientOriginalExtension(), $extensions)) {
                            $resize_full_image = Image::make($request->attachment)
                                ->resize(1000, 600);
                            $resize_full_image->save('assets/uploads/jobs/proposal' . '/' . $attachment_name);
                        } else {
                            $attachment->move('assets/uploads/jobs/proposal', $attachment_name);
                        }
                    }
                }

                $proposal = JobProposal::create([
                    'job_id' => $request->job_id,
                    'freelancer_id' => auth()->user()->id,
                    'client_id' => $request->client_id,
                    'amount' => $amount,
                    'duration' => $request->duration,
                    'revision' => $request->revision,
                    'cover_letter' => $request->cover_letter,
                    'attachment' => $attachment_name,
                    'currency' => $currency ?? null,
                    'conversion_rate' => $conversion_rate ?? null,
                    'symbol' => $symbol ?? null,
                    'load_from' => in_array($storage_driver, ['CustomUploader']) ? 0 : 1, //added for cloud storage 0=local 1=cloud
                ]);
                client_notification($proposal->id, $request->client_id, 'Proposal', 'You have a new job proposal');
                event(new ProjectEvent(__('You have a new job proposal'), $request->client_id));

                UserSubscription::where('id', $freelancer_subscription->id)->update([
                    'limit' => $freelancer_subscription->limit - (get_static_option('limit_settings') ?? 2)
                ]);

                return back()->with(toastr_success(__('Proposal successfully send')));
            }
            return back()->with(toastr_warning(__('You have not enough connect to apply.')));
        } else {
            $attachment_name = '';
            if ($attachment = $request->file('attachment')) {
                $request->validate([
                    'attachment' => 'required|mimes:png,jpg,jpeg,bmp,gif,tiff,svg,csv,txt,xlx,xls,pdf|max:2048',
                ]);
                $attachment_name = time() . '-' . uniqid() . '.' . $attachment->getClientOriginalExtension();
                $extensions = array('png', 'jpg', 'jpeg', 'bmp', 'gif', 'tiff', 'svg');

                if (in_array($attachment->getClientOriginalExtension(), $extensions)) {
                    $resize_full_image = Image::make($request->attachment)
                        ->resize(1000, 600);
                    $resize_full_image->save('assets/uploads/jobs/proposal' . '/' . $attachment_name);
                } else {
                    $attachment->move('assets/uploads/jobs/proposal', $attachment_name);
                }
            }
            $proposal = JobProposal::create([
                'job_id' => $request->job_id,
                'freelancer_id' => auth()->user()->id,
                'client_id' => $request->client_id,
                'amount' => $amount,
                'duration' => $request->duration,
                'revision' => $request->revision,
                'cover_letter' => $request->cover_letter,
                'currency' => $currency ?? null,
                'conversion_rate' => $conversion_rate ?? null,
                'symbol' => $symbol ?? null,
                'attachment' => $attachment_name,
            ]);
            client_notification($proposal->id, $request->client_id, 'Proposal', __('You have a new job proposal'));
            return back()->with(toastr_success(__('Proposal successfully send')));
        }
    }
}
