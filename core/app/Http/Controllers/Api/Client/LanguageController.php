<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use Modules\CurrencySwitcher\App\Models\SelectedCurrencyList;

class LanguageController extends Controller
{
    public function all_language()
    {
        $is_rtl_on_or_not = get_user_lang_direction() == 1 ?? false;
        $active_currency_list = null;
        if(moduleExists('CurrencySwitcher')){
            $active_currency_list = SelectedCurrencyList::select('currency','symbol','conversion_rate','currency_symbol_position')->where('status', 1)->get();
        }

        return response()->json([
            "symbol" => site_currency_symbol(),
            "currencyPosition" => get_static_option('site_currency_symbol_position'),
            "rtl" => $is_rtl_on_or_not,
            "currency_code" => get_static_option("site_global_currency"),
            "language" => Language::where("default",1)->first(),
            "active_currency_list" => $active_currency_list,
        ]);
    }

    //string translate
    public function string_translate(Request $request)
    {
        $translateable_array = json_decode($request->post('strings'),true);
        $translated_array = [];
        if($request->has('strings')){
            foreach($translateable_array as $key => $string){
                $translated_array[$key] = __($key);
            }
        }

        return response()->json([
            'strings'=> $translated_array
        ]);
    }
}
