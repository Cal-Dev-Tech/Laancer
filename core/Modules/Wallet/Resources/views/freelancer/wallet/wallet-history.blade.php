@extends('frontend.layout.master')
@section('site_title', __('Wallet History'))
@section('style')
    <style>
        .single-profile-settings-flex {
            justify-content: space-between;
        }

        .single-profile-settings-contents .single-profile-settings-contents-upload-btn {
            padding: 0;
        }

        .single-profile-settings .single-profile-settings-thumb {
            max-width: unset;
        }

        .balance-wallet {
            color: var(--paragraph-color);
        }

        .balance-wallet strong {
            color: var(--heading-color);
        }

        .single-profile-settings-thumb {
            width: unset;
        }
    </style>
@endsection

@section('content')
    <main>
        <x-breadcrumb.user-profile-breadcrumb :title="__('Wallet History')" :innerTitle="__('Wallet History')" />
        <!-- Profile Settings area Starts -->
        <div class="responsive-overlay"></div>
        <div class="profile-settings-area pat-100 pab-100 section-bg-2">
            <div class="container">
                <div class="row g-4">
                    @include('frontend.user.layout.partials.sidebar')
                    <div class="col-xl-9 col-lg-8">
                        <div class="profile-settings-wrapper">

                            <div class="single-profile-settings" id="display_client_profile_photo">
                                <div class="single-profile-settings-flex">
                                    <div class="single-profile-settings-thumb w-100 h-auto">
                                        <div class="d-flex justify-content-between gap-3">
                                            <div class="mb-3">
                                                <div class="fs-5">{{ __('Total Earnings:') }}
                                                    <span class="single-project-content-price">{{ float_amount_with_currency_symbol($total_earning) }}</span>
                                                </div>
                                                <div class="fs-5">{{ __('Total Deposits:') }}
                                                    <span class="single-project-content-price">{{ float_amount_with_currency_symbol($total_deposit) }}</span>
                                                </div>
                                            </div>
                                            <div>
                                                <label for="wallet_filter" class="mb-0">{{ __('Filter:') }}</label>
                                                <select id="wallet_filter" class="form-control w-auto">
                                                    <option value="">{{ __('All') }}</option>
                                                    <option value="earning">{{ __('Earning') }}</option>
                                                    <option value="deposit">{{ __('Deposit') }}</option>
                                                    <option value="deduction">{{ __('Deduct by Admin') }}</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between gap-3 flex-wrap mt-3">
                                            <div class="d-flex gap-2">
                                                @if ($total_wallet_balance >= get_static_option('minimum_withdraw_amount'))
                                                    <div class="single-profile-settings-contents">
                                                        <div class="single-profile-settings-contents-upload">
                                                            <div class="single-profile-settings-contents-upload-btn">
                                                                @if (moduleExists('SecurityManage'))
                                                                    @if (Auth::guard('web')->user()->freeze_withdraw == 'freeze')
                                                                        <button type="button" class="btn btn-danger"
                                                                            disabled>{{ __('Withdraw Request') }}</button>
                                                                    @else
                                                                        <button class="btn btn-danger" data-bs-toggle="modal"
                                                                            data-bs-target="#withdrawModal">{{ __('Withdraw Request') }}</button>
                                                                    @endif
                                                                @else
                                                                    <button class="btn btn-danger" data-bs-toggle="modal"
                                                                        data-bs-target="#withdrawModal">{{ __('Withdraw Request') }}</button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="single-profile-settings-contents">
                                                    <div class="single-profile-settings-contents-upload">
                                                        <div class="single-profile-settings-contents-upload-btn">
                                                            <button class="btn-profile btn-bg-1" data-bs-toggle="modal"
                                                                data-bs-target="#paymentGatewayModal">{{ __('Deposit to Wallet') }}</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div>
                                                <button id="download_report"
                                                    class="btn btn-success">{{ __('Download Report') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="single-profile-settings" id="display_client_profile_info">
                                <div class="single-profile-settings-header">
                                    <x-validation.error />
                                    <div class="single-profile-settings-header-flex">
                                        <x-form.form-title :title="__('Wallet History')" :class="'single-profile-settings-header-title'" />
                                        <x-search.search-in-table :id="'string_search'" :placeholder="__('Enter date to search')" />
                                    </div>
                                </div>
                                <div class="single-profile-settings-inner profile-border-top">
                                    <div class="custom_table style-04 search_result">
                                        @include('wallet::freelancer.wallet.search-result')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Profile Settings area end -->
        @include('wallet::freelancer.wallet.withdraw-modal')
        <x-frontend.payment-gateway.gateway-markup :title="__('You can deposit to your wallet from the available payment gateway')" />
    </main>
@endsection

@section('script')
    @include('wallet::freelancer.wallet.wallet-js')
    <x-frontend.payment-gateway.gateway-select-js />
@endsection
