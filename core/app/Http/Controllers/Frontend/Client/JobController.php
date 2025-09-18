<?php

namespace App\Http\Controllers\Frontend\Client;

use App\Models\Skill;
use App\Models\Length;
use App\Mail\BasicMail;
use App\Models\JobPost;
use App\Models\Project;
use App\Models\UserWork;
use App\Events\AdminEvent;
use App\Models\JobHistory;
use App\Helper\LogActivity;
use App\Models\JobProposal;
use Illuminate\Support\Str;
use App\Events\ProjectEvent;
use Illuminate\Http\Request;
use App\Models\ExperienceLevel;
use App\Models\AdminNotification;
use App\Models\ClientNotification;
use Modules\Wallet\Entities\Wallet;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Modules\Service\Entities\SubCategory;
use Modules\CurrencySwitcher\App\Models\SelectedCurrencyList;

class JobController extends Controller
{
    //all jobs
    public function all_job()
    {
        $user_id = Auth::guard('web')->user()->id;
        $all_jobs = JobPost::select(['id','title','description','type','level','status','on_off','current_status','created_at'])
            ->withCount('job_proposals')
            ->latest()->where('user_id',$user_id)
            ->paginate(10);

        $active_jobs = JobPost::where('current_status',1)->where('user_id',$user_id)->count();
        $complete_jobs = JobPost::where('current_status',2)->where('user_id',$user_id)->count();
        $closed_jobs = JobPost::where('on_off',0)->where('user_id',$user_id)->count();

        $top_projects = Project::select('id', 'title','slug','user_id','basic_regular_charge','basic_discount_charge','basic_delivery','description','image')
            ->where('project_on_off','1')
            ->whereHas('project_creator')
            ->where('status','1')
            ->latest()
            ->take(3)
            ->get();

        return view('frontend.user.client.job.my-job.all-jobs',compact(['all_jobs','active_jobs','complete_jobs','closed_jobs','top_projects']));
    }

    //job filter
    public function job_filter(Request $request)
    {
        $user_id = Auth::guard('web')->user()->id;
        $query = $all_jobs = JobPost::select(['id','title','description','type','level','status','on_off','current_status','created_at'])
            ->latest()
            ->where('user_id',$user_id);

        if($request->value == 'all'){
            $all_jobs = $query->paginate(10);
        }
        if($request->value == 'active'){
            $all_jobs = $query->where('current_status',1)->paginate(10);
        }
        if($request->value == 'complete'){
            $all_jobs = $query->where('current_status',2)->paginate(10);
        }
        if($request->value == 'close'){
            $all_jobs = $query->where('on_off',0)->paginate(10);
        }

        return view('frontend.user.client.job.my-job.search-result',compact('all_jobs'))->render();
    }

    //job create
    public function job_create(Request $request)
    {
        if($request->isMethod('post'))
        {
            $slug_validation = moduleExists('CurrencySwitcher')
                ? 'required|max:191'
                : 'required|max:191|unique:job_posts,slug';

             $request->validate([
                'title'=>'required|min:5|max:100',
                'slug'=>$slug_validation,
                'category'=>'required',
                'duration'=>'required|max:191',
                'level'=>'required|max:191',
                'description'=>'required|min:10',
                'type'=>'required|max:191',
                 'skill'=>'required|array',
                 'meta_title'=>'nullable|max:255',
                 'meta_description'=>'nullable|max:500',
            ]);


             if($request->type == 'fixed'){
                 $request->validate([
                     'budget'=>'required|numeric|gt:0',
                 ]);
             }else{
                 $request->validate([
                     'hourly_rate'=>'required|numeric|gt:0',
                     'estimated_hours'=>'required|numeric|gt:0',
                 ]);
             }

            $budget = null;
            $hourly_rate = null;
            $user_id  = Auth::guard('web')->user()->id;
            $slug = !empty($request->slug) ? $request->slug : $request->title;

            if(moduleExists('CurrencySwitcher')){
                $slug = $this->generate_unique_slug($request->slug ?? $request->title);
                $get_user_currency = SelectedCurrencyList::where('currency',get_currency_according_to_user())->first() ?? null;
                if(!empty($get_user_currency)){
                    $budget = $request->budget/$get_user_currency->conversion_rate;
                    $hourly_rate = $request->hourly_rate/$get_user_currency->conversion_rate;
                }
            }

            $attachmentName = '';
            $upload_folder = 'jobs';
            $storage_driver = Storage::getDefaultDriver();

            if ($attachment = $request->file('attachment')) {

                $allowedSize = get_static_option('max_upload_size') ?? '5120';
                $allowedExtensions = json_decode(get_static_option('file_extensions'), true);

                if($allowedExtensions){
                    $allowed_extensions = implode(',', $allowedExtensions);
                    $request->validate([
                        'attachment' => 'required|mimes:' . $allowed_extensions . '|max:' . $allowedSize,
                    ]);
                }else{
                    $request->validate([
                        'attachment'=>'required|mimes:png,jpg,jpeg,bmp,gif,tiff,svg,csv,txt,xlx,xls,pdf,docx|max:5120',
                    ]);
                }

                $attachmentName = time().'-'.uniqid().'.'.$attachment->getClientOriginalExtension();
                $extensions = array('png','jpg','jpeg','bmp','gif','tiff','svg');

                if (cloudStorageExist() && in_array(Storage::getDefaultDriver(), ['s3', 'cloudFlareR2', 'wasabi'])) {
                    if(in_array($attachment->getClientOriginalExtension(), $extensions)){
                        add_frontend_cloud_image_if_module_exists($upload_folder, $attachment, $attachmentName,'public');
                    }else{
                        add_frontend_cloud_image_if_module_exists($upload_folder, $attachment, $attachmentName,'public');
                    }
                }else{
                    if(in_array($attachment->getClientOriginalExtension(), $extensions)){
                        $resize_full_image = Image::make($request->attachment)
                            ->resize(800, 500);
                        $resize_full_image->save('assets/uploads/jobs' .'/'. $attachmentName);
                    }else{
                        $attachment->move('assets/uploads/jobs', $attachmentName);
                    }
                }

            }

            $job = JobPost::create([
                'user_id'=>$user_id,
                'title'=>$request->title,
                'slug' => Str::slug(purify_html($slug),'-',null),
                'category'=>$request->category,
                'duration'=>$request->duration,
                'level'=>$request->level,
                'description'=>$request->description,
                'type'=>$request->type,
                'hourly_rate'=>$hourly_rate ?? $request->hourly_rate,
                'estimated_hours'=>$request->estimated_hours,
                'budget'=>$budget ?? $request->budget ?? 0,
                'attachment'=>$attachmentName,
                'status'=> get_static_option('job_auto_approval')  == 'no' ? 0 : 1,
                'job_approve_request'=>  1,
                'meta_title'=>$request->meta_title,
                'meta_description'=>$request->meta_description,
                'load_from' => in_array($storage_driver,['CustomUploader']) ? 0 : 1, //added for cloud storage 0=local 1=cloud
            ]);

            $job->job_sub_categories()->attach($request->subcategory);
            $job->job_skills()->attach($request->skill);

            $get_job_related_freelancers = UserWork::where('category_id',$request->category)->take(30)->get();
            foreach($get_job_related_freelancers as $freelancer){
                freelancer_notification($job->id, $freelancer->user_id, 'New Job', 'New Job Posted.');
                event(new ProjectEvent(__('New Job Posted.'), $freelancer->user_id));
            }


            // Generate optimized meta title
            $meta_title = $request->meta_title ?? $request->title;
            $meta_title = strlen($meta_title) > 60 ? substr($meta_title, 0, 57) . '...' : $meta_title;
            
            // Generate optimized meta description
            $meta_description = $request->meta_description ?? $request->description;
            $meta_description = strlen($meta_description) > 160 
                ? substr(strip_tags($meta_description), 0, 157) . '...' 
                : strip_tags($meta_description);
            
            // Generate keywords from title and skills
            $title_words = explode(' ', strtolower($request->title));
            $skill_names = collect($request->skill)->map(function($skill_id) {
                $skill = Skill::find($skill_id);
                return $skill ? strtolower($skill->skill) : null;
            })->filter()->toArray();
            
            $all_keywords = array_merge($title_words, $skill_names);
            $keywords = collect($all_keywords)
                ->filter(function($word) {
                    $stopWords = ['a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'has', 'he', 'in', 'is', 'it', 'its', 'of', 'on', 'that', 'the', 'to', 'was', 'will', 'with'];
                    return strlen(trim($word)) > 2 && !in_array(trim($word), $stopWords);
                })
                ->map(fn($word) => trim($word))
                ->unique()
                ->take(10)
                ->implode(', ');
            
            $image_extensions = ['png','jpg','jpeg','bmp','gif','tiff','svg','webp'];
            $image_url = null;
            
            if ($attachmentName && in_array(strtolower(pathinfo($attachmentName, PATHINFO_EXTENSION)), $image_extensions)) {
                $image_url = asset('assets/uploads/jobs/' . $attachmentName);
            } else {
                $image_url = asset('assets/frontend/img/job-default.jpg');
            }
            
            // Prepare meta data
            $metaData = [
                'meta_title' => purify_html($meta_title),
                'meta_description' => purify_html($meta_description),
                'meta_tags' => purify_html($keywords),

                'facebook_meta_tags' => purify_html($keywords),
                'facebook_meta_description' => purify_html($meta_description),
                'facebook_meta_image' => $image_url,
                
                // Twitter Card meta tags
                'twitter_meta_tags' => purify_html($keywords),
                'twitter_meta_description' => purify_html($meta_description),
                'twitter_meta_image' => $image_url,
            ];

            // Create meta data
            $job->metaData()->create($metaData);

            //security manage
            if(moduleExists('SecurityManage')){
                LogActivity::addToLog('Job create','Client');
            }

            try {
                $message = get_static_option('job_create_email_message') ?? __('New job has been published.');
                $message = str_replace(["@job_title"],[$job->title], $message);
                Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                    'subject' => get_static_option('job_create_email_subject') ?? __('New Job'),
                    'message' => $message
                ]));
            }catch (\Exception $e) {}

            //create project notification to admin
            AdminNotification::create([
                'identity'=>$job->id,
                'user_id'=>$user_id,
                'type'=>'Job',
                'message'=>__('New job has been published.'),
            ]);
            event(new AdminEvent(__('New job has been published.')));

            toastr_success(__('Job successfully created'));
            return redirect()->route('client.job.all');
        }
        $all_lengths = Length::where('status', 1)->get();
        return view('frontend.user.client.job.create.create-job',compact('all_lengths'));
    }

    //job edit
    public function job_edit(Request $request,$id)
    {
        $user_id  = Auth::guard('web')->user()->id;
        $job_details = JobPost::where('id',$id)->where('user_id',$user_id)->first();
        $all_lengths = Length::where('status', 1)->get();
        $all_levels = ExperienceLevel::where('status',1)->get();
        $get_sub_categories_from_job_category = SubCategory::where('category_id',$job_details->category)->get() ?? '';
        $slug = !empty($request->slug) ? $request->slug : $request->title;
        $delete_old_attachment =  'assets/uploads/jobs/'.$job_details->attachment;

        if($request->isMethod('post'))
        {
            $slug_validation = moduleExists('CurrencySwitcher')
                ? 'required|max:191'
                : 'required|max:191|unique:job_posts,slug,'.$id;

            $request->validate([
                'title'=>'required|min:5|max:100',
                'slug'=>$slug_validation,
                'category'=>'required',
                'duration'=>'required|max:191',
                'level'=>'required|max:191',
                'description'=>'required|min:10',
                'type'=>'required|max:191',
                'skill'=>'required|array',
                'meta_title'=>'nullable|max:255',
                'meta_description'=>'nullable|max:500',
            ]);

            if($request->type == 'fixed'){
                $request->validate([
                    'budget'=>'required|numeric|gt:0',
                ]);
            }else{
                $request->validate([
                    'hourly_rate'=>'required|numeric|gt:0',
                    'estimated_hours'=>'required|numeric|gt:0',
                ]);
            }

            $budget = null;
            $hourly_rate = null;
            if(moduleExists('CurrencySwitcher')){
                $slug = $this->generate_unique_slug($request->slug ?? $request->title);
                $get_user_currency = SelectedCurrencyList::where('currency',get_currency_according_to_user())->first() ?? null;
                if(!empty($get_user_currency)){
                    $budget = $request->budget/$get_user_currency->conversion_rate;
                    $hourly_rate = $request->hourly_rate/$get_user_currency->conversion_rate;
                }
            }

            $attachmentName = '';
            $upload_folder = 'jobs';
            $extensions = array('png','jpg','jpeg','bmp','gif','tiff','svg');

            $allowedSize = get_static_option('max_upload_size') ?? '5120';
            $allowedExtensions = json_decode(get_static_option('file_extensions'), true);

            if (cloudStorageExist() && in_array(Storage::getDefaultDriver(), ['s3', 'cloudFlareR2', 'wasabi'])) {
                if ($attachment = $request->file('attachment')) {

                    if($allowedExtensions){
                        $allowed_extensions = implode(',', $allowedExtensions);
                        $request->validate([
                            'attachment' => 'required|mimes:' . $allowed_extensions . '|max:' . $allowedSize,
                        ]);
                    }else{
                        $request->validate([
                            'attachment'=>'required|mimes:png,jpg,jpeg,bmp,gif,tiff,svg,csv,txt,xlx,xls,pdf,docx|max:5120',
                        ]);
                    }

                    $currentImagePath = $job_details->attachment;
                    if ($currentImagePath) {
                        delete_frontend_cloud_image_if_module_exists('jobs/'.$currentImagePath);
                    }

                    $attachmentName = time().'-'.uniqid().'.'.$attachment->getClientOriginalExtension();
                    if(in_array($attachment->getClientOriginalExtension(), $extensions)){
                        add_frontend_cloud_image_if_module_exists($upload_folder, $attachment, $attachmentName,'public');
                    }else{
                        add_frontend_cloud_image_if_module_exists($upload_folder, $attachment, $attachmentName,'public');
                    }
                }else{
                    $attachmentName = $job_details->attachment;
                }
            }else{
                if ($attachment = $request->file('attachment')) {
                    if($allowedExtensions){
                        $allowed_extensions = implode(',', $allowedExtensions);
                        $request->validate([
                            'attachment' => 'required|mimes:' . $allowed_extensions . '|max:' . $allowedSize,
                        ]);
                    }else{
                        $request->validate([
                            'attachment'=>'required|mimes:png,jpg,jpeg,bmp,gif,tiff,svg,csv,txt,xlx,xls,pdf,docx|max:5120',
                        ]);
                    }
                    if(file_exists($delete_old_attachment)){
                        File::delete($delete_old_attachment);
                    }
                    $attachmentName = time().'-'.uniqid().'.'.$attachment->getClientOriginalExtension();
                    if(in_array($attachment->getClientOriginalExtension(), $extensions)){
                        $resize_full_image = Image::make($request->attachment)
                            ->resize(800, 500);
                        $resize_full_image->save('assets/uploads/jobs' .'/'. $attachmentName);
                    }else{
                        $attachment->move('assets/uploads/jobs', $attachmentName);
                    }
                }else{
                    $attachmentName = $job_details->attachment;
                }
            }

            JobPost::where('id',$id)->update([
                'user_id'=>$user_id,
                'title'=>$request->title,
                'slug' => Str::slug(purify_html($slug),'-',null),
                'category'=>$request->category,
                'duration'=>$request->duration,
                'level'=>$request->level,
                'description'=>$request->description,
                'type'=>$request->type,
                'hourly_rate'=>$hourly_rate ?? $request->hourly_rate,
                'estimated_hours'=>$request->estimated_hours,
                'budget'=>$budget ?? $request->budget,
                'attachment'=>$attachmentName,
                'meta_title'=>$request->meta_title,
                'status'=> get_static_option('job_auto_approval')  == 'no' ? 0 : 1,
                'meta_description'=>$request->meta_description,
            ]);

            $job = JobPost::find($id);
            $job->job_sub_categories()->sync($request->subcategory);
            $job->job_skills()->sync($request->skill);
            $job_id_from_job_history_table = JobHistory::where('job_id', $id)->first();

            if(empty($job_id_from_job_history_table)){
                JobHistory::Create([
                    'job_id'=>$job->id,
                    'user_id'=>$job->user_id,
                    'reject_count'=>0,
                    'edit_count'=>1,
                ]);
            }else{
                JobHistory::where('job_id',$id)->update([
                    'reject_count'=>$job_id_from_job_history_table->edit_count + 1
                ]);
            }

            //security manage
            if(moduleExists('SecurityManage')){
                LogActivity::addToLog('Job edit','Client');
            }

            try {
                $message = get_static_option('job_edit_email_message') ?? __('A job has been edited.');
                $message = str_replace(["@job_title"],[$job->title], $message);
                Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                    'subject' => get_static_option('job_edit_email_subject') ?? __('Job Edit Email'),
                    'message' => $message
                ]));
            }catch (\Exception $e) {}

            //edit job notification to admin
            AdminNotification::create([
                'identity'=>$job->id,
                'user_id'=>$user_id,
                'type'=>'Edit Job',
                'message'=>__('A Job has been edited.'),
            ]);

            event(new AdminEvent(__('A Job has been edited.')));
            toastr_success(__('Job successfully Updated'));
            return redirect()->route('client.job.all');
        }
        return view('frontend.user.client.job.edit.edit-job',compact(['job_details','get_sub_categories_from_job_category','all_lengths','all_levels']));
    }

    // pagination
    function pagination(Request $request)
    {
        if($request->ajax()){
            $user_id = Auth::guard('web')->user()->id;
            $query = $all_jobs = JobPost::select(['id','title','description','type','level','status','on_off','current_status','created_at'])
                ->latest()
                ->where('user_id',$user_id);

            if($request->value == 'all'){
                $all_jobs = $query->paginate(10);
            }
            if($request->value == 'active'){
                $all_jobs = $query->where('current_status',1)->paginate(10);
            }
            if($request->value == 'complete'){
                $all_jobs = $query->where('current_status',2)->paginate(10);
            }
            if($request->value == 'close'){
                $all_jobs = $query->where('on_off',0)->paginate(10);
            }
            return view('frontend.user.client.job.my-job.search-result',compact('all_jobs'))->render();
        }
    }

    //job details
    public function job_details(Request $request, $id)
    {
        $job_details = JobPost::with(['job_creator','job_skills','job_proposals'])
            ->where('id',$id)
            ->where('user_id',Auth::guard('web')->user()->id)
            ->first();

        $hired_freelancer_count = JobProposal::where('job_id',$id)->where('is_hired',1)->count();
        $short_listed_freelancer_count = JobProposal::where('job_id',$id)->where('is_hired',0)->where('is_rejected',0)->where('is_short_listed',1)->count();
        $interviewed_freelancer_count = JobProposal::where('job_id',$id)->where('is_rejected',0)->where('is_interview_take',1)->count();

        JobPost::where('id',$id)->update(['last_seen'=>date('Y-m-d H:i:s')]);
        if(!$request->ajax()) {
            if ($request->has('mark_as_read') && $request->mark_as_read == 'true') {
                ClientNotification::where('client_id', Auth::guard('web')->user()->id)
                    ->where('is_read', 'unread')->where('type', 'Job')->where('identity', $id)
                    ->update(['is_read' => 'read']);
            }
        }
        return !empty($job_details) ? view('frontend.user.client.job.job-details.job-details',compact(['job_details','hired_freelancer_count','short_listed_freelancer_count','interviewed_freelancer_count'])) : back();
    }

    //proposal details
    public function proposal_details($id)
    {
        $proposal_details = JobProposal::where('id',$id)
            ->where('client_id',Auth::guard('web')->user()->id)
            ->first();
        JobProposal::where('id',$id)->update(['is_view'=>1]);
        ClientNotification::where('client_id',Auth::guard('web')->user()->id)
            ->where('is_read','unread')
            ->where('identity',$id)
            ->where('type','Proposal')
            ->update(['is_read' => 'read']);
        return !empty($proposal_details) ? view('frontend.user.client.job.job-details.proposal-details',compact('proposal_details')) : back();
    }

    //add to shortlist
    public function add_remove_shortlist(Request $request)
    {
        $proposal = JobProposal::where('id',$request->proposal_id)->first();
        $is_short_listed = $proposal->is_short_listed == 0 ? 1 : 0;
        JobProposal::where('id',$request->proposal_id)->update(['is_short_listed'=>$is_short_listed]);
        return response()->json(['status'=>$is_short_listed]);
    }

    //filter job proposal
    public function job_proposal_filter(Request $request)
    {
        $job_proposals = JobProposal::with('job:id,type,hourly_rate,estimated_hours')->where('job_id',$request->job_id)->latest();

        if($request->filter_val == 'all'){
            $job_proposals = $job_proposals->get();
        }
        if($request->filter_val == 'hired'){
            $job_proposals = $job_proposals->where('is_hired',1)->get();
        }
        if($request->filter_val == 'shortlisted'){
            $job_proposals = $job_proposals->where('is_hired',0)->where('is_rejected',0)->where('is_short_listed',1)->get();
        }
        if($request->filter_val == 'interviewing'){
            $job_proposals = $job_proposals->where('is_hired',0)->where('is_short_listed',0)->where('is_rejected',0)->where('is_interview_take',1)->get();
        }
        return view('frontend.user.client.job.job-details.filter-proposals',compact('job_proposals'))->render();
    }

    //reject proposal
    public function reject_proposal(Request $request)
    {
        JobProposal::where('id',$request->proposal_id)->update(['is_rejected' => 1]);
        return response()->json(['status' => 1]);
    }

    //job open close
    public function open_close(Request $request)
    {
        $job = JobPost::where('id',$request->job_id)->first();
        $open_or_close = $job->on_off == 0 ? 1 : 0;
        JobPost::where('id',$request->job_id)->update(['on_off'=>$open_or_close]);
        return response()->json(['status'=>$open_or_close]);
    }

    public function rate_and_hours(Request $request)
    {
        $user_id = Auth::guard('web')->user()->id;
        $job = JobPost::where('id',$request->job_id)->where('user_id',$user_id)->first();
        if(!empty($job)){
          JobPost::where('id',$request->job_id)->update([
              'hourly_rate'=>$request->hourly_rate,
              'estimated_hours'=>$request->estimated_hour,
              ]);
            return back()->with(toastr_success(__('Hourly rate and hours updated successfully.')));
        }else{
            return back()->with(toastr_warning(__('Job not found!')));
        }
    }

    private function generate_unique_slug($slug, $job_id = null)
    {
        $counter = 1;
        $existingSlugs = JobPost::where('slug', 'like', $slug . '%')
            ->where('id', '!=', $job_id)
            ->pluck('slug')
            ->toArray();

        if (empty($existingSlugs)) {
            return $slug;
        }

        $newSlug = $slug;
        while (in_array($newSlug, $existingSlugs)) {
            $newSlug = $slug . '-' . $counter;
            $counter++;
        }

        return $newSlug;
    }
}
