<style>
    .single-input-icon:after {
        content: "";
        position: absolute;
        height: 38px;
        width: 2px;
        background-color: #F3F3F3;
        bottom: 0;
        left: 40px;
    }
    .class_for_currency_border{
        padding-left: 55px;
    }
    .currency_design_css{position: absolute;top: 7px;left: 7px;}
</style>

@php $user_type = auth()->user()->user_type == 1 ? 'client' : 'freelancer' @endphp

<div class="modal fade" id="paymentGatewayModal" tabindex="-1" aria-labelledby="paymentGatewayModalLabel" aria-hidden="true">
    <div class="modal-dialog ab">
        <form action="{{ route($user_type.'.'.'wallet.deposit') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="paymentGatewayModalLabel">{{ $title ?? '' }}</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @php
                   $max_deposit_amount = get_static_option('deposit_amount_limitation_for_user') ?? 300;
                   if(moduleExists('CurrencySwitcher')){
                   $get_user_currency = Modules\CurrencySwitcher\App\Models\SelectedCurrencyList::where('currency',get_currency_according_to_user())->first() ?? null;
                   $max_deposit_amount = $max_deposit_amount * ($get_user_currency->conversion_rate ?? 1);
                   }
                @endphp
                <div class="modal-body">

                    @if(moduleExists('CurrencySwitcher'))
                        <div class="single-input-icon mb-3 position-relative">
                            <label for="amount" class="label-title">{{ __('Enter Deposit Amount') }}</label>
                            <div class="input-icon position-relative">
                                <input type="number" name="amount" id="amount" value=""  placeholder="{{ __('Max Limit: ').$max_deposit_amount }}" class="form-control class_for_currency_border">
                                <span class="currency_design_css">{{ get_currency_according_to_user() ?? get_static_option('site_global_currency') }}</span> }}</span>
                            </div>
                        </div>
                        @else
                        <x-form.text
                            :type="'number'"
                            :title="__('Enter Deposit Amount')"
                            :name="'amount'"
                            :id="'amount'"
                            :placeholder="__('Max Limit: '). $max_deposit_amount" />

                        <div class="mt-2 show_hide_transaction_section d-none mb-3">
                            <p class="mb-0">
                                {{ __('Transaction Fee:') }}
                                <span class="currency_symbol">{{ site_currency_symbol() }}</span>
                                <span class="transaction_fee_amount">0.00</span>
                            </p>
                            <p class="fw-bold">
                                {{ __('Total Payable:') }}
                                <span class="currency_symbol">{{ site_currency_symbol() }}</span>
                                <span class="transaction_total_amount">0.00</span>
                            </p>
                        </div>
                    @endif
                    <div class="confirm-payment payment-border">
                        <div class="single-checkbox">
                            <div class="checkbox-inlines">
                                <label class="checkbox-label" for="check2">
                                    {!! \App\Helper\PaymentGatewayList::renderPaymentGatewayForForm(false) !!}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-profile btn-outline-gray btn-hover-danger" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <x-btn.submit :title="__('Deposit')" :class="'btn-profile btn-bg-1 deposit_amount_to_wallet'" />
                </div>
            </div>
        </form>
    </div>
</div>

