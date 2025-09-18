@extends('backend.layout.master')
@section('title', __('User Chatting Access'))
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
                            <h4 class="customMarkup__single__title">{{ __('User Chatting Access') }}</h4>
                        </div>
                        <x-validation.error />
                        <div class="customMarkup__single__inner mt-4">
                            <form action="{{route('admin.check.contact.availability')}}" method="POST">
                                @csrf
                                <div class="single-input my-5">
                                    <label class="label-title">{{ __('Allow client to contact freelancer') }}</label>

                                    <select name="check_contact_permission" class="form-control">
                                        <option value="1" @if($record?->can_contact_freelancer == 1) selected @endif>{{ __('Yes') }}</option>
                                        <option value="0"@if($record?->can_contact_freelancer == 0) selected @endif>{{ __('No') }}</option>
                                    </select>
                                </div>
                                @can('check_contact_permission')
                                <x-btn.submit :title="__('Save Changes')" :class="'btn btn-primary mt-2 pr-4 pl-4'" />
                                @endcan
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="customMarkup__single">
                    <div class="customMarkup__single__item">
                        <div class="customMarkup__single__item__flex">
                            <h4 class="customMarkup__single__title">{{ __('User Chatting Access Before Login') }}</h4>
                        </div>
                        <x-validation.error />
                        <div class="customMarkup__single__inner mt-4">
                            <form action="{{route('admin.show.contact.me.button.before.login')}}" method="POST">
                                @csrf
                                <div class="single-input my-5">
                                    <label class="label-title">{{ __('Show Contact Me Button Before Login') }}</label>

                                    <select name="show_button_before_login" class="form-control">
                                        <option value="1" @if($record?->show_contact_me_before_login == 1) selected @endif>{{ __('Yes') }}</option>
                                        <option value="0"@if($record?->show_contact_me_before_login == 0) selected @endif>{{ __('No') }}</option>
                                    </select>
                                </div>
                                @can('check_contact_permission')
                                <x-btn.submit :title="__('Save Changes')" :class="'btn btn-primary mt-2 pr-4 pl-4'" />
                                @endcan
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-media.markup/>
@endsection
