@extends('frontend.layout.master')
@section('page-meta-data')
    {!!  render_page_meta_data_for_service($project) !!}
@endsection

@section('style')
    <x-summernote.summernote-css />
    <style>
        .rating_profile_details {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .project-feedback-contents {
            flex: 1;
        }
        @if(get_static_option('profile_page_badge_settings') == 'enable')
        .level-badge-wrapper {
            top: 10px;
            right: 10px;
        }
        .jobFilter-proposal-author-contents-subtitle{
            padding-left:10px;
        }
        @endif
        .disabled-link {
            background-color: #ccc !important;
            pointer-events: none;
            cursor: default;
        }

        .pricing-wrapper-left{
            .pricing-wrapper-card-bottom-list {
                max-height: 50px;
                min-width: 205px;
                align-items: unset;
                span{
                    margin:auto 0;
                }
            }
        }

        [data-star] {
            text-align: left;
            font-style: normal;
            display: inline-block;
            position: relative;
            unicode-bidi: bidi-override;
        }

        [data-star]::before {
            display: block;
            content: "\f005" "\f005" "\f005" "\f005" "\f005";
            width: 100%;
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 15px;
            color: var(--body-color);
        }

        [data-star]::after {
            white-space: nowrap;
            position: absolute;
            top: 0;
            left: 0;
            content: "\f005" "\f005" "\f005" "\f005" "\f005";
            width: 100%;
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 15px;
            ;
            width: 0;
            color: var(--secondary-color);
            overflow: hidden;
            height: 100%;
        }

        [data-star^="0.1"]::after {
            width: 2%
        }

        [data-star^="0.2"]::after {
            width: 4%
        }

        [data-star^="0.3"]::after {
            width: 6%
        }

        [data-star^="0.4"]::after {
            width: 8%
        }

        [data-star^="0.5"]::after {
            width: 10%
        }

        [data-star^="0.6"]::after {
            width: 12%
        }

        [data-star^="0.7"]::after {
            width: 14%
        }

        [data-star^="0.8"]::after {
            width: 16%
        }

        [data-star^="0.9"]::after {
            width: 18%
        }

        [data-star^="1"]::after {
            width: 20%
        }

        [data-star^="1.1"]::after {
            width: 22%
        }

        [data-star^="1.2"]::after {
            width: 24%
        }

        [data-star^="1.3"]::after {
            width: 26%
        }

        [data-star^="1.4"]::after {
            width: 28%
        }

        [data-star^="1.5"]::after {
            width: 30%
        }

        [data-star^="1.6"]::after {
            width: 32%
        }

        [data-star^="1.7"]::after {
            width: 34%
        }

        [data-star^="1.8"]::after {
            width: 36%
        }

        [data-star^="1.9"]::after {
            width: 38%
        }

        [data-star^="2"]::after {
            width: 40%
        }

        [data-star^="2.1"]::after {
            width: 42%
        }

        [data-star^="2.2"]::after {
            width: 44%
        }

        [data-star^="2.3"]::after {
            width: 46%
        }

        [data-star^="2.4"]::after {
            width: 48%
        }

        [data-star^="2.5"]::after {
            width: 50%
        }

        [data-star^="2.6"]::after {
            width: 52%
        }

        [data-star^="2.7"]::after {
            width: 54%
        }

        [data-star^="2.8"]::after {
            width: 56%
        }

        [data-star^="2.9"]::after {
            width: 58%
        }

        [data-star^="3"]::after {
            width: 60%
        }

        [data-star^="3.1"]::after {
            width: 62%
        }

        [data-star^="3.2"]::after {
            width: 64%
        }

        [data-star^="3.3"]::after {
            width: 66%
        }

        [data-star^="3.4"]::after {
            width: 68%
        }

        [data-star^="3.5"]::after {
            width: 70%
        }

        [data-star^="3.6"]::after {
            width: 72%
        }

        [data-star^="3.7"]::after {
            width: 74%
        }

        [data-star^="3.8"]::after {
            width: 76%
        }

        [data-star^="3.9"]::after {
            width: 78%
        }

        [data-star^="4"]::after {
            width: 80%
        }

        [data-star^="4.1"]::after {
            width: 82%
        }

        [data-star^="4.2"]::after {
            width: 84%
        }

        [data-star^="4.3"]::after {
            width: 86%
        }

        [data-star^="4.4"]::after {
            width: 88%
        }

        [data-star^="4.5"]::after {
            width: 90%
        }

        [data-star^="4.6"]::after {
            width: 92%
        }

        [data-star^="4.7"]::after {
            width: 94%
        }

        [data-star^="4.8"]::after {
            width: 96%
        }

        [data-star^="4.9"]::after {
            width: 98%
        }

        [data-star^="5"]::after {
            width: 100%
        }

        @media (min-width: 992px) and (max-width: 1199.98px) {
            .pricing-wrapper-left .pricing-wrapper-card-top {
                display: block;
            }
        }

        @media (min-width: 300px) and (max-width: 991.98px) {
            .pricing-wrapper-left .pricing-wrapper-card-top {
                display: block;
            }
        }

        .pricing-wrapper {
            overflow: auto;
        }
        .pricing-wrapper-card {
            min-width: 150px;
        }
        @media (min-width: 992px) and (max-width: 1199.98px) {
            .pricing-wrapper-left .pricing-wrapper-card-top {
                /*  display: none; */
            }
        }
    </style>
@endsection
@section('content')
    <main>
        @if(moduleExists('CoinPaymentGateway'))@else<x-frontend.category.category/>@endif
        <x-breadcrumb.user-profile-breadcrumb :title="__('Project Details')" :innerTitle="__('Project Details')" />
        <!-- Project preview area Starts -->
        <div class="preview-area section-bg-2 pat-100 pab-100">
            <div class="container">
                <div class="row g-4">
                    <div class="col-xl-7 col-lg-7">
                        <div class="project-preview">
                            <div class="project-preview-thumb">
                                @if(cloudStorageExist() && in_array(Storage::getDefaultDriver(), ['s3', 'cloudFlareR2', 'wasabi']))
                                    <img src="{{ render_frontend_cloud_image_if_module_exists('project/'.$project->image, load_from: $project->load_from) }}" alt="{{ $project->title ?? '' }}">
                                @else
                                    <img src="{{ asset('assets/uploads/project/'.$project->image) ?? '' }}" alt="{{ $project->title ?? '' }}">
                                @endif
                            </div>
                            <div class="project-preview-contents mt-4">
                                <div class="single-project-content-top align-items-center flex-between">
                                    {!! project_rating($project->id) !!}
                                </div>
                                <h1 class="project-preview-contents-title mt-3"> {{ $project->title }} </h1>
                                <p class="project-preview-contents-para"> {!! $project->description !!} </p>
                            </div>
                        </div>
                        <div class="project-preview">
                            <div class="myJob-wrapper-single-flex flex-between align-items-center">
                                <div class="myJob-wrapper-single-contents">
                                    <div class="jobFilter-proposal-author-flex">
                                        <div class="jobFilter-proposal-author-thumb position-relative">
                                            @if ($user->image)
                                                <a href="{{ route('freelancer.profile.details', $user->username) }}">
                                                    @if(cloudStorageExist() && in_array(Storage::getDefaultDriver(), ['s3', 'cloudFlareR2', 'wasabi']))
                                                        <img src="{{ render_frontend_cloud_image_if_module_exists('profile/'.$user->image, load_from: $user->load_from) }}" alt="{{ $user->first_name ?? '' }}">
                                                    @else
                                                        <img src="{{ asset('assets/uploads/profile/' . $user->image) }}"
                                                             alt="{{ $user->first_name }}">
                                                    @endif
                                                </a>
                                                @if(moduleExists('FreelancerLevel'))
                                                    @if(get_static_option('profile_page_badge_settings') == 'enable')
                                                        <div class="freelancer-level-badge position-absolute">
                                                            {!! freelancer_level($user->id,'talent') ?? '' !!}
                                                        </div>
                                                    @endif
                                                @endif
                                            @else
                                                <a href="{{ route('freelancer.profile.details', $user->username) }}">
                                                    <img src="{{ asset('assets/static/img/author/author.jpg') }}"
                                                        alt="{{ __('AuthorImg') }}">
                                                </a>
                                                @if(moduleExists('FreelancerLevel'))
                                                    @if(get_static_option('profile_page_badge_settings') == 'enable')
                                                        <div class="freelancer-level-badge position-absolute">
                                                            {!! freelancer_level($user->id,'talent') ?? '' !!}
                                                        </div>
                                                    @endif
                                                @endif
                                            @endif
                                        </div>
                                        <div class="jobFilter-proposal-author-contents">
                                            <h4 class="single-freelancer-author-name">
                                                <a
                                                    href="{{ route('freelancer.profile.details', $user->username) }}">{{ $user->first_name }}
                                                    {{ $user->last_name }}@if(moduleExists('FreelancerLevel'))<small>{{ freelancer_level($user->id) }}</small>@endif
                                                </a>
                                                @if(Cache::has('user_is_online_' . $user->id))
                                                    <span class="single-freelancer-author-status"> {{ __('Active') }} </span>
                                                @else
                                                    <span class="single-freelancer-author-status-ofline"> {{ __('Inactive') }} </span>
                                                @endif
                                            </h4>
                                            <p class="jobFilter-proposal-author-contents-subtitle mt-2">
                                                @if($user->user_introduction?->title)
                                                {{ $user->user_introduction?->title }} ·
                                                @endif
                                                <span>
                                                    @if($user->user_state?->state)
                                                    {{ $user->user_state?->state }},
                                                    @endif
                                                    {{ $user->user_country?->country }}
                                                </span>
                                                @if($user->user_verified_status == 1) <i class="fas fa-circle-check"></i>@endif
                                            </p>
                                            <div class="jobFilter-proposal-author-contents-review mt-2">
                                                {!! freelancer_rating($user->id) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @if (Auth::guard('web')->check() && Auth::guard('web')->user()->user_type == 1 && Auth::guard('web')->user()->id != $project->user_id && Session::get('user_role') != 'freelancer')
                                    <div class="btn-wrapper">
                                        <form action="{{ route('client.message.send') }}" method="post"
                                            enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="freelancer_id" id="freelancer_id"
                                                value="{{ $project->user_id }}">
                                            <input type="hidden" name="from_user" id="from_user"
                                                value="{{ Auth::guard('web')->user()->id }}">
                                            <input type="hidden" name="project_id" id="project_id"
                                                value="{{ $project->id }}">
                                            <button type="submit" class="btn-profile btn-bg-1">
                                                <i class="fa-regular fa-comments"></i>  {{ __('Contact Me') }}</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if (!empty($project->standard_title) && !empty($project->premium_title))
                            <div class="project-preview" id="comparePackage">
                                <div class="project-preview-head profile-border-bottom">
                                    <h4 class="project-preview-head-title"> {{ __('Compare Packages') }} </h4>
                                </div>
                                <div class="table-responsive">
                                    <table class="comparison-table compare-package-table w-100">
                                        <thead class="pricing-wrapper-card text-center">
                                            <tr class="pricing-wrapper-card-top">
                                                <th class="text-center">
                                                    <h2 class="pricing-wrapper-card-top-prices">
                                                    {{ __('Packages') }}
                                                    </h2>
                                                </th>
                                                <th class="text-center">
                                                    
                                                    <h2 class="pricing-wrapper-card-top-prices">
                                                        {{ __('Basic') }}
                                                    </h2>
                                                </th>
                                                @if (!empty($project->standard_title))
                                                    <th class="text-center">
                                                        <h2 class="pricing-wrapper-card-top-prices">
                                                            {{ __('Standard') }}
                                                        </h2>
                                                        </th>
                                                @endif
                                                @if (!empty($project->premium_title))
                                                    <th class="text-center">
                                                        <h2 class="pricing-wrapper-card-top-prices">
                                                        {{ __('Premium') }}
                                                        </h2>
                                                    </th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="pricing-wrapper-card-bottom-list">
                                            <tr>
                                                <td class="text-center">{{ __('Revisions') }}</td>
                                                @foreach (['basic', 'standard', 'premium'] as $type)
                                                    <td class="text-center">
                                                        <span
                                                            class="close-icon">{{ $project->{"{$type}_revision"} }}</span>
                                                    </td>
                                                @endforeach
                                            </tr>
                                            <tr>
                                                <td class="text-center">{{ __('Delivery Time') }}</td>
                                                @foreach (['basic', 'standard', 'premium'] as $type)
                                                    <td class="text-center">
                                                        <span class="close-icon">
                                                            {{ $project->{"{$type}_delivery"} }}
                                                        </span>
                                                    </td>
                                                @endforeach
                                            </tr>
                                            @foreach ($project->project_attributes as $attr)
                                                <tr>
                                                    <td class="text-center">
                                                        {{ $attr->check_numeric_title }}
                                                    </td>
                                                    @foreach (['basic', 'standard', 'premium'] as $type)
                                                        @php
                                                            $value = $attr->{"{$type}_check_numeric"};
                                                        @endphp
                                                        <td class="text-center">
                                                            {!! in_array($value, ['on', true], true)
                                                                ? '<span class="check-icon"> <i class="fas fa-check"></i>
                                                                </span>'
                                                                : (in_array($value, ['off', false], true)
                                                                    ? '<span class="close-icon"> <i class="fas fa-times"></i>
                                                                </span>'
                                                                    : '<span>' . $value . '</span>') !!}
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                            
                                            <tr class="total">
                                                <td class="text-center">
                                                    <span class="deep_black_text md-font fw_semibold">
                                                        <h2 class="pricing-wrapper-card-top-prices">
                                                            {{ __('Price') }}
                                                        </h2>
                                                    </span>
                                                </td>
                                                @foreach (['basic', 'standard', 'premium'] as $type)
                                                    <td class="text-center">
                                                        <div class="price">
                                                            @if ($project->{"{$type}_regular_charge"} != null && $project->{"{$type}_regular_charge"} > 0)
                                                                <h6 class="price-main">
                                                                    {{ float_amount_with_currency_symbol($project->{"{$type}_discount_charge"}) }}
                                                                </h6>
                                                                <s class="price-old">
                                                                    {{ float_amount_with_currency_symbol($project->{"{$type}_regular_charge"}) }}
                                                                </s>
                                                            @else
                                                                <h6 class="price-main">
                                                                    {{ float_amount_with_currency_symbol($project->{"{$type}_regular_charge"}) }}
                                                                </h6>
                                                            @endif
                                                        </div>
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        <?php
                        $pagination_limit = 10;
                        $project_id = $project->id;
                        $countProjectCompleteOrder = \App\Models\Order::select('id')
                            ->whereHas('rating')
                            ->where('identity', $project->id)
                            ->where('is_project_job', 'project')
                            ->where('status', 3)
                            ->count();
                        ?>

                        @if ($countProjectCompleteOrder >= 1)
                            <div class="project-preview project-reviews-scroll">
                                <div class="project-preview-head profile-border-bottom">
                                    <h4 class="project-preview-head-title">{{ __('Feedback & Reviews') }}</h4>
                                </div>
                                <div class="project-reviews">
                                    @include('frontend.pages.project-details.reviews')
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="col-xl-5 col-lg-5">
                        <x-validation.error />
                        <div class="sticky-sidebar">
                            <div class="project-preview">
                                <div class="project-preview-tab">
                                    <ul class="tabs">
                                        <li data-tab="Basic" class="active">{{ __($project->basic_title) }}</li>
                                        <li data-tab="Standard" class="@if(empty($project->standard_title)) pe-none @endif">{{ __($project->standard_title) }}</li>
                                        <li data-tab="Premium" class="@if(empty($project->premium_title)) pe-none @endif">{{ __($project->premium_title) }}</li>
                                    </ul>
                                    <div class="project-preview-tab-contents mt-4">

                                        <div class="tab-content-item active" id="Basic">
                                            <div class="project-preview-tab-header">
                                                <div class="project-preview-tab-header-item">
                                                    <span class="left"><i class="fa-solid fa-repeat"></i>
                                                        {{ __('Revisions') }}</span>
                                                    <strong class="right">{{ $project->basic_revision }}</strong>
                                                </div>
                                                <div class="project-preview-tab-header-item">
                                                    <span class="left"><i class="fa-regular fa-clock"></i>
                                                        {{ __('Delivery time') }}</span>
                                                    <strong class="right">{{ __($project->basic_delivery) }}</strong>
                                                </div>
                                            </div>
                                            <div class="project-preview-tab-inner mt-4">
                                                @foreach ($project->project_attributes as $attr)
                                                    @if(empty($attr->basic_extra_price) || $attr->basic_extra_price == 0)
                                                        <div class="project-preview-tab-inner-item">
                                                            <span class="left">{{ $attr->check_numeric_title }}</span>
                                                            @if ($attr->basic_check_numeric == 'on')
                                                                <span class="check-icon"> <i class="fas fa-check"></i> </span>
                                                            @elseif ($attr->basic_check_numeric == 'off')
                                                                <span class="close-icon"> <i class="fas fa-times"></i> </span>
                                                            @else
                                                                <span class="right">{{ $attr->basic_check_numeric }}</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endforeach

                                                {{-- Extra Services (Basic) --}}
                                                @php
                                                    $basicExtras = $project->project_attributes->filter(fn($attr) => $attr->basic_extra_price > 0);
                                                @endphp

                                                @if($basicExtras->count())
                                                    <div class="project-preview-tab-inner-item">
                                                        <span class="left price-title">{{ __('Extra Service') }}</span>
                                                    </div>
                                                    @foreach ($basicExtras as $attr)
                                                        <div class="project-preview-tab-inner-item d-flex justify-content-between align-items-center">
                                                            <label class="extra-service m-0 left">
                                                                <input type="checkbox" class="basic-extra-checkbox"
                                                                    data-price="{{ $attr->basic_extra_price }}"
                                                                    name="extras[{{ $attr->id }}]">
                                                                {{ $attr->check_numeric_title }}
                                                            </label>
                                                            <span class="right price">+{{ float_amount_with_currency_symbol($attr->basic_extra_price) }}</span>
                                                        </div>
                                                    @endforeach
                                                @endif

                                                <div class="project-preview-tab-inner-item">
                                                    @if ($project->basic_discount_charge != null && $project->basic_discount_charge > 0)
                                                        <span class="left price-title">{{ __('Price') }}</span>
                                                        <span class="right price">
                                                            <s>{{ float_amount_with_currency_symbol($project->basic_regular_charge ?? '') }}</s><span>{{ float_amount_with_currency_symbol($project->basic_discount_charge) }}</span></span>
                                                    @else
                                                        <span class="left price-title">{{ __('Price') }}</span>
                                                        <span
                                                            class="right price"><span>{{ float_amount_with_currency_symbol($project->basic_regular_charge ?? '') }}</span></span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-content-item" id="Standard">
                                            <div class="project-preview-tab-header">
                                                <div class="project-preview-tab-header-item">
                                                    <span class="left"><i class="fa-solid fa-repeat"></i>
                                                        {{ __('Revisions') }}</span>
                                                    <strong class="right">{{ $project->standard_revision }}</strong>
                                                </div>
                                                <div class="project-preview-tab-header-item">
                                                    <span class="left"><i class="fa-regular fa-clock"></i>
                                                        {{ __('Delivery time') }}</span>
                                                    <strong class="right">{{ __($project->standard_delivery) }}</strong>
                                                </div>
                                            </div>
                                            <div class="project-preview-tab-inner mt-4">
                                                @foreach ($project->project_attributes as $attr)
                                                    @if(empty($attr->standard_extra_price) || $attr->standard_extra_price == 0)
                                                        <div class="project-preview-tab-inner-item">
                                                            <span class="left">{{ $attr->check_numeric_title }}</span>
                                                            @if ($attr->standard_check_numeric == 'on')
                                                                <span class="check-icon"> <i class="fas fa-check"></i> </span>
                                                            @elseif($attr->standard_check_numeric == 'off')
                                                                <span class="close-close"> <i class="fas fa-times"></i>
                                                                </span>
                                                            @else
                                                                <span class="right"> {{ $attr->standard_check_numeric }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endforeach

                                                @php
                                                    $standardExtras = $project->project_attributes->filter(fn($attr) => $attr->standard_extra_price > 0);
                                                @endphp

                                                @if($standardExtras->count())
                                                    <div class="project-preview-tab-inner-item">
                                                        <span class="left price-title">{{ __('Extra Service') }}</span>
                                                    </div>
                                                    @foreach ($standardExtras as $attr)
                                                        <div class="project-preview-tab-inner-item d-flex justify-content-between align-items-center">
                                                            <label class="extra-service m-0">
                                                                <input type="checkbox" class="standard-extra-checkbox"
                                                                    data-price="{{ $attr->standard_extra_price }}"
                                                                    name="extras[{{ $attr->id }}]">
                                                                {{ $attr->check_numeric_title }}
                                                            </label>
                                                            <span class="right price">+{{ float_amount_with_currency_symbol($attr->standard_extra_price) }}</span>
                                                        </div>
                                                    @endforeach
                                                @endif


                                                <div class="project-preview-tab-inner-item">
                                                    @if ($project->standard_discount_charge != null && $project->standard_discount_charge > 0)
                                                        <span class="left price-title">{{ __('Price') }}</span>
                                                        <span class="right price">
                                                            <s>{{ float_amount_with_currency_symbol($project->standard_regular_charge ?? '') }}</s><span>{{ float_amount_with_currency_symbol($project->standard_discount_charge) }}</span></span>
                                                    @else
                                                        <span class="left price-title">{{ __('Price') }}</span>
                                                        <span
                                                            class="right price"><span>{{ float_amount_with_currency_symbol($project->standard_regular_charge ?? '') }}</span></span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-content-item" id="Premium">
                                            <div class="project-preview-tab-header">
                                                <div class="project-preview-tab-header-item">
                                                    <span class="left"><i class="fa-solid fa-repeat"></i>
                                                        {{ __('Revisions') }}</span>
                                                    <strong class="right">{{ $project->premium_revision }}</strong>
                                                </div>
                                                <div class="project-preview-tab-header-item">
                                                    <span class="left"><i class="fa-regular fa-clock"></i>
                                                        {{ __('Delivery time') }}</span>
                                                    <strong class="right">{{ __($project->premium_delivery) }}</strong>
                                                </div>
                                            </div>
                                            <div class="project-preview-tab-inner mt-4">
                                                @foreach ($project->project_attributes as $attr)
                                                    @if(empty($attr->premium_extra_price) || $attr->premium_extra_price == 0)
                                                        <div class="project-preview-tab-inner-item">
                                                            <span class="left">{{ $attr->check_numeric_title }}</span>
                                                            @if ($attr->premium_check_numeric == 'on')
                                                                <span class="check-icon"> <i class="fas fa-check"></i> </span>
                                                            @elseif($attr->premium_check_numeric == 'off')
                                                                <span class="close-icon"> <i class="fas fa-times"></i> </span>
                                                            @else
                                                                <span class="right"> {{ $attr->premium_check_numeric }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @endforeach

                                                {{-- Extra Services (Premium) --}}
                                                @php
                                                    $premiumExtras = $project->project_attributes->filter(fn($attr) => $attr->premium_extra_price > 0);
                                                @endphp

                                                @if($premiumExtras->count())
                                                    <div class="project-preview-tab-inner-item">
                                                        <span class="left price-title">{{ __('Extra Service') }}</span>
                                                    </div>
                                                    @foreach ($premiumExtras as $attr)
                                                        <div class="project-preview-tab-inner-item d-flex justify-content-between align-items-center">
                                                            <label class="extra-service m-0">
                                                                <input type="checkbox" class="premium-extra-checkbox"
                                                                    data-price="{{ $attr->premium_extra_price }}"
                                                                    name="extras[{{ $attr->id }}]">
                                                                {{ $attr->check_numeric_title }}
                                                            </label>
                                                            <span class="right price">+{{ float_amount_with_currency_symbol($attr->premium_extra_price) }}</span>
                                                        </div>
                                                    @endforeach
                                                @endif

                                                <div class="project-preview-tab-inner-item">
                                                    @if ($project->premium_discount_charge != null && $project->premium_discount_charge > 0)
                                                        <span class="left price-title">{{ __('Price') }}</span>
                                                        <span class="right price">
                                                            <s>{{ float_amount_with_currency_symbol($project->premium_regular_charge ?? '') }}</s><span>{{ float_amount_with_currency_symbol($project->premium_discount_charge) }}</span></span>
                                                    @else
                                                        <span class="left price-title">{{ __('Price') }}</span>
                                                        <span
                                                            class="right price"><span>{{ float_amount_with_currency_symbol($project->premium_regular_charge ?? '') }}</span></span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="btn-wrapper flex-btn justify-content-between mt-4">
                                            @if (Auth::guard('web')->check())
                                                @if (Auth::guard('web')->user()->user_type == 1 && Auth::guard('web')->user()->id != $project->user_id && Session::get('user_role') != 'freelancer')
                                                    <form action="{{ route('client.message.send') }}" method="post"
                                                        enctype="multipart/form-data">
                                                        @csrf
                                                        <input type="hidden" name="freelancer_id" id="freelancer_id"
                                                            value="{{ $project->user_id }}">
                                                        <input type="hidden" name="from_user" id="from_user"
                                                            value="1">
                                                        <input type="hidden" name="project_id" id="project_id"
                                                            value="{{ $project->id }}">
                                                        <button type="submit" class="btn-profile btn-outline-gray"><i
                                                                class="fa-regular fa-comments"></i>
                                                            {{ __('Contact Me') }}</button>
                                                    </form>
                                                    @if(moduleExists('SecurityManage'))
                                                        @if(Auth::guard('web')->user()->freeze_order_create == 'freeze')
                                                            <a href="#/" class="btn-profile btn-bg-1 @if(Auth::guard('web')->user()->freeze_order_create == 'freeze') disabled-link @endif">
                                                                {{ __('Continue to Order') }}
                                                            </a>
                                                        @else
                                                            <a href="#/"
                                                               class="btn-profile btn-bg-1 basic_standard_premium"
                                                               data-project_id="{{ $project->id }}" data-bs-toggle="modal"
                                                               data-bs-target="#paymentGatewayModal">{{ __('Continue to Order') }}
                                                            </a>
                                                        @endif
                                                    @else
                                                        <a href="#/"
                                                           class="btn-profile btn-bg-1 basic_standard_premium"
                                                           data-project_id="{{ $project->id }}" data-bs-toggle="modal"
                                                           data-bs-target="#paymentGatewayModal">{{ __('Continue to Order') }}
                                                        </a>
                                                    @endif
                                                @endif

                                                 @if (Auth::guard('web')->user()->user_type == 2 && Auth::guard('web')->user()->id != $project->user_id && Session::get('user_role') == 'client')
                                                    @include('frontend.pages.project-details.freelancer-order-as-client')
                                                @endif
                                            @else
                                                <a class="btn-profile btn-outline-gray contact_warning_chat_message">
                                                    <i class="fa-regular fa-comments"></i>{{ __('Contact Me') }}
                                                </a>
                                                <a href="#/" class="btn-profile btn-bg-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#loginModal">{{ __('Login to Order') }}
                                                </a>
                                            @endif
                                        </div>

                                        @if (!empty($project->standard_title) && !empty($project->premium_title))
                                            <div class="btn-wrapper text-left mt-4">
                                                <a href="#comparePackage" class="compareBtn">
                                                    {{ __('Compare Package') }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Project preview area end -->
    </main>

    @include('frontend.pages.order.login-markup')
    @include('frontend.pages.order.gateway-markup')

@endsection

@section('script')
    <x-frontend.payment-gateway.gateway-select-js />
    @include('frontend.pages.project-details.load-more-js')
    @include('frontend.pages.order.order-js')
@endsection
