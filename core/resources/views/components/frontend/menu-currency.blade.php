<div class="navbar-right-item">
    <div class="navbar-author">
        <a href="javascript:void(0)" class="navbar-author-flex flex-btn navbar-author-link">
            <div class="navbar-author-thumb">
                <a href="javascript:void(0)" class="navbar-right-notification-icon-currency">
                    <i class="fa-solid fa-globe"></i>
                </a>
            </div>
        </a>
        <div class="navbar-author-wrapper currency_switcher_area_for_css">
            <div class="navbar-author-wrapper-list">
                @if(moduleExists('CurrencySwitcher'))
                    @php
                        $currency_list = \Modules\CurrencySwitcher\App\Models\SelectedCurrencyList::where('status',1)->get();
                        $userCurrency = Session::get('user_current_currency') ?? get_currency_according_to_user();
                    @endphp
                    <div class="currency_switcher_area">
                        <div>
                            <select class="btn-profile btn-bg-1" id="currency_switch">
                                @if(!empty($currency_list->count()) > 0)
                                    <option value="" disabled>{{ __('Select Currency') }}</option>
                                    @foreach($currency_list as $list)
                                        <option value="{{ $list->currency }}" @if($userCurrency == $list->currency) selected @endif>
                                            {{ $list->currency .'('. $list->symbol.')' }}
                                        </option>
                                    @endforeach

                                    @if(empty($userCurrency) && !empty(get_static_option('site_global_currency')))
                                        <option value="" selected>{{ get_static_option('site_global_currency') .'('.site_currency_symbol().')' }}</option>
                                    @endif
{{--                                    @foreach($currency_list as $list)--}}
{{--                                        @if(!empty(Session::get('user_current_currency')))--}}
{{--                                            <option value="{{ $list->currency }}" @if(Session::get('user_current_currency') == $list->currency) selected  @endif>{{ $list->currency .'('. $list->symbol.')' }}</option>--}}
{{--                                        @else--}}
{{--                                            <option value="{{ $list->currency }}" @if(get_currency_according_to_user() == $list->currency) selected  @endif>{{ $list->currency .'('. $list->symbol.')' }}</option>--}}
{{--                                        @endif--}}
{{--                                    @endforeach--}}

{{--                                    @if(!empty(get_static_option('site_global_currency')))--}}
{{--                                        <option value="" selected>{{ get_static_option('site_global_currency') .'('.site_currency_symbol().')' }}</option>--}}
{{--                                    @endif--}}
                                @else
                                    <option value="">{{ __('No Available Currency') }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>