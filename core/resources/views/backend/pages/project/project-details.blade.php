@extends('backend.layout.master')
@section('title', __('Project Details'))
@section('style')
    <x-select2.select2-css />
@endsection
@section('content')
    <div class="dashboard__body">
        <div class="customMarkup__single__item">

            <div class="customMarkup__single__inner mt-4">
                <div class="row g-4">
                    <div class="col-xl-7 col-lg-12">
                        <div class="project-preview">
                            <div class="project-preview-thumb">
                                @if(cloudStorageExist() && in_array(Storage::getDefaultDriver(), ['s3', 'cloudFlareR2', 'wasabi']))
                                    <img src="{{ render_frontend_cloud_image_if_module_exists('project/'.$project->image, load_from: $project->load_from) }}" alt="{{ $project->title ?? '' }}">
                                @else
                                    <img src="{{ asset('assets/uploads/project/' . $project->image) }}" alt="{{ $project->title }}">
                                @endif
                            </div>
                            <div class="project-preview-contents mt-4">
                                <div class="customMarkup__single__item__flex project--rejected--wrapper">
                                    <span class="customMarkup__single__title">{{ __('Status:') }}
                                        @if ($project->status === 0)
                                            <span>{{ __('Pending') }}</span>
                                        @elseif($project->status === 1)
                                            <span>{{ __('Approved') }}</span>
                                        @elseif($project->status === 2)
                                            <span>{{ __('Rejected') }}</span>
                                        @endif
                                    </span>
                                    <span class="customMarkup__single__title">{{ __('Reject:') }}
                                        <span>{{ $project->project_history?->reject_count ?? '0' }}</span>
                                    </span>
                                    <span class="customMarkup__single__title">{{ __('Edit:') }}
                                        <span>{{ $project->project_history?->edit_count ?? '0' }}</span>
                                    </span>
                                </div>
                                <h4 class="project-preview-contents-title mt-3"> {{ $project->title }} </h4>
                                <p class="project-preview-contents-para"> {!! $project->description !!} </p>
                            </div>
                        </div>
                        <div class="project-preview">
                            <div class="myJob-wrapper-single-flex flex-between align-items-center">
                                <div class="myJob-wrapper-single-contents">
                                    <div class="jobFilter-proposal-author-flex">
                                        <div class="jobFilter-proposal-author-thumb">
                                            @if($user->image)
                                                @if(cloudStorageExist() && in_array(Storage::getDefaultDriver(), ['s3', 'cloudFlareR2', 'wasabi']))
                                                    <img src="{{ render_frontend_cloud_image_if_module_exists( 'profile/'. $user->image, load_from: $user->load_from ?? '') }}" alt="{{ __('profile img') }}">
                                                @else
                                                    <img src="{{ asset('assets/uploads/profile/' . $user->image) }}" alt="{{ $user->first_name }}">
                                                @endif
                                            @else
                                                <img src="{{ asset('assets/static/img/author/author.jpg') }}" alt="{{ __('AuthorImg') }}">
                                            @endif
                                        </div>
                                        <div class="jobFilter-proposal-author-contents">
                                            <h4 class="jobFilter-proposal-author-contents-title"> {{ $user->first_name }}
                                                {{ $user->last_name }}</h4>
                                            <p class="jobFilter-proposal-author-contents-subtitle mt-2">
                                                @if($user->user_introduction?->title)
                                                    {{ $user->user_introduction?->title }} ·
                                                @endif
                                                <span>
                                                    @if($user->user_state?->state)
                                                        {{ $user->user_state?->state }},
                                                    @endif
                                                    @if($user->user_country?->country)
                                                        {{ $user->user_country?->country }}
                                                    @endif
                                                </span>
                                            </p>

                                            <div class="jobFilter-proposal-author-contents-review mt-2">
                                                {!! freelancer_rating($user->id) !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
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

                    </div>

                    <div class="col-xl-5 col-lg-8">
                        <div class="sticky-sidebar">
                            <div class="project-preview">
                                <div class="project-preview-tab">
                                    <ul class="tabs dashboard-tabs">
                                        <li data-tab="basic" class="active">{{ __($project->basic_title) }}</li>
                                        <li data-tab="standard">{{ __($project->standard_title) }}</li>
                                        <li data-tab="premium">{{ __($project->premium_title) }}</li>
                                    </ul>
                                    <div class="project-preview-tab-contents mt-4">

                                        <div class="tab-content-item dashboard-tab-content-item active" id="basic">
                                            <div class="project-preview-tab-header">
                                                <div class="project-preview-tab-header-item">
                                                    <span class="left"><i class="fa-solid fa-repeat"></i>
                                                        {{ __('Revisions') }}</span>
                                                    <strong class="right">{{ $project->basic_revision }}</strong>
                                                </div>
                                                <div class="project-preview-tab-header-item">
                                                    <span class="left"><i class="fa-regular fa-clock"></i>
                                                        {{ __('Delivery time') }}</span>
                                                    <strong class="right">{{ $project->basic_delivery }} </strong>
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
                                        <div class="tab-content-item dashboard-tab-content-item" id="standard">
                                            <div class="project-preview-tab-header">
                                                <div class="project-preview-tab-header-item">
                                                    <span class="left"><i class="fa-solid fa-repeat"></i>
                                                        {{ __('Revisions') }}</span>
                                                    <strong class="right">{{ $project->basic_revision }}</strong>
                                                </div>
                                                <div class="project-preview-tab-header-item">
                                                    <span class="left"><i class="fa-regular fa-clock"></i>
                                                        {{ __('Delivery time') }}</span>
                                                    <strong class="right">{{ $project->basic_delivery }}</strong>
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
                                        <div class="tab-content-item dashboard-tab-content-item" id="premium">
                                            <div class="project-preview-tab-header">
                                                <div class="project-preview-tab-header-item">
                                                    <span class="left"><i class="fa-solid fa-repeat"></i>
                                                        {{ __('Revisions') }}</span>
                                                    <strong class="right">{{ $project->premium_revision }}</strong>
                                                </div>
                                                <div class="project-preview-tab-header-item">
                                                    <span class="left"><i class="fa-regular fa-clock"></i>
                                                        {{ __('Delivery time') }}</span>
                                                    <strong class="right">{{ $project->premium_delivery }}</strong>
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

                                        <hr class="mt-5">
                                        <div class="btn-wrapper flex-btn justify-content-between">
                                            @can('project-reject')
                                                <a href="#" class="btn-profile btn-outline-gray btn-hover-danger" data-bs-target="#rejectProjectModal" data-bs-toggle="modal">{{ __('Click to Reject') }}</a>
                                            @endcan
                                            @can('project-status-change')
                                                @if ($project->status === 0 || $project->status === 2)
                                                    <x-status.table.status-change :title="__('Click to Active')" :class="'btn-profile btn-bg-1 swal_status_change_button'"
                                                                                  :url="route('admin.project.status.change', $project->id)" />
                                                @else
                                                    <x-status.table.status-change :title="__('Click to Inactive')" :class="'btn-profile btn-bg-1 swal_status_change_button'"
                                                                                  :url="route('admin.project.status.change', $project->id)" />
                                                @endif
                                            @endcan
                                            <div class="mt-3">
                                                <x-notice.general-notice :description="__(
                                                    'Notice: Active means users will be able to see the project on the website.',
                                                )" :description1="__(
                                                    'Notice: Inactive means the project will be hidden from users.',
                                                )"
                                                                         :description2="__(
                                                        'Notice: Rejected means the project has issues and the user is requested to resolve these issues and resubmit the project.',
                                                    )" />
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('backend.pages.project.project-reject')

@endsection

@section('script')
    <x-sweet-alert.sweet-alert2-js />
    <x-select2.select2-js />
    @include('backend.pages.project.project-js')

@endsection
