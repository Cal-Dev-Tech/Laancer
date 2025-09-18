@extends('backend.layout.master')
@section('title', __('Admin Commission Settings'))
@section('style')
    <x-media.css/>
@endsection
@section('content')
    <div class="dashboard__body">
        <div class="row">
            <div class="col-lg-6">
                <div class="customMarkup__single">
                    <div class="customMarkup__single__item">
                        <div class="customMarkup__single__item__flex">
                            <h4 class="customMarkup__single__title">{{ __('Global Commission Settings') }}</h4>
                        </div>
                        <x-validation.error />
                        <div class="customMarkup__single__inner mt-4">
                            <x-notice.general-notice :class="'mt-5'" :description="__('Notice: Global commission settings apply to all users by default. Subscription-specific rates will override these settings for subscribed users.')" />
                            <form action="{{route('admin.commission.settings')}}" method="post">
                                @csrf
                                <div class="single-input my-5">
                                    <label class="label-title">{{ __('Commission Type') }}</label>
                                    <select name="admin_commission_type" class="form-control">
                                        <option value="">{{ __('Select Type') }}</option>
                                        <option value="percentage" @if(get_static_option('admin_commission_type') == 'percentage') selected @endif>{{ __('Percentage') }}</option>
                                        <option value="fixed" @if(get_static_option('admin_commission_type') == 'fixed') selected @endif>{{ __('Fixed') }}</option>
                                    </select>
                                </div>
                                <x-form.number :title="__('Commission Charge')" :min="'1'" :max="'500'" :step="'0.01'" :name="'admin_commission_charge'" :value="get_static_option('admin_commission_charge') ?? 25 " :placeholder="__('Commission Charge')"/>
                                @can('admin-commission-settings-update')
                                <x-btn.submit :title="__('Update Global Settings')" :class="'btn btn-primary mt-4 pr-4 pl-4'" />
                                @endcan
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription-Based Commission Settings -->
            <div class="col-lg-6">
                <div class="customMarkup__single">
                    <div class="customMarkup__single__item">
                        <div class="customMarkup__single__item__flex">
                            <h4 class="customMarkup__single__title">{{ __('Subscription Commission Rates') }}</h4>
                        </div>
                        <div class="customMarkup__single__inner mt-4">
                            <x-notice.general-notice :class="'mt-5'" :description="__('Notice: Set custom commission rates for each subscription plan. Leave empty to use global settings.')" />
                            
                            @foreach($subscriptions as $subscription)
                            <div class="subscription-commission-item border rounded p-3 mb-3">
                                <h4 class="mb-3">{{ $subscription->title }}</h4>
                                <form action="{{route('admin.subscription.commission.update', $subscription->id)}}" method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="single-input">
                                                <label class="label-title">{{ __('Commission Type') }}</label>
                                                <select name="commission_type" class="form-control">
                                                    <option value="">{{ __('Use Global Settings') }}</option>
                                                    <option value="percentage" @if($subscription->commission_type == 'percentage') selected @endif>{{ __('Percentage') }}</option>
                                                    <option value="fixed" @if($subscription->commission_type == 'fixed') selected @endif>{{ __('Fixed') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="single-input">
                                                <label class="label-title">{{ __('Commission Rate') }}</label>
                                                <input type="number" name="commission_rate" 
                                                       value="{{ $subscription->commission_rate }}" 
                                                       class="form-control" 
                                                       min="0" max="500" step="0.01"
                                                       placeholder="{{ __('Leave empty for global rate') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            Current: 
                                            @if($subscription->hasCustomCommission())
                                                {{ $subscription->commission_rate }}{{ $subscription->commission_type == 'percentage' ? '%' : ' (Fixed)' }}
                                            @else
                                                Global ({{ get_static_option('admin_commission_charge') ?? 25 }}{{ get_static_option('admin_commission_type') == 'percentage' ? '%' : ' (Fixed)' }})
                                            @endif
                                        </small>
                                    </div>
                                    @can('admin-commission-settings-update')
                                    <x-btn.submit :title="__('Update')" :class="'btn btn-sm btn-primary mt-2'" />
                                    @endcan
                                </form>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-media.markup/>
@endsection