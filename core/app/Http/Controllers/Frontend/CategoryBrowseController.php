<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Modules\Service\Entities\Category;

class CategoryBrowseController extends Controller
{
    public function browse()
    {
        $categories = Category::with(['sub_categories' => function($q){
            $q->orderBy('sub_category');
        }])
            ->where('status', 1)
            ->orderBy('category')
            ->get();

        return view('frontend.pages.category-browse', compact('categories'));
    }
}


