@extends('backend.layout.master')
@section('title', __('Wallet Deposit History'))
@section('style')
    <x-select2.select2-css />
@endsection
@section('content')
    <div class="dashboard__body">
        <div class="row">
            <div class="col-lg-12">
                <x-notice.general-notice :description="__('Notice: You can search here by deposit date.')" />
                <div class="customMarkup__single">
                    <div class="customMarkup__single__item">
                        <div class="customMarkup__single__item__flex">
                            <h4 class="customMarkup__single__title">{{ __('Wallet Deposit History') }}</h4>
                            <x-search.search-in-table :id="'string_search'" :placeholder="__('Enter date to search')" />
                        </div>
                        <div class="customMarkup__single__item__flex d-flex gap-2 mt-3 justify-content-start">
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#addWalletCreditModal">
                                <i class="las la-plus"></i>
                                {{ __('Credit User Wallet') }}
                            </button>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                data-bs-target="#deductWalletModal">
                                <i class="las la-plus"></i>
                                {{ __('Deduct From User Wallet') }}
                            </button>
                        </div>
                        <div class="customMarkup__single__inner mt-4">
                            <!-- Table Start -->
                            <div class="custom_table style-04 search_result">
                                @include('wallet::admin.wallet.search-result')
                            </div>
                            <!-- Table End -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="addWalletCreditModal" tabindex="-1" aria-labelledby="addWalletCreditModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addWalletCreditForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="addWalletCreditModalLabel">{{ __('Add Wallet Credit') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group mb-3">
                            <label>{{ __('Select User') }}</label>
                            <select name="user_id" id="userSelect" class="form-control"></select>
                        </div>


                        <!-- Amount -->
                        <div class="form-group mb-3">
                            <label>{{ __('Amount') }}</label>
                            <input type="number" name="amount" class="form-control" min="1" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>{{ __('Note (Optional)') }}</label>
                            <textarea name="note" class="form-control" placeholder="{{ __('Enter a note for the deduction') }}"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="creditWalletBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="btn-text">{{ __('Credit Wallet') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deductWalletModal" tabindex="-1" aria-labelledby="deductWalletModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="deductWalletForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="deductWalletModalLabel">{{ __('Deduct Wallet Amount') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="{{ __('Close') }}"></button>
                    </div>
                    <div class="modal-body">

                        <div class="form-group mb-3">
                            <label>{{ __('Select User') }}</label>
                            <select name="user_id" id="deductUserSelect" class="form-control"></select>
                        </div>

                        <div class="form-group mb-3">
                            <label>{{ __('Amount') }}</label>
                            <input type="number" name="amount" class="form-control" min="1" required>
                        </div>

                        <div class="form-group mb-3">
                            <label>{{ __('Note (Optional)') }}</label>
                            <textarea name="note" class="form-control" placeholder="{{ __('Enter a note for the deduction') }}"></textarea>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger" id="deductWalletBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="btn-text">{{ __('Deduct Wallet') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('script')
    <x-sweet-alert.sweet-alert2-js />
    <x-select2.select2-js />
    @include('wallet::admin.wallet.wallet-js')
@endsection
