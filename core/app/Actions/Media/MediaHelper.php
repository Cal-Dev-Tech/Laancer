<?php

namespace App\Actions\Media;

use App\Http\Services\HandleImageUploadService;
use App\Models\MediaUpload;
use App\Models\MediaUploader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class MediaHelper
{

    public static function fetch_media_image($request,$type='admin')
    {
        $image_query = MediaUpload::query();

        if ($type === 'web'){
            $image_query->where(['user_id' => auth($type)->id()]);
        }
        $all_images = $image_query->where(['type' => $type])->orderBy('id', 'DESC')->take(20)->get();
        $selected_image = MediaUpload::find($request->selected);

        $all_image_files = [];
        foreach ($all_images as $image){
            if (!is_null($selected_image) && $image->id === $selected_image->id){
                continue;
            }

            if (self::check_file_exists('media-uploader/' . $image->path, load_from: $image->load_from)){
                $image_url = self::getUploadAssetPath('media-uploader/' . $image->path, load_from: $image->load_from);

                $all_image_files[] = [
                    'image_id' => $image->id,
                    'title' => $image->title,
                    'dimensions' => $image->dimensions,
                    'alt' => $image->alt,
                    'size' => $image->size,
                    'type' => pathinfo($image_url,PATHINFO_EXTENSION),
                    'path' => $image->path,
                    'user_type' => $image->type === 0 ? 'admin' : 'user',
                    'img_url' => $image_url,
                    'upload_at' => date_format($image->created_at, 'd M y')
                ];

            }else{
                //delete assets as well
                self::deleteOldFile($image->path);
                MediaUpload::find($image->id)->delete();
            }
        }

        return $all_image_files;
    }

    private static function check_file_exists($path, $load_from = 0) : bool
    {
        $file_path = self::getUploadBasePath($path);

        try {
            $driver = Storage::getDefaultDriver();

            // 0 means local
//            if ($load_from === 0){
//                $driver = "CustomUploader";
//            }

            return Storage::disk($driver)->fileExists($path);
        }catch (\Exception $e){
            return "";
        }
    }

    private static function getUploadBasePath($path = '', $load_from = 0) :string
    {
        return Storage::renderUrl($path, load_from:$load_from);
    }

    private static function getUploadAssetPath($path = '', $load_from=0) :string
    {
        return Storage::renderUrl($path, load_from: $load_from);
    }

    public static function delete_media_image($request,$type ='admin')
    {
        $get_image_details = MediaUpload::find($request->img_id);
        $image_query = MediaUpload::query();
        if ($type === 'web'){
            $image_query->where(['type' => $type,'user_id' => auth($type)->id()]);
        }

        self::deleteOldFile($get_image_details->path);
        $get_image_details->delete();
        return redirect()->back();
    }

    public static function insert_media_image($request,$type='admin',$file_field_name = 'file'){

        if ($request->hasFile($file_field_name)) {
            $image = $request->$file_field_name;
            $image_dimension_for_db = '';
            $image_extenstion = $image->getClientOriginalExtension();
            $image_name_with_ext = $image->getClientOriginalName();
            $image_name = pathinfo($image_name_with_ext,PATHINFO_FILENAME);
            $folder_path = 'assets/uploads/media-uploader/';

            if($image_extenstion == 'svg'){
                $image_name_with_ext = $image->getClientOriginalName();
                $image_db = $image_name . time() . '.' . $image_extenstion;
                $request->$file_field_name->move($folder_path, $image_db);
                $image_size_for_db = '';

                return MediaUpload::create([
                    'title' => $image_name_with_ext,
                    'size' => '',
                    'path' => $image_db,
                    'dimensions' => $image_dimension_for_db,
                    'type' => $type,
                    'user_id' => auth($type)->id(),
                ]);
            }

            if($image_extenstion == 'mp4'){
                $image_name_with_ext = $image->getClientOriginalName();
                $image_db = $image_name . time() . '.' . $image_extenstion;
                $request->$file_field_name->move($folder_path, $image_db);
                $image_size_for_db = '';

                return MediaUpload::create([
                    'title' => $image_name_with_ext,
                    'size' => '',
                    'path' => $image_db,
                    'dimensions' => $image_dimension_for_db,
                    'type' => $type,
                    'user_id' => auth($type)->id(),
                ]);
            }

            $image_dimension = getimagesize($image);
            $image_extenstion = $image->getClientOriginalExtension();
            $image_name_with_ext = $image->getClientOriginalName();
            $image_width = $image_dimension[0];
            $image_height = $image_dimension[1];
            $image_dimension_for_db = $image_width . ' x ' . $image_height . ' pixels';
            $image_size_for_db = $image->getSize();

            $image_db = $image_name . time() . '.' . $image_extenstion;
            $upload_folder = 'media-uploader';
            $storage_driver = Storage::getDefaultDriver();

            if (cloudStorageExist() && in_array(Storage::getDefaultDriver(),['s3','cloudFlareR2','wasabi'])){
                Storage::putFileAs($upload_folder, $image, $image_db,'public');
            }else{
                $resize_full_image = Image::make($request->$file_field_name)
                    ->resize($image_width, $image_height,function ($constraint) {
                        $constraint->aspectRatio();
                    });
                $resize_full_image->save($folder_path .'/'. $image_db);
            }

            MediaUpload::create([
                'title' => $image_name_with_ext,
                'size' => formatBytes($image_size_for_db),
                'path' => $image_db,
                'dimensions' => $image_dimension_for_db,
                'type' => $type,
                'user_id' => auth($type)->id(),
                'load_from' => in_array($storage_driver,['CustomUploader']) ? 0 : 1, //added for cloud storage
            ]);
        }
    }

    public static function load_more_images($request,$type = 'admin'){

        $image_query = MediaUpload::query();

        if ($type === 'web'){
            $image_query->where(['type' => $type,'user_id' => auth($type)->id()]);
        }

        $all_images = $image_query->orderBy('id', 'DESC')->skip($request->skip)->take(20)->get();

        $all_image_files = [];
        foreach ($all_images as $image){
            if (file_exists('assets/uploads/media-uploader/'.$image->path)){
                $image_url = asset('assets/uploads/media-uploader/'.$image->path);
                if (file_exists('assets/uploads/media-uploader/grid-' . $image->path)) {
                    $image_url = asset('assets/uploads/media-uploader/grid-' . $image->path);
                }
                $all_image_files[] = [
                    'image_id' => $image->id,
                    'title' => $image->title,
                    'dimensions' => $image->dimensions,
                    'alt' => $image->alt,
                    'size' => $image->size,
                    'path' => $image->path,
                    'img_url' => $image_url,
                    'upload_at' => date_format($image->created_at, 'd M y')
                ];

            }
        }
        return $all_image_files;
    }

    private static function deleteOldFile($path) : void
    {
        $driver = get_static_option('storage_driver');
        if (in_array($driver, ['wasabi', 's3', 'cloudFlareR2'])) {
            self::deleteCloudUploadedFile($driver,'media-uploader/'. $path);
        } else {
            @unlink(self::getUploadBasePath('media-uploader/'.$path));
        }
    }

    private static function deleteCloudUploadedFile($driver, $path)
    {
        return Storage::disk($driver)->delete($path);
    }

    public function createRandomImageAndReturnId(int $height, int $width, string $imgCategory = 'random', string $userType = 'user'): ?int
    {
        if ($height <= 0 || $width <= 0 || $height > 1080 || $width > 1920) {
            Log::error("Invalid dimensions: Height - {$height}, Width - {$width}");
            toastr_error(__('Invalid image dimensions provided.'));
            return null;
        }

        $retries = 0;
        $max_retries = 3;
        while ($retries < $max_retries) {
            $image_url = "https://picsum.photos/{$width}/{$height}";
            $response = Http::get($image_url);

            if ($response->successful()) {
                break;
            }
            $retries++;
        }

        if (!$response->successful()) {
            Log::error("Failed to fetch image from URL: {$image_url}");
            toastr_warning(__('An error occured while fetching the image. Please try again.'));
            return null;
        }

        $folder_path = 'assets/uploads/media-uploader/';
        if (!File::exists($folder_path)) {
            File::makeDirectory($folder_path, 0755, true);
        }

        $image_name = $imgCategory . '_image_' . time();
        $image_extension = 'jpg';
        $image_name_with_ext = $image_name . '.' . $image_extension;
        $image_path = $folder_path . $image_name_with_ext;

        try {
            file_put_contents($image_path, $response->body());

            if (!file_exists($image_path)) {
                Log::error("Failed to save image: {$image_path}");
                toastr_error(__('Unable to save the image. Please try again.'));
                return null;
            }

            $image_size_for_db = filesize($image_path);
            $image_dimension = getimagesize($image_path);
            $image_width = $image_dimension[0];
            $image_height = $image_dimension[1];
            $image_dimension_for_db = "{$image_width} x {$image_height} pixels";

            $resize_full_image = Image::make($image_path)
                ->resize($image_width, $image_height, function ($constraint) {
                    $constraint->aspectRatio();
                });
            $resize_full_image->save($image_path);

            $image = MediaUpload::create([
                'title' => $image_name_with_ext,
                'size' => formatBytes($image_size_for_db),
                'path' => $image_name_with_ext,
                'dimensions' => $image_dimension_for_db,
                'type' => $userType,
                'user_id' => auth()->id(),
                'load_from' => in_array(Storage::getDefaultDriver(), ['CustomUploader']) ? 0 : 1,
            ]);

            return $image->id;
        } catch (\Exception $e) {
            Log::error("Error while storing random image: " . $e->getMessage(), ['image_url' => $image_url]);
            toastr_error(__('Some error occurred while processing the image. Please try again.'));
            return null;
        }
    }
}
