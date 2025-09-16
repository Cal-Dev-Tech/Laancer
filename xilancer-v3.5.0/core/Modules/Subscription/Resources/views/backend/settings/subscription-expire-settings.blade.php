@extends('backend.layout.master')
@section('title', __('Subscription Expire Settings'))
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
                            <h4 class="customMarkup__single__title">{{ __('Subscription Expire Settings') }}</h4>
                        </div>
                        <x-validation.error />
                        <div class="customMarkup__single__inner mt-4">
                            <x-notice.general-notice :class="'mt-5'" :description="__('Notice: How many days in advance would you like to receive a subscription expiry reminder?')" />
                            <form action="{{route('admin.subscription.expire.enable.disable.settings')}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="single-input my-5">
                                    <label class="label-title">{{ __('Enable/Disable Subscription') }}</label>
                                    <select name="subscription_expire_days" class="form-control">
                                        <option value="">{{ __('Select Day') }}</option>
                                        <option value="1" @if(get_static_option('subscription_expire_days') == '1') selected @endif>{{ __('1 Days') }}</option>
                                        <option value="2" @if(get_static_option('subscription_expire_days') == '2') selected @endif>{{ __('2 Days') }}</option>
                                        <option value="3" @if(get_static_option('subscription_expire_days') == '3') selected @endif>{{ __('3 Days') }}</option>
                                        <option value="7" @if(get_static_option('subscription_expire_days') == '7') selected @endif>{{ __('7 Days') }}</option>
                                    </select>
                                </div>
                                <x-btn.submit :title="__('Update')" :class="'btn btn-primary mt-4 pr-4 pl-4'" />
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-media.markup/>
@endsection

@section('script')
    <x-media.js/>
    <script>
        (function($){
            "use strict";
            $(document).ready(function () {

            });
        })(jQuery);
    </script>
@endsection
