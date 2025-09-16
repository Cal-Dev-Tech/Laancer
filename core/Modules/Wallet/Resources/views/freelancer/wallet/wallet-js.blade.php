<script>
    (function($){
        "use strict";
        $(document).ready(function(){
            let site_default_currency_symbol = '{{ site_currency_symbol() }}';
            //update profile
            $(document).on('click','.deposit_amount_to_wallet',function(e){

                @if(moduleExists('CurrencySwitcher'))
                @php $get_user_currency = \Modules\CurrencySwitcher\App\Models\SelectedCurrencyList::where('currency',get_currency_according_to_user())->first() ?? null;
                @endphp
                let max_deposit_amount = 0;
                max_deposit_amount = "{{ get_static_option('deposit_amount_limitation_for_user') * $get_user_currency->conversion_rate }}";

                let amount_for_currency  = parseInt($('#amount').val());
                let max_amount_for_currency = parseInt(max_deposit_amount ?? 3000);
                if(amount_for_currency == '' || isNaN(amount_for_currency) || amount_for_currency <= 0){
                    toastr_warning_js("{{ __('Please enter your deposit amount.') }}");
                    return false;
                }
                if(amount_for_currency  > max_amount_for_currency){
                    toastr_warning_js("{{ __('Deposit amount must not greater than the max limit.') }}");
                    return false;
                }

                @else
                    let amount  = parseInt($('#amount').val());
                    let max_amount = parseInt("{{ get_static_option('deposit_amount_limitation_for_user') ?? '3000' }}");
                    if(amount == '' || isNaN(amount) || amount <= 0){
                        toastr_warning_js("{{ __('Please enter your deposit amount.') }}");
                        return false;
                    }
                    if(amount  > max_amount){
                        toastr_warning_js("{{ __('Deposit amount must not greater than the max limit.') }}");
                        return false;
                    }
                @endif
            })

            // pagination
            $(document).on('click', '.pagination a', function(e){
                e.preventDefault();
                let page = $(this).attr('href').split('page=')[1];
                histories(page);
            });
            function histories(page){
                $.ajax({
                    url:"{{ route('freelancer.wallet.paginate.data').'?page='}}" + page,
                    success:function(res){
                        $('.search_result').html(res);
                    }
                });
            }

            // search history
            $(document).on('keyup','#string_search',function(){
                let string_search = $(this).val();
                $.ajax({
                    url:"{{ route('freelancer.wallet.search') }}",
                    method:'GET',
                    data:{string_search:string_search},
                    success:function(res){
                        if(res.status=='nothing'){
                            $('.search_result').html('<h3 class="text-center text-danger">'+"{{ __('Nothing Found') }}"+'</h3>');
                        }else{
                            $('.search_result').html(res);
                        }
                    }
                });
            })

            // get fields
            $(document).on("change", ".gateway-name", function() {
                let gatewayInformation = "";
                $(".gateway-information-wrapper").fadeOut(150);

                JSON.parse($(this).find(":selected").attr("data-fields")).forEach(function(value, index) {
                    let gateway_name = value.toLowerCase().replaceAll(" ", "_").replaceAll("-", "_");

                    gatewayInformation += `
                        <div class="single-input">
                            ${ value }
                            <input type="text" name="gateway_field[${ gateway_name }]" class="form-control" placeholder="{{ __('Write') }} ${ value.toLowerCase() }" />
                        </div>
                    `;
                })

                $(".gateway-information-wrapper").html(gatewayInformation);
                $(".gateway-information-wrapper").fadeIn(250);
            })

            //fee and amount container
            $(document).on('keyup','#withdraw_request_amount',function(){
                let site_default_currency_symbol = '{{ site_currency_symbol() }}';
                $('.fee_and_receive_amount_container').removeClass('d-none');

                let withdraw_fee = 0;
                let withdraw_fee_amount = 0;
                let receiveable_amount = 0;

                let amount = $(this).val()
                let withdraw_fee_type = "{{ get_static_option('withdraw_fee_type') }}"
                withdraw_fee = "{{ round(get_static_option('withdraw_fee'),2) }}"

                console.log(amount,withdraw_fee_type,withdraw_fee)

                withdraw_fee_amount = withdraw_fee_type == 'percentage' ? (amount*withdraw_fee/100).toFixed(2) : withdraw_fee;
                receiveable_amount = parseFloat(amount - withdraw_fee_amount);

                $('.withdraw_fee_amount_for_each_transaction').text(site_default_currency_symbol + withdraw_fee_amount)
                $('.receiveable_amount').text(site_default_currency_symbol + receiveable_amount.toFixed(2))
            })


            // Filter by type
            $(document).on('change', '#wallet_filter', function() {
                let type = $(this).val();
                let string_search = $('#string_search').val();
                $.ajax({
                    url: "{{ route('freelancer.wallet.filter') }}",
                    method: 'GET',
                    data: { type: type, string_search: string_search },
                    success: function(res) {
                        if(res.status == 'nothing'){
                            $('.search_result').html('<h3 class="text-center text-danger">{{ __("Nothing Found") }}</h3>');
                        } else {
                            $('.search_result').html(res);
                        }
                    }
                });
            });

            $(document).on('click', '#download_report', function() {
                let type = $('#wallet_filter').val();
                window.location.href = "{{ route('freelancer.wallet.download') }}" + "?type=" + type;
            });

            $(document).on('keyup change', '#amount', function() {
                let amount = parseFloat($(this).val()) || 0;

                let transaction_type = "{{ get_static_option('transaction_fee_type') ?? '' }}";
                let transaction_charge = parseFloat("{{ get_static_option('transaction_fee_charge') ?? 0 }}");

                if(transaction_charge > 0 && amount > 0){
                    $('.show_hide_transaction_section').removeClass('d-none');

                    let fee = transaction_type === 'fixed'
                        ? transaction_charge
                        : (amount * transaction_charge / 100);

                    let total = amount + fee;

                    $('.currency_symbol').text(site_default_currency_symbol);

                    $('.transaction_fee_amount').text(fee.toFixed(2));
                    $('.transaction_total_amount').text(total.toFixed(2));
                } else {
                    $('.show_hide_transaction_section').addClass('d-none');
                }
            });

        });
    }(jQuery));
</script>
