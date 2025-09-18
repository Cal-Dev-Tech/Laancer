<?php

namespace App\Http\Controllers\Frontend\Freelancer;

use App\Events\AdminEvent;
use App\Helper\LogActivity;
use App\Http\Controllers\Controller;
use App\Mail\BasicMail;
use App\Models\AdminNotification;
use App\Models\Project;
use App\Models\ProjectAttribute;
use App\Models\ProjectSubCategory;
use App\Models\ProjectHistory;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Modules\CurrencySwitcher\App\Models\SelectedCurrencyList;
use Modules\Service\Entities\SubCategory;

class ProjectController extends Controller
{
    // project create
    public function create_project(Request $request)
    {
        if($request->isMethod('post'))
        {
            $slug_validation = moduleExists('CurrencySwitcher')
                ? 'required|max:191'
                : 'required|max:191|unique:projects,slug';

            $request->validate([
                'category'=>'required',
                'project_title'=>'required|min:20|max:100',
                'project_description'=>'required|min:50',
                'slug'=>$slug_validation,
                'image'=>'required|mimes:jpg,jpeg,png,bmp,tiff,svg,webp,gif, avif|max:5120',
                'basic_title'=>'required|max:191',
                'basic_regular_charge'=>'required|numeric|integer',
                'checkbox_or_numeric_title'=>'required|array|max:191',
                'meta_title'=>'nullable|max:255',
                'meta_description'=>'nullable|max:500',
            ]);

            if ($image = $request->file('image')) {
                $request->validate([
                    'image' => 'required|mimes:jpg,jpeg,png,bmp,tiff,svg|max:5120',
                ]);
            }

            if(get_static_option('project_auto_approval') == 'yes'){
                $project_auto_approval = 1;
                $project_approve_request = 1;
            }else{
                $project_auto_approval=0;
                $project_approve_request=0;
            }

            $standard_title = null;
            $premium_title = null;
            $standard_regular_charge = null;
            $standard_discount_charge = null;
            $premium_regular_charge = null;
            $premium_discount_charge = null;

            if($request->offer_packages_available_or_not == 1){
                $standard_title = $request->standard_title;
                $premium_title = $request->premium_title;
                $standard_regular_charge = $request->standard_regular_charge;
                $standard_discount_charge = $request->standard_discount_charge;
                $premium_regular_charge = $request->premium_regular_charge;
                $premium_discount_charge = $request->premium_discount_charge;
            }

            $basic_regular_charge = null;
            $basic_discount_charge = null;
            $user_id  = Auth::guard('web')->user()->id;
            $slug = !empty($request->slug) ? $request->slug : $request->project_title;

            if(moduleExists('CurrencySwitcher')){
                $slug = $this->generateUniqueSlug($request->slug ?? $request->project_title);
                $get_user_currency = SelectedCurrencyList::where('currency', get_currency_according_to_user())->first() ?? null;
                if(!empty($get_user_currency)){
                    $basic_regular_charge = $request->basic_regular_charge/$get_user_currency->conversion_rate;
                    $basic_discount_charge = $request->basic_discount_charge/$get_user_currency->conversion_rate;
                    $standard_regular_charge = $request->standard_regular_charge/$get_user_currency->conversion_rate;
                    $standard_discount_charge = $request->standard_discount_charge/$get_user_currency->conversion_rate;
                    $premium_regular_charge = $request->premium_regular_charge/$get_user_currency->conversion_rate;
                    $premium_discount_charge = $request->premium_discount_charge/$get_user_currency->conversion_rate;
                }
            }


            DB::beginTransaction();
            try {
                $imageName = '';
                $upload_folder = 'project';
                $storage_driver = Storage::getDefaultDriver();

                if ($image = $request->file('image')) {
                    $imageName = time().'-'.uniqid().'.'.$image->getClientOriginalExtension();
                    $resize_full_image = Image::make($request->image)
                        ->resize(750, 410);

                    if (cloudStorageExist() && in_array(Storage::getDefaultDriver(), ['s3', 'cloudFlareR2', 'wasabi'])) {
                        add_frontend_cloud_image_if_module_exists($upload_folder, $image, $imageName,'public');
                    }else{
                        $resize_full_image->save('assets/uploads/project' .'/'. $imageName);
                    }
                }

                $project = Project::create([
                    'user_id'=>$user_id,
                    'category_id'=>$request->category,
                    'title'=>$request->project_title,
                    'slug' => Str::slug(purify_html($slug),'-',null),
                    'description'=>$request->project_description,
                    'image'=>$imageName,
                    'basic_title'=>$request->basic_title,
                    'standard_title'=>$standard_title,
                    'premium_title'=>$premium_title,
                    'basic_revision'=>$request->basic_revision,
                    'standard_revision'=>$request->standard_revision,
                    'premium_revision'=>$request->premium_revision,
                    'basic_delivery'=>$request->basic_delivery,
                    'standard_delivery'=>$request->standard_delivery,
                    'premium_delivery'=>$request->premium_delivery,
                    'basic_regular_charge'=>$basic_regular_charge ?? $request->basic_regular_charge,
                    'basic_discount_charge'=>$basic_discount_charge ?? $request->basic_discount_charge,
                    'standard_regular_charge'=>$standard_regular_charge,
                    'standard_discount_charge'=>$standard_discount_charge,
                    'premium_regular_charge'=>$premium_regular_charge,
                    'premium_discount_charge'=>$premium_discount_charge,
                    'project_on_off'=>1,
                    'status'=>$project_auto_approval,
                    'project_approve_request'=>$project_approve_request,
                    'offer_packages_available_or_not'=>$request->offer_packages_available_or_not,
                    'meta_title'=>$request->meta_title,
                    'meta_description'=>$request->meta_description,
                    'load_from' => in_array($storage_driver,['CustomUploader']) ? 0 : 1, //added for cloud storage 0=local 1=cloud
                ]);
                $project->project_sub_categories()->attach($request->subcategory);

                $arr = [];
                foreach($request->checkbox_or_numeric_title as $key => $attr):
                    $attr_value = preg_replace('/[^a-z0-9_]/', '_', strtolower($attr));
                    $field_type = $request->checkbox_or_numeric_select[$key] ?? 'checkbox';

                    switch($field_type) {
                        case 'checkbox':
                            $fallback_value = "off";
                            break;
                        case 'numeric':
                            $fallback_value = 0;
                            break;
                        case 'text':
                            $fallback_value = "";
                            break;
                        default:
                            $fallback_value = "off";
                    }

                    $basic_price = $request->$attr_value["basic_price"] ?? null;
                    $standard_price = $request->$attr_value["standard_price"] ?? null;
                    $premium_price = $request->$attr_value["premium_price"] ?? null;
                    $is_paid = ($basic_price || $standard_price || $premium_price) ? 1 : 0;


                    $arr[] = [
                        'user_id' => $user_id,
                        'create_project_id' => $project->id,
                        'check_numeric_title' => $attr,
                        'basic_check_numeric' => $request->$attr_value["basic"] ?? $fallback_value,
                        'standard_check_numeric' => $request->$attr_value["standard"] ?? $fallback_value,
                        'premium_check_numeric' => $request->$attr_value["premium"] ?? $fallback_value,
                        'basic_extra_price' => $basic_price,
                        'standard_extra_price' => $standard_price,
                        'premium_extra_price' => $premium_price,
                        'is_paid' => $is_paid,
                        'type' => $request->checkbox_or_numeric_select[$key] ?? null,
                        'created_at'=> date('Y-m-d H:i:s'),
                        'updated_at'=> date('Y-m-d H:i:s'),
                    ];
                endforeach;

                $data = Validator::make($arr,["*.basic_check_numeric" => "nullable"]);
                $data->validated();

                ProjectAttribute::insert($arr);


                // Generate meta title
                $meta_title = $request->meta_title ?? $request->project_title;
                $meta_title = strlen($meta_title) > 60 ? substr($meta_title, 0, 57) . '...' : $meta_title;
                
                // Generate meta description
                $meta_description = $request->meta_description ?? $request->project_description;
                $meta_description = strlen($meta_description) > 160 
                    ? substr(strip_tags($meta_description), 0, 157) . '...' 
                    : strip_tags($meta_description);
                
                // Generate keywords from title
                $title_words = explode(' ', strtolower($request->project_title));
                $keywords = collect($title_words)
                    ->filter(function($word) {
                        // Filter out common stop words
                        $stopWords = ['a', 'an', 'and', 'are', 'as', 'at', 'be', 'by', 'for', 'from', 'has', 'he', 'in', 'is', 'it', 'its', 'of', 'on', 'that', 'the', 'to', 'was', 'will', 'with'];
                        return strlen(trim($word)) > 2 && !in_array(trim($word), $stopWords);
                    })
                    ->map(fn($word) => trim($word))
                    ->unique()
                    ->take(10)
                    ->implode(', ');

                $image_url = $imageName ? asset('assets/uploads/project/' . $imageName) : null;
                
                // Create comprehensive meta data
                $metaData = [
                    'meta_title' => purify_html($meta_title),
                    'meta_description' => purify_html($meta_description),
                    'meta_tags' => purify_html($keywords),
                    
                    'facebook_meta_tags' => purify_html($keywords),
                    'facebook_meta_description' => purify_html($meta_description),
                    'facebook_meta_image' => $image_url,
                    
                    'twitter_meta_tags' => purify_html($keywords),
                    'twitter_meta_description' => purify_html($meta_description),
                    'twitter_meta_image' => $image_url,
                ];
                
                
                $project->metaData()->create($metaData);

                //security manage
                if(moduleExists('SecurityManage')){
                    LogActivity::addToLog('Project create','Freelancer');
                }

                DB::commit();
            }catch(Exception $e){

                DB::rollBack();

                if ($request->file('image')) {
                    $delete_img = 'assets/uploads/project/'.$imageName;
                    File::delete($delete_img);
                }

                toastr_error(__('Basic check numeric field is required'));
                return back();
            }

            try {
                $message = get_static_option('project_create_email_message') ?? __('A new project is just created.');
                $message = str_replace(["@project_title"],[$project->title], $message);
                Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                    'subject' => get_static_option('project_create_email_subject') ?? __('Project Create Email'),
                    'message' => $message
                ]));
            }catch (\Exception $e) {}

            //create project notification to admin
            AdminNotification::create([
                'identity'=>$project->id,
                'user_id'=>$user_id,
                'type'=>'Create Project',
                'message'=>__('A new project has been created'),
            ]);
            event(new AdminEvent(__('A project has been created.')));
            toastr_success(__('Project Successfully Created'));
            return redirect()->route('freelancer.profile.details', Auth::guard('web')->user()->username);
        }

        return view('frontend.user.freelancer.project.create.create-project');
    }

    // project edit
    public function edit_project(Request $request, $id)
    {
        $project_details = Project::with('project_attributes')
            ->where('user_id',Auth::guard('web')->user()->id)
            ->where('id',$id)->first();
        $get_sub_categories_from_project_category = SubCategory::where('category_id',$project_details->category_id)->get() ?? '';

        if($request->isMethod('post'))
        {
            $slug_validation = moduleExists('CurrencySwitcher')
                ? 'required|max:191'
                : 'required|max:191|unique:projects,slug,'.$id;

            $regular_charge_validation = moduleExists('CurrencySwitcher')
                ? 'required|numeric'
                : 'required|numeric|integer';

            $request->validate([
                'project_title'=>'required|min:20|max:100|unique:projects,title,'.$id,
                'project_description'=>'required|min:50',
                'slug'=>$slug_validation,
                'basic_title'=>'required|max:191',
                'basic_regular_charge'=>$regular_charge_validation,
                'checkbox_or_numeric_title'=>'required|array|max:191',
                'meta_title'=>'nullable|max:255',
                'meta_description'=>'nullable|max:500',
            ]);
            
            if ($image = $request->file('image')) {
                $request->validate([
                    'image'=>'required|mimes:jpg,jpeg,png,bmp,tiff,svg,webp,gif, avif|max:5120',
                ]);
            }

            $standard_title = null;
            $premium_title = null;
            $standard_regular_charge = null;
            $standard_discount_charge = null;
            $premium_regular_charge = null;
            $premium_discount_charge = null;
            $basic_regular_charge = null;
            $basic_discount_charge = null;

            if($request->offer_packages_available_or_not == 1){
                $standard_title = $request->standard_title;
                $premium_title = $request->premium_title;
                $standard_regular_charge = $request->standard_regular_charge;
                $standard_discount_charge = $request->standard_discount_charge;
                $premium_regular_charge = $request->premium_regular_charge;
                $premium_discount_charge = $request->premium_discount_charge;
            }

            $user_id  = Auth::guard('web')->user()->id;
            $slug = !empty($request->slug) ? $request->slug : $request->project_title;

            if(moduleExists('CurrencySwitcher')){
                $slug = $this->generateUniqueSlug($request->slug ?? $request->project_title);
                $get_user_currency = SelectedCurrencyList::where('currency', get_currency_according_to_user())->first() ?? null;
                if(!empty($get_user_currency)){
                    $basic_regular_charge = $request->basic_regular_charge/$get_user_currency->conversion_rate;
                    $basic_discount_charge = $request->basic_discount_charge/$get_user_currency->conversion_rate;
                    $standard_regular_charge = $request->standard_regular_charge/$get_user_currency->conversion_rate;
                    $standard_discount_charge = $request->standard_discount_charge/$get_user_currency->conversion_rate;
                    $premium_regular_charge = $request->premium_regular_charge/$get_user_currency->conversion_rate;
                    $premium_discount_charge = $request->premium_discount_charge/$get_user_currency->conversion_rate;
                }
            }

            $delete_old_img =  'assets/uploads/project/'.$project_details->image;
            DB::beginTransaction();
            try {
                $imageName = '';
                $upload_folder = 'project';

                if (cloudStorageExist() && in_array(Storage::getDefaultDriver(), ['s3', 'cloudFlareR2', 'wasabi'])) {
                    if ($image = $request->file('image')) {
                        // $request->validate([
                        //     'image'=>'required|mimes:jpg,jpeg,png,bmp,tiff,svg|max:5120',
                        // ]);
                        $imageName = time().'-'.uniqid().'.'.$image->getClientOriginalExtension();

                        // Get the current image path from the database
                        $currentImagePath = $project_details->image;
                        // Delete the old image if it exists
                        if ($currentImagePath) {
                            delete_frontend_cloud_image_if_module_exists('project/'.$currentImagePath);
                        }
                        add_frontend_cloud_image_if_module_exists($upload_folder, $image, $imageName,'public');
                    }else{
                        $imageName = $project_details->image;
                    }
                }else{
                    if ($image = $request->file('image')) {
                        $request->validate([
                            'image'=>'required|mimes:jpg,jpeg,png,bmp,tiff,svg,webp,gif, avif|max:5120',
                        ]);
                        if(file_exists($delete_old_img)){
                            File::delete($delete_old_img);
                        }
                        $imageName = time().'-'.uniqid().'.'.$image->getClientOriginalExtension();
                        $resize_full_image = Image::make($request->image)
                            ->resize(750, 410);
                        $resize_full_image->save('assets/uploads/project' .'/'. $imageName);
                    }else{
                        $imageName = $project_details->image;
                    }
                }


                Project::where('id',$id)->update([
                    'user_id'=>$user_id,
                    'category_id'=>$request->category,
                    'title'=>$request->project_title,
                    'slug' => Str::slug(purify_html($slug),'-',null),
                    'description'=>$request->project_description,
                    'image'=>$imageName,
                    'basic_title'=>$request->basic_title,
                    'standard_title'=>$standard_title,
                    'premium_title'=>$premium_title,
                    'basic_revision'=>$request->basic_revision,
                    'standard_revision'=>$request->standard_revision,
                    'premium_revision'=>$request->premium_revision,
                    'basic_delivery'=>$request->basic_delivery,
                    'standard_delivery'=>$request->standard_delivery,
                    'premium_delivery'=>$request->premium_delivery,
                    'basic_regular_charge'=>$basic_regular_charge ?? $request->basic_regular_charge,
                    'basic_discount_charge'=>$basic_discount_charge ?? $request->basic_discount_charge,
                    'standard_regular_charge'=>$standard_regular_charge,
                    'standard_discount_charge'=>$standard_discount_charge,
                    'premium_regular_charge'=>$premium_regular_charge,
                    'premium_discount_charge'=>$premium_discount_charge,
                    'project_on_off'=>1,
                    'project_approve_request'=> $project_details->project_approve_request == 1 ? 1 : 0,
                    'status'=> get_static_option('project_auto_approval') == 'yes' ? 1 : 0,
                    'offer_packages_available_or_not'=> $request->offer_packages_available_or_not ?? 0,
                    'meta_title'=>$request->meta_title,
                    'meta_description'=>$request->meta_description,
                ]);

                //update product pivot table data
                $project = Project::find($id);
                $project->project_sub_categories()->sync($request->subcategory);

                ProjectAttribute::where('create_project_id',$id)->delete();

                $arr = [];
                foreach($request->checkbox_or_numeric_title as $key => $attr):
                    $attr_value = preg_replace('/[^a-z0-9_]/', '_', strtolower($attr));
                    $field_type = $request->checkbox_or_numeric_select[$key] ?? 'checkbox';

                    switch($field_type) {
                        case 'checkbox':
                            $fallback_value = "off";
                            break;
                        case 'numeric':
                            $fallback_value = 0;
                            break;
                        case 'text':
                            $fallback_value = "";
                            break;
                        default:
                            $fallback_value = "off";
                    }

                    $basic_price = $request->$attr_value["basic_price"] ?? null;
                    $standard_price = $request->$attr_value["standard_price"] ?? null;
                    $premium_price = $request->$attr_value["premium_price"] ?? null;
                    $is_paid = ($basic_price || $standard_price || $premium_price) ? 1 : 0;

                    $arr[] = [
                        'user_id' => $user_id,
                        'create_project_id' => $id,
                        'check_numeric_title' => $attr,
                        'basic_check_numeric' => $request->$attr_value["basic"] ?? $fallback_value,
                        'standard_check_numeric' => $request->$attr_value["standard"] ?? $fallback_value,
                        'premium_check_numeric' => $request->$attr_value["premium"] ?? $fallback_value,
                        'basic_extra_price' => $basic_price,
                        'standard_extra_price' => $standard_price,
                        'premium_extra_price' => $premium_price,
                        'is_paid' => $is_paid,
                        'type' => $request->checkbox_or_numeric_select[$key] ?? null,
                        'updated_at'=> date('Y-m-d H:i:s'),
                    ];

                endforeach;

                $data = Validator::make($arr,["*.basic_check_numeric" => "nullable"]);
                $data->validated();

                ProjectAttribute::insert($arr);

                $project_id_from_project_history_table = ProjectHistory::where('project_id', $id)->first();

                if(empty($project_id_from_project_history_table)){
                    ProjectHistory::Create([
                        'project_id'=>$project->id,
                        'user_id'=>$project->user_id,
                        'reject_count'=>0,
                        'edit_count'=>1,
                    ]);
                }else{
                    ProjectHistory::where('project_id',$id)->update([
                        'reject_count'=>$project_id_from_project_history_table->edit_count + 1
                    ]);
                }

                //security manage
                if(moduleExists('SecurityManage')){
                    LogActivity::addToLog('Project edit','Freelancer');
                }

                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                if ($request->file('image')) {
                    $delete_img = 'assets/uploads/project/'.$imageName;
                    File::delete($delete_img);
                }
                toastr_error(__('Basic check numeric field is required'));
                return back();
            }

            try {
                $message = get_static_option('project_edit_email_message') ?? __('A new project is just edited.');
                $message = str_replace(["@project_title"],[$project->title], $message);
                Mail::to(get_static_option('site_global_email'))->send(new BasicMail([
                    'subject' => get_static_option('project_edit_email_subject') ?? __('Project Edit Email'),
                    'message' => $message
                ]));
            }catch (\Exception $e) {}

            //edit project notification to admin
            AdminNotification::create([
                'identity'=>$id,
                'user_id'=>$user_id,
                'type'=>'Edit Project',
                'message'=>__('A project has been edited.'),
            ]);
            event(new AdminEvent(__('A project has been edited.')));


            toastr_success(__('Project Successfully Updated'));
            return redirect()->route('freelancer.profile.details', Auth::guard('web')->user()->username);
        }

        return view('frontend.user.freelancer.project.edit.edit-project',compact('project_details','get_sub_categories_from_project_category'));
    }

    // project preview
    public function project_preview()
    {
        $all_projects = Project::with('project_attributes')->where('user_id',Auth::guard('web')->user()->id)->latest()->get();
        return view('frontend.user.freelancer.project.preview.all-projects',compact('all_projects'));
    }

    // project description

    public function project_description(Request $request)
    {
        if($request->ajax()){
            $project_title_and_description = Project::select(['title','description'])->where('id',$request->project_id)->first();
            return view('frontend.user.freelancer.project.preview.project-description',compact('project_title_and_description'))->render();
        }
    }

    // project delete
    public function delete_project(Request $request)
    {
       $project = Project::findOrFail($request->project_id);
       ProjectAttribute::where('create_project_id',$project->id)->delete();
       ProjectHistory::where('project_id',$project->id)->delete();
        $project->delete();
        return response()->json(['status'=>'success']);
    }


    private function generateUniqueSlug($slug, $project_id = null)
    {
        $counter = 1;
        $existingSlugs = Project::where('slug', 'like', $slug . '%')
            ->where('id', '!=', $project_id)
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
