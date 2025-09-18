@extends('backend.layout.master')
@section('title', __('Broadcasting Settings'))
@section('style')
    <x-media.css />
@endsection
@section('content')
    <div class="dashboard__body">
        <div class="row">
            <div class="col-lg-6">
                <div class="customMarkup__single">
                    <div class="customMarkup__single__item">
                        <div class="customMarkup__single__item__flex">
                            <h4 class="customMarkup__single__title">{{ __('Broadcasting Settings') }}</h4>
                        </div>
                        <x-validation.error />
                        <div class="customMarkup__single__inner mt-4">
                            <x-notice.general-notice :class="'mt-5'" :description="__(
                                'Notice: To activate live chat you must setup your broadcasting driver and credentials.',
                            )" />
                            <form action="{{ route('admin.pusher.settings') }}" method="post">
                                @csrf

                                <div class="single-input mb-3">
                                    <label class="label-title mb-3">{{ __('Broadcasting Driver') }}</label>
                                    <select name="BROADCAST_DRIVER" class="form-control" id="broadcast-driver-select">
                                        <option value="pusher" {{ env('BROADCAST_DRIVER') == 'pusher' ? 'selected' : '' }}>
                                            {{ __('Pusher') }}</option>
                                        <option value="reverb" {{ env('BROADCAST_DRIVER') == 'reverb' ? 'selected' : '' }}>
                                            {{ __('Reverb') }}</option>
                                    </select>
                                </div>

                                {{-- Pusher Settings --}}
                                <div id="pusher-settings" class="driver-settings"
                                    style="{{ env('BROADCAST_DRIVER') != 'pusher' ? 'display: none;' : '' }}">
                                    <h5 class="mb-4">{{ __('Pusher Settings') }}</h5>
                                    <x-form.text :title="__('Pusher App ID')" :name="'PUSHER_APP_ID'" :value="env('PUSHER_APP_ID') ?? ''"
                                        :placeholder="__('Pusher App ID')" />
                                    <x-form.text :title="__('Pusher App Key')" :name="'PUSHER_APP_KEY'" :value="env('PUSHER_APP_KEY') ?? ''" :placeholder="__('Pusher App Key')" />
                                    <x-form.text :title="__('Pusher App Secret')" :name="'PUSHER_APP_SECRET'" :value="env('PUSHER_APP_SECRET') ?? ''"
                                        :placeholder="__('Pusher App Secret')" />
                                    <x-form.text :title="__('Pusher App Cluster')" :name="'PUSHER_APP_CLUSTER'" :value="env('PUSHER_APP_CLUSTER') ?? ''"
                                        :placeholder="__('Pusher App Cluster')" />
                                </div>

                                {{-- Reverb Settings --}}
                                <div id="reverb-settings" class="driver-settings"
                                    style="{{ env('BROADCAST_DRIVER') != 'reverb' ? 'display: none;' : '' }}">
                                    <h5 class="mb-4">{{ __('Reverb Settings') }}</h5>
                                    <x-form.text :title="__('Reverb App ID')" :name="'REVERB_APP_ID'" :value="env('REVERB_APP_ID') ?? ''"
                                        :placeholder="__('Reverb App ID')" />
                                    <x-form.text  :title="__('Reverb App Key')" :name="'REVERB_APP_KEY'" :value="env('REVERB_APP_KEY') ?? ''"
                                        :placeholder="__('Reverb App Key')" />
                                    <x-form.text :title="__('Reverb App Secret')" :name="'REVERB_APP_SECRET'" :value="env('REVERB_APP_SECRET') ?? ''"
                                        :placeholder="__('Reverb App Secret')" />
                                    <x-form.text :title="__('Reverb Host')" :name="'REVERB_HOST'" :value="env('REVERB_HOST') ?? 'localhost'"
                                        :placeholder="__('Reverb Host (e.g., localhost)')" />
                                    <x-form.text :title="__('Reverb Port')" :name="'REVERB_PORT'" :value="env('REVERB_PORT') ?? '8080'"
                                        :placeholder="__('Reverb Port (e.g., 8080)')" />
                                    <x-form.text :title="__('Reverb Scheme')" :name="'REVERB_SCHEME'" :value="env('REVERB_SCHEME') ?? 'http'"
                                        :placeholder="__('Reverb Scheme (http or https)')" />
                                </div>
                                <x-btn.submit :title="__('Update')" :class="'btn btn-primary mt-4 pr-4 pl-4'" />
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-media.markup />
@endsection
@section('script')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                const $driverSelect = $('#broadcast-driver-select');
                const $pusherSettings = $('#pusher-settings');
                const $reverbSettings = $('#reverb-settings');

                $driverSelect.on('change', function() {
                    $('.driver-settings').hide();

                    if (this.value === 'pusher') {
                        $pusherSettings.show();
                    } else if (this.value === 'reverb') {
                        $reverbSettings.show();
                    }
                });
            });
        }(jQuery));
    </script>

@endsection
