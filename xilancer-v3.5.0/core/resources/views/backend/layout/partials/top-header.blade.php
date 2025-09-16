<div class="dashboard__header">
    <div class="dashboard__header__flex">
        <div class="dashboard__header__left">
            <h4 class="dashboard__header__title">{{ Auth::guard('admin')->user()->name ?? '' }}</h4>
            <a href="{{ url('/') }}" class="dashboard__header__para mt-2">{{ __('Here\'s what\'s going on in') }} {{ get_static_option('site_title') }}</a>
        </div>
        <div class="dashboard__header__right">
            <div class="dashboard__header__right__flex">
                <div class="dashboard__header__right__item">
                    <a target="_blank" href="{{ url('/') }}" class="visitSite__btn">{{ __('Visit Site') }}</a>
                </div>
                <div class="dashboard__header__right__item">
                    @if(moduleExists('CurrencySwitcher'))
                        <div class="navbar-right-item">
                            <div class="navbar-author">
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
                                                            <option value="" disable>{{ __('Select Currency') }}</option>
                                                            @foreach($currency_list as $list)
                                                                <option value="{{ $list->currency }}" @if($userCurrency == $list->currency) selected @endif>
                                                                    {{ $list->currency .'('. $list->symbol.')' }}
                                                                </option>
                                                            @endforeach

                                                            @if(empty($userCurrency) && !empty(get_static_option('site_global_currency')))
                                                                <option value="" selected>{{ get_static_option('site_global_currency') .'('.site_currency_symbol().')' }}</option>
                                                            @endif
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
                    @endif
                </div>
                <div class="dashboard__header__right__item">
                    <div class="dashboard__author">
                        <a href="javascript:void(0)" class="dashboard__author__flex flex-btn">
                            <div class="dashboard__author__thumb">
                                @if(Auth::check() && Auth::guard('admin')->user()->image)
                                    {!! render_image_markup_by_attachment_id(Auth::guard('admin')->user()->image,'','thumb') !!}
                                @else
                                    <img src="{{ asset('assets/static/img/admin/admin.jpg') }}" alt="{{ __('authorImg') }}">
                                @endif
                            </div>
                        </a>
                        <div class="dashboard__author__wrapper">
                            <div class="dashboard__author__wrapper__list">
                                @if(Auth::check() && Auth::guard('admin')->user()->role == '1')
                                <a href="{{ route('admin.all') }}" class="dashboard__author__wrapper__list__item"><i class="fa-solid fa-gear"></i>{{ __('Admin settings') }}</a>
                                @endif
                                    <a href="{{ route('admin.update.info') }}" class="dashboard__author__wrapper__list__item"><i class="fa-solid fa-info"></i>{{ __('Update Info') }}</a>
                                    <a href="{{ route('admin.logout') }}" class="dashboard__author__wrapper__list__item"><i class="fa-solid fa-arrow-right-from-bracket"></i>{{ __('Log Out') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dashboard__header__right__item">
                    <div class="dashboard__notification">
                        <a href="javascript:void(0)" class="dashboard__notification__icon">
                                <i class="fa-solid fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ App\Models\AdminNotification::unread_notification()->count() }}</span>
                        </a>
                        <div class="dashboard__notification__wrapper">
                            <div class="dashboard__notification__list">
                                @foreach(App\Models\AdminNotification::unread_notification() as $notification)
                                  <x-backend.admin-notification :notification="$notification"/>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
