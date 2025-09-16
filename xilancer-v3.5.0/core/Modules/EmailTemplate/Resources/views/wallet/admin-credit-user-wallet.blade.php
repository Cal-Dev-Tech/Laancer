@extends('backend.layout.master')
@section('title', __('Admin Credit User Wallet Email'))
@section('style')
    <x-summernote.summernote-css />
@endsection
@section('content')
    <div class="dashboard__body">
        <div class="row">
            <div class="col-lg-12">
                <div class="customMarkup__single">
                    <div class="customMarkup__single__item">
                        <div class="customMarkup__single__item__flex">
                            <h4 class="customMarkup__single__title">{{ __('Admin Credit User Wallet Email') }}</h4>
                        </div>
                        <div class="search_delete_wrapper">
                            <h4><a class="btn-profile btn-bg-1" href="{{ route('admin.email.template.all') }}">{{ __('All Templates') }}</a></h4>
                            <x-search.search-in-table :id="'string_search'" />
                        </div>
                        <div class="customMarkup__single__inner mt-4">
                            <x-validation.error />
                            <form action="{{route('admin.user.wallet.credit.email')}}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.text
                                    :title="__('Email Subject')"
                                    :type="__('text')"
                                    :name="'admin_credit_wallet_subject'"
                                    :id="'admin_credit_wallet_subject'"
                                    :value="get_static_option('admin_credit_wallet_subject') ?? __('Wallet Credited by Admin')"
                                />
                                <x-form.summernote
                                    :title="__('Email Message')"
                                    :name="'admin_credit_wallet_message'"
                                    :id="'admin_credit_wallet_message'"
                                    :value="get_static_option('admin_credit_wallet_message') ?? '' "
                                />
                                <small class="form-text text-muted text-danger margin-top-20"><code>@name</code> {{__('will be replaced by dynamically with  name.')}}</small><br>
                                <small class="form-text text-muted text-danger margin-top-20"><code>@amount</code> {{__('will be replaced by dynamically with  amount.')}}</small><br>
                                <small class="form-text text-muted text-danger margin-top-20"><code>@deposit_id</code> {{__('will be replaced by dynamically with deposit id.')}}</small><br>
                                <x-btn.submit :title="__('Save')" :class="'btn-profile btn-bg-1 mt-4 pr-4 pl-4 update_info'" />
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <x-summernote.summernote-js />
@endsection
