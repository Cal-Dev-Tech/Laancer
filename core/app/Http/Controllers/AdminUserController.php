<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CountryManage\Entities\City;
use Modules\CountryManage\Entities\State;
use Modules\Service\Entities\SubCategory;

class AdminUserController extends Controller
{
    // get state
    public function get_country_state(Request $request)
    {
        $states = State::where('country_id', $request->country)->where('status', 1)->get();
        return response()->json([
            'status' => 'success',
            'states' => $states,
        ]);
    }

    // get city
    public function get_state_city(Request $request)
    {
        $cities = City::where('state_id', $request->state)->where('status', 1)->get();
        return response()->json([
            'status' => 'success',
            'cities' => $cities,
        ]);
    }

    // get subcategory
    public function get_subcategory(Request $request)
    {
        $subcategories = SubCategory::where('category_id', $request->category)->where('status', 1)->get();
        return response()->json([
            'status' => 'success',
            'subcategories' => $subcategories,
        ]);
    }

    // get skills by category
    public function getSkillsByCategory(Request $request)
    {
        try {
            $categoryId = $request->category;

            if (!$categoryId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Category ID is required'
                ]);
            }

            $skills = \App\Models\Skill::where('category_id', $categoryId)
                ->select('id', 'skill', 'category_id')
                ->get();

            return response()->json([
                'status' => 'success',
                'skills' => $skills
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to load skills',
                'error' => $e->getMessage()
            ]);
        }
    }
}
