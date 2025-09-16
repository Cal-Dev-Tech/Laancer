@extends('backend.layout.master')
@section('title', __('Change Admin URL Prefix'))
@section('style')
    <x-media.css />
@endsection
@section('content')
    <div class="dashboard__body">
        <div class="row">
            <div class="col-lg-6">
                <div class="customMarkup__single">
                    <div class="customMarkup__single__item">
                        <x-notice.general-notice :description="__('Notice: You can change the admin login page URL prefix by entering a single word. Avoid using \'login\' as it is reserved for the user login page.')" />
                        <div class="customMarkup__single__item__flex">
                            <h4 class="customMarkup__single__title">{{ __('Change Admin URL Prefix') }}</h4>
                        </div>
                        <x-validation.error />
                        <div class="customMarkup__single__inner mt-4">
                            <form action="{{ route('admin.url.prefix.settings') }}" method="post">
                                @csrf
                                <div class="single-input my-5">
                                    <label class="label-title">{{ __('Admin URL Prefix') }}</label>
                                    <input type="text" class="form-control" name="admin_url_prefix"
                                        value="{{ get_static_option('admin_url_prefix') ?? 'admin' }}"
                                        placeholder="{{ __('Enter admin URL prefix') }}">
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
