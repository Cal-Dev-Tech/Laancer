<!-- Package & Charge Start -->
<div class="setup-wrapper-contents">
    <div class="create-project-wrapper-item">
        <div class="create-project-wrapper-item-top profile-border-bottom">
            <h4 class="create-project-wrapper-title"> {{ __('Package & Charge') }} </h4>
            <div class="custom_switch_wrapper">
                <label class="custom_switch">
                    <input class="custom-switch" type="checkbox" name="offer_packages_available_or_not" id="offer_packages_available_or_not" @checked($project_details->offer_packages_available_or_not != 0) value="1">
                    <span class="switch-label slider round" for="offer_packages_available_or_not"></span>
                </label>
                <span><strong>{{ __('info:') }}</strong> {{ __('offer package enable disable') }}</span>
            </div>
        </div>
        <div class="package-contents">
            <div class="package-table">

                <table class="table table-bordered table-responsive create_project_table">
                    <thead>
                    <tr>
                        <th class="package-head">
                        </th>
                        <th class="package-head">
                            <div class="package-head-flex flex-between align-items-center">
                                <span class="package-head-title" id="basic_title" data-title="Basic">{{ __($project_details->basic_title) ?? __('Basic') }}</span>
                                <span class="package-head-edit"></span>
                            </div>
                        </th>
                        <th class="package-head">
                            <div class="package-head-flex flex-between align-items-center">
                                <span class="package-head-title" id="standard_title" data-title="Standard">{{ __($project_details->standard_title) ?? __('Standard') }}</span>
                                <span class="package-head-edit"></span>
                            </div>
                        </th>
                        <th class="package-head">
                            <div class="package-head-flex flex-between align-items-center">
                                <span class="package-head-title" id="premium_title" data-title="Premium">{{ __($project_details->premium_title) ?? __('Premium') }}</span>
                                <span class="package-head-edit"></span>
                            </div>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="add-rows-parent">
                    <tr>
                        <th>
                            <div class="package-head-left">
                                <span class="package-head-left-title">{{ __('Revisions') }}</span>
                            </div>
                        </th>
                        <td>
                            <div class="package-field">
                                <div class="package-select">
                                    <select class="form-control" name="basic_revision" id="basic_revision">
                                        @for($i = 1; $i<=10; $i++)
                                            <option value="{{ $i }}" @if($project_details->basic_revision == $i) selected @endif>{{ $i }}</option>
                                        @endfor
                                        <option value="1000" @if($project_details->basic_revision == '1000') selected @endif>{{ __('Unlimited') }}</option>
                                    </select>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="package-field">
                                <div class="package-select">
                                    <select class="form-control disabled_or_not" name="standard_revision" id="standard_revision">
                                        @for($i = 1; $i<=10; $i++)
                                            <option value="{{ $i }}" @if($project_details->standard_revision == $i) selected @endif>{{ $i }}</option>
                                        @endfor
                                        <option value="1000" @if($project_details->standard_revision == '1000') selected @endif>{{ __('Unlimited') }}</option>
                                    </select>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="package-field">
                                <div class="package-select">
                                    <select class="form-control disabled_or_not" name="premium_revision" id="premium_revision">
                                        @for($i = 1; $i<=10; $i++)
                                            <option value="{{ $i }}" @if($project_details->premium_revision == $i) selected @endif>{{ $i }}</option>
                                        @endfor
                                        <option value="1000" @if($project_details->premium_revision == '1000') selected @endif>{{ __('Unlimited') }}</option>
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <div class="package-head-left">
                                <span class="package-head-left-title">{{ __('Delivery time') }}</span>
                            </div>
                        </th>
                        @php $all_lengths = \App\Models\Length::where('status',1)->get() @endphp
                        @if($all_lengths->count() >= 1)
                            <td>
                                <div class="package-select">
                                    <select class="form-control" name="basic_delivery" id="basic_delivery">
                                        @foreach($all_lengths as $length)
                                            <option value="{{ $length->length }}" {{ $project_details->basic_delivery ===  $length->length ? 'selected' : ''}}>{{ $length->length }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td>
                                <select class="form-control disabled_or_not" name="standard_delivery" id="standard_delivery">
                                    @foreach($all_lengths as $length)
                                        <option value="{{ $length->length }}" {{ $project_details->standard_delivery ===  $length->length ? 'selected' : ''}}>{{ $length->length }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select class="form-control disabled_or_not" name="premium_delivery" id="premium_delivery">
                                    @foreach($all_lengths as $length)
                                        <option value="{{ $length->length }}" {{ $project_details->premium_delivery ===  $length->length ? 'selected' : ''}}>{{ $length->length }}</option>
                                    @endforeach
                                </select>
                            </td>
                        @else
                            <td>
                                <div class="package-select">
                                    <select class="form-control" name="basic_delivery" id="basic_delivery">
                                        <option value="1 Days" @if($project_details->basic_delivery == '1 Days') selected @endif>{{ __('1 Days') }}</option>
                                        <option value="2 Days" @if($project_details->basic_delivery == '2 Days') selected @endif>{{ __('2 Days') }}</option>
                                        <option value="3 Days" @if($project_details->basic_delivery == '3 Days') selected @endif>{{ __('3 Days') }}</option>
                                        <option value="Less than a week" @if($project_details->basic_delivery == 'Less than a week') selected @endif>{{ __('Less than a Week') }}</option>
                                        <option value="Less than a month" @if($project_details->basic_delivery == 'Less than a month') selected @endif>{{ __('Less than a month') }}</option>
                                        <option value="Less than 2 month" @if($project_details->basic_delivery == 'Less than 2 month') selected @endif>{{ __('Less than 2 month') }}</option>
                                        <option value="Less than 3 month" @if($project_details->basic_delivery == 'Less than 3 month') selected @endif>{{ __('Less than 3 month') }}</option>
                                        <option value="More than 3 month" @if($project_details->basic_delivery == 'More than 3 month') selected @endif>{{ __('More than 3 month') }}</option>
                                    </select>
                                </div>
                            </td>
                            <td>
                                <select class="form-control disabled_or_not" name="standard_delivery" id="standard_delivery">
                                    <option value="1 Days" @if($project_details->standard_delivery == '1 Days') selected @endif>{{ __('1 Days') }}</option>
                                    <option value="2 Days" @if($project_details->standard_delivery == '2 Days') selected @endif>{{ __('2 Days') }}</option>
                                    <option value="3 Days" @if($project_details->standard_delivery == '3 Days') selected @endif>{{ __('3 Days') }}</option>
                                    <option value="Less than a week" @if($project_details->standard_delivery == 'Less than a week') selected @endif>{{ __('Less than a Week') }}</option>
                                    <option value="Less than a month" @if($project_details->standard_delivery == 'Less than a month') selected @endif>{{ __('Less than a month') }}</option>
                                    <option value="Less than 2 month" @if($project_details->standard_delivery == 'Less than 2 month') selected @endif>{{ __('Less than 2 month') }}</option>
                                    <option value="Less than 3 month" @if($project_details->standard_delivery == 'Less than 3 month') selected @endif>{{ __('Less than 3 month') }}</option>
                                    <option value="More than 3 month" @if($project_details->standard_delivery == 'More than 3 month') selected @endif>{{ __('More than 3 month') }}</option>
                                </select>
                            </td>
                            <td>
                                <select class="form-control disabled_or_not" name="premium_delivery" id="premium_delivery">
                                    <option value="1 Days" @if($project_details->premium_delivery == '1 Days') selected @endif>{{ __('1 Days') }}</option>
                                    <option value="2 Days" @if($project_details->premium_delivery == '2 Days') selected @endif>{{ __('2 Days') }}</option>
                                    <option value="3 Days" @if($project_details->premium_delivery == '3 Days') selected @endif>{{ __('3 Days') }}</option>
                                    <option value="Less than a week" @if($project_details->premium_delivery == 'Less than a week') selected @endif>{{ __('Less than a Week') }}</option>
                                    <option value="Less than a month" @if($project_details->premium_delivery == 'Less than a month') selected @endif>{{ __('Less than a month') }}</option>
                                    <option value="Less than 2 month" @if($project_details->premium_delivery == 'Less than 2 month') selected @endif>{{ __('Less than 2 month') }}</option>
                                    <option value="Less than 3 month" @if($project_details->premium_delivery == 'Less than 3 month') selected @endif>{{ __('Less than 3 month') }}</option>
                                    <option value="More than 3 month" @if($project_details->premium_delivery == 'More than 3 month') selected @endif>{{ __('More than 3 month') }}</option>
                                </select>
                            </td>
                        @endif

                    </tr>
                    @foreach($project_details->project_attributes as $attr)
                        @php
                            $attr_value = str_replace(" ", "_",strtolower($attr->check_numeric_title));
                        @endphp
                    <tr class="append-include append-remove">
                        <th>
                            <div class="package-head-left">
                                <div class="package-head-left-flex flex-column">
                                    <input class="form-control checkbox_or_numeric_title" type="text" name="checkbox_or_numeric_title[]" value="{{ $attr->check_numeric_title ?? '' }}" placeholder="{{ __('Enter Title') }}">
                                    <div class="text-danger validation-error"></div>
                                </div>
                                <div class="package-field">
                                    <div class="package-field-select">
                                        <select class="form-control checkbox_or_numeric_select" name="checkbox_or_numeric_select[]">
                                            <option value="checkbox" @if($attr->type == 'checkbox') selected @endif>{{ __('Check Boxes') }}</option>
                                            <option value="numeric" @if($attr->type == 'numeric') selected @endif>{{ __('Numeric') }}</option>
                                            <option value="text" @if($attr->type == 'text') selected @endif>{{ __('Text Field') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </th>
                        <td>
                            <div class="d-flex flex-column gap-1">
                                @if($attr->type == 'checkbox')
                                    <input name="{{ $attr_value }}[basic]" type="checkbox" class="check-input" @if($attr->basic_check_numeric == 'on') checked @endif>
                                @elseif($attr->type == 'numeric')
                                    <input name="{{ $attr_value }}[basic]" type="number" class="form-control" value="{{ $attr->basic_check_numeric }}">
                                @elseif($attr->type == 'text')
                                    <input name="{{ $attr_value }}[basic]" type="text" class="form-control text-input" maxlength="60" value="{{ $attr->basic_check_numeric }}">
                                    <div class="char-counter">{{ strlen($attr->basic_check_numeric) }}/60</div>
                                @endif

                                <input type="number" name="{{ $attr_value }}[basic_price]" class="form-control price-input" placeholder="Extra price" min="0" value="{{ $attr->basic_extra_price ?? '' }}">
                            </div>
                        </td>

                        <td>
                            <div class="d-flex flex-column gap-1">
                                @if($attr->type == 'checkbox')
                                    <input name="{{ $attr_value }}[standard]" type="checkbox" class="check-input disabled_or_not" @if($attr->standard_check_numeric == 'on') checked @endif>
                                @elseif($attr->type == 'numeric')
                                    <input name="{{ $attr_value }}[standard]" type="number" class="form-control disabled_or_not" value="{{ $attr->standard_check_numeric }}">
                                @elseif($attr->type == 'text')
                                    <input name="{{ $attr_value }}[standard]" type="text" class="form-control text-input disabled_or_not" maxlength="60" value="{{ $attr->standard_check_numeric }}">
                                    <div class="char-counter">{{ strlen($attr->standard_check_numeric) }}/60</div>
                                @endif

                                <input type="number" name="{{ $attr_value }}[standard_price]" class="form-control price-input" placeholder="Extra price" min="0" value="{{ $attr->standard_extra_price ?? '' }}">
                            </div>
                        </td>

                        <td>
                            <div class="d-flex flex-column gap-1">
                                @if($attr->type == 'checkbox')
                                    <input name="{{ $attr_value }}[premium]" type="checkbox" class="check-input disabled_or_not" @if($attr->premium_check_numeric == 'on') checked @endif>
                                @elseif($attr->type == 'numeric')
                                    <input name="{{ $attr_value }}[premium]" type="number" class="form-control disabled_or_not" value="{{ $attr->premium_check_numeric }}">
                                @elseif($attr->type == 'text')
                                    <input name="{{ $attr_value }}[premium]" type="text" class="form-control text-input disabled_or_not" maxlength="60" value="{{ $attr->premium_check_numeric }}">
                                    <div class="char-counter">{{ strlen($attr->premium_check_numeric) }}/60</div>
                                @endif

                                <input type="number" name="{{ $attr_value }}[premium_price]" class="form-control price-input" placeholder="Extra price" min="0" value="{{ $attr->premium_extra_price ?? '' }}">

                                <div class="package-button-wrapper">
                                    <div class="package-field-icon add-rows">
                                        <i class="fa-solid fa-plus"></i>
                                    </div>
                                    <div class="package-field-icon remove-rows remove-icon">
                                        <i class="fa-solid fa-minus"></i>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    <tr class="delivery_charge_amount">
                        <th>
                            <div class="package-head-left">
                                <div class="package-head-left-flex">
                                    <span class="package-head-left-title">{{ __('Charges') }}</span>
                                </div>
                            </div>
                        </th>
                        <td>
                            <div class="package-field">
                                <div class="package-field-price">
                                    <div class="package-field-price-flex flex-between">
                                        <div class="package-field-price-main">
                                            <h5 class="package-field-price-main-title">
                                                @if(moduleExists('CurrencySwitcher'))
                                                    @if($project_details->basic_discount_charge !== null && $project_details->basic_discount_charge>0)
                                                        <span class="basic_discount_charge">{{ float_amount_without_currency_symbol($project_details->basic_discount_charge) }}</span>
                                                        <span class="basic_regular_charge"><s>{{ float_amount_without_currency_symbol($project_details->basic_regular_charge) }}</s></span>
                                                    @else
                                                        <span class="basic_discount_charge">{{ float_amount_without_currency_symbol($project_details->basic_discount_charge) }}</span>
                                                        <span class="basic_regular_charge">{{ float_amount_without_currency_symbol($project_details->basic_regular_charge) }}</span>
                                                    @endif
                                                @else
                                                    @if($project_details->basic_discount_charge)
                                                        <span class="basic_discount_charge">{{ float_amount_with_currency_symbol($project_details->basic_discount_charge) }}</span>
                                                        <span class="basic_regular_charge"><s>{{ float_amount_with_currency_symbol($project_details->basic_regular_charge) }}</s></span>
                                                    @else
                                                        <span class="basic_discount_charge"></span>
                                                        <span class="basic_regular_charge">{{ float_amount_with_currency_symbol($project_details->basic_regular_charge) }}</span>
                                                    @endif
                                                @endif
                                            </h5>
                                        </div>
                                        <div class="package-field-price-edit click-edit-basic-price">
                                            <img src="{{ asset('assets/static/icons/edit_color.svg') }}" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="package-field">
                                <div class="package-field-price">
                                    <div class="package-field-price-flex flex-between">
                                        <div class="package-field-price-main">
                                            <h5 class="package-field-price-main-title">
                                                @if(moduleExists('CurrencySwitcher'))
                                                    @if($project_details->standard_discount_charge !== null && $project_details->standard_discount_charge>0)
                                                        <span class="standard_discount_charge">{{ float_amount_without_currency_symbol($project_details->standard_discount_charge,2) }}</span>
                                                        <span class="standard_regular_charge"><s>{{ float_amount_without_currency_symbol($project_details->standard_regular_charge,2) }}</s></span>
                                                    @else
                                                        <span class="standard_discount_charge">{{ float_amount_without_currency_symbol($project_details->standard_discount_charge,2) }}</span>
                                                        <span class="standard_regular_charge">{{ float_amount_without_currency_symbol($project_details->standard_regular_charge,2) }}</span>
                                                    @endif
                                                @else
                                                    @if($project_details->standard_discount_charge)
                                                        <span class="standard_discount_charge">{{ float_amount_with_currency_symbol($project_details->standard_discount_charge) }}</span>
                                                        <span class="standard_regular_charge"><s>{{ float_amount_with_currency_symbol($project_details->standard_regular_charge) }}</s></span>
                                                    @else
                                                        <span class="standard_discount_charge"></span>
                                                        <span class="standard_regular_charge">{{ float_amount_with_currency_symbol($project_details->standard_regular_charge) }}</span>
                                                    @endif
                                                @endif
                                            </h5>
                                        </div>
                                        <div class="package-field-price-edit click-edit-standard-price">
                                            <img src="{{ asset('assets/static/icons/edit_color.svg') }}" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="package-field">
                                <div class="package-field-price">
                                    <div class="package-field-price-flex flex-between">
                                        <div class="package-field-price-main">
                                            <h5 class="package-field-price-main-title">
                                                @if(moduleExists('CurrencySwitcher'))
                                                    @if($project_details->premium_discount_charge !== null && $project_details->premium_discount_charge>0)
                                                        <span class="premium_discount_charge">{{ float_amount_without_currency_symbol($project_details->premium_discount_charge,2) }}</span>
                                                        <span class="premium_regular_charge"><s>{{ float_amount_without_currency_symbol($project_details->premium_regular_charge,2) }}</s></span>
                                                    @else
                                                        <span class="premium_discount_charge">{{ float_amount_without_currency_symbol($project_details->premium_discount_charge,2) }}</span>
                                                        <span class="premium_regular_charge">{{ float_amount_without_currency_symbol($project_details->premium_regular_charge) }}</span>
                                                    @endif
                                                @else
                                                    @if($project_details->premium_discount_charge)
                                                        <span class="premium_discount_charge">{{ float_amount_with_currency_symbol($project_details->premium_discount_charge) }}</span>
                                                        <span class="premium_regular_charge"><s>{{ float_amount_with_currency_symbol($project_details->premium_regular_charge) }}</s></span>
                                                    @else
                                                        <span class="premium_discount_charge"></span>
                                                        <span class="premium_regular_charge">{{ float_amount_with_currency_symbol($project_details->premium_regular_charge) }}</span>
                                                    @endif
                                                @endif
                                            </h5>
                                        </div>
                                        <div class="package-field-price-edit click-edit-premium-price">
                                            <img src="{{ asset('assets/static/icons/edit_color.svg') }}" alt="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<div class="popup-overlay"></div>

<!-- Basic Popup start -->
<div class="popup-fixed price-popup-basic-charge">
    <div class="popup-contents">
        <span class="popup-contents-close popup-close"> <i class="fas fa-times"></i> </span>
        <h2 class="popup-contents-title">{{ __('Set Charge') }}</h2>
        <div class="popup-contents-form custom-form">
            <div class="single-input single-input-icon">
                <label class="label-title"> {{ __('Regular Charge') }} </label>
                <input type="text" name="basic_regular_charge" id="basic_regular_charge" class="form--control" placeholder="00" value="{{ float_amount_without_currency_symbol($project_details->basic_regular_charge) }}">
                @if(moduleExists('CurrencySwitcher'))
                    <span class="input-icon">{{ site_currency_symbol() }}</span>
                @else
                    <span class="input-icon">{{ get_static_option('site_global_currency') ?? '' }}</span>
                @endif
            </div>
            <div class="single-input single-input-icon">
                <label class="label-title">{{ __('Discount Charge(Optional)') }}</label>
                <input type="text" name="basic_discount_charge" id="basic_discount_charge" class="form--control" placeholder="00" value="{{ float_amount_without_currency_symbol($project_details->basic_discount_charge) }}">
                @if(moduleExists('CurrencySwitcher'))
                    <span class="input-icon">{{ site_currency_symbol() }}</span>
                @else
                    <span class="input-icon">{{ get_static_option('site_global_currency') ?? '' }}</span>
                @endif
            </div>
        </div>
        <div class="popup-contents-btn flex-btn justify-content-end profile-border-top">
            <a href="javascript:void(0)" class="btn-profile btn-outline-gray btn-hover-danger popup-close"> <i class="las la-arrow-left"></i>{{ __('Cancel') }}</a>
            <a href="javascript:void(0)" class="btn-profile btn-bg-1 basic_price_setup">{{ __('Set Price') }}</a>
        </div>
    </div>
</div>

<!-- Standard Popup start -->
<div class="popup-fixed price-popup-standard-charge">
    <div class="popup-contents">
        <span class="popup-contents-close popup-close"> <i class="fas fa-times"></i> </span>
        <h2 class="popup-contents-title"> {{ __('Set Charge') }} </h2>
        <div class="popup-contents-form custom-form">
            <div class="single-input single-input-icon">
                <label class="label-title"> {{ __('Regular Charge') }} </label>
                <input type="text" name="standard_regular_charge" id="standard_regular_charge" class="form--control" placeholder="00" value="{{ float_amount_without_currency_symbol($project_details->standard_regular_charge) ?? '' }}">
                @if(moduleExists('CurrencySwitcher'))
                    <span class="input-icon">{{ site_currency_symbol() }}</span>
                @else
                    <span class="input-icon">{{ get_static_option('site_global_currency') ?? '' }}</span>
                @endif
            </div>
            <div class="single-input single-input-icon">
                <label class="label-title"> {{ __('Discount Charge(Optional)') }} </label>
                <input type="text" name="standard_discount_charge" id="standard_discount_charge" class="form--control" placeholder="00" value="{{ float_amount_without_currency_symbol($project_details->standard_discount_charge) ?? '' }}">
                @if(moduleExists('CurrencySwitcher'))
                    <span class="input-icon">{{ site_currency_symbol() }}</span>
                @else
                    <span class="input-icon">{{ get_static_option('site_global_currency') ?? '' }}</span>
                @endif
            </div>
        </div>
        <div class="popup-contents-btn flex-btn justify-content-end profile-border-top">
            <a href="javascript:void(0)" class="btn-profile btn-outline-gray btn-hover-danger popup-close"> <i class="las la-arrow-left"></i>{{ __('Cancel') }}</a>
            <a href="javascript:void(0)" class="btn-profile btn-bg-1 standard_price_setup">{{ __('Set Price') }}</a>
        </div>
    </div>
</div>

<!-- Premium Popup start -->
<div class="popup-fixed price-popup-premium-charge">
    <div class="popup-contents">
        <span class="popup-contents-close popup-close"> <i class="fas fa-times"></i> </span>
        <h2 class="popup-contents-title">{{ __('Set Charge') }}</h2>
        <div class="popup-contents-form custom-form">
            <div class="single-input single-input-icon">
                <label class="label-title">{{ __('Regular Charge') }}</label>
                <input type="text" name="premium_regular_charge" id="premium_regular_charge" class="form--control" placeholder="00" value="{{ float_amount_without_currency_symbol($project_details->premium_regular_charge) ?? '' }}">
                @if(moduleExists('CurrencySwitcher'))
                    <span class="input-icon">{{ site_currency_symbol() }}</span>
                @else
                    <span class="input-icon">{{ get_static_option('site_global_currency') ?? '' }}</span>
                @endif
            </div>
            <div class="single-input single-input-icon">
                <label class="label-title">{{ __('Discount Charge(Optional)') }}</label>
                <input type="text" name="premium_discount_charge" id="premium_discount_charge" class="form--control" placeholder="00" value="{{ float_amount_without_currency_symbol($project_details->premium_discount_charge) ?? '' }}">
                @if(moduleExists('CurrencySwitcher'))
                    <span class="input-icon">{{ site_currency_symbol() }}</span>
                @else
                    <span class="input-icon">{{ get_static_option('site_global_currency') ?? '' }}</span>
                @endif
            </div>
        </div>
        <div class="popup-contents-btn flex-btn justify-content-end profile-border-top">
            <a href="javascript:void(0)" class="btn-profile btn-outline-gray btn-hover-danger popup-close"> <i class="las la-arrow-left"></i>{{ __('Cancel') }}</a>
            <a href="javascript:void(0)" class="btn-profile btn-bg-1 premium_price_setup">{{ __('Set Price') }}</a>
        </div>
    </div>
</div>
<!-- Package & Charge Ends -->
