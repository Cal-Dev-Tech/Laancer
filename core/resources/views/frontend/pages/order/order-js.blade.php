<script>
    (function($) {
        "use strict";
        $(document).ready(function() {
            let site_default_currency_symbol = '{{ site_currency_symbol() }}';
            let originalPrices = {};

            // Store original prices on page load
            function storeOriginalPrices() {
                $('.tab-content-item').each(function() {
                    let tabId = $(this).attr('id');
                    let priceElement = $(this).find(
                        '.project-preview-tab-inner-item:last .price span:last');
                    if (priceElement.length > 0) {
                        let priceText = priceElement.text().trim();
                        if (priceText) {
                            let cleanPrice = priceText.substring(1);
                            let thousand_separator =
                                "{{ get_static_option('site_currency_thousand_separator') }}";
                            let decimal_separator =
                                "{{ get_static_option('site_currency_decimal_separator') }}";
                            originalPrices[tabId] = normalizePrice(cleanPrice, thousand_separator,
                                decimal_separator);
                        }
                    }
                });
            }

            // Call on page load
            storeOriginalPrices();

            $('document').on('click', '.set_dead_line', function() {
                $(this).flatpickr({
                    altInput: true,
                    altFormat: "F j, Y",
                    dateFormat: "Y-m-d",
                });

            })

            // login
            $(document).on('click', '.login_to_continue_order', function(e){
                e.preventDefault();
                let username = $('#username').val();
                let password = $('#password').val();
                let erContainer = $(".error-message");
                erContainer.html('');
                $.ajax({
                    url:"{{ route('order.user.login')}}",
                    data:{username:username,password:password},
                    method:'POST',
                    error:function(res){
                        let errors = res.responseJSON;
                        erContainer.html('<div class="alert alert-danger"></div>');
                        $.each(errors.errors, function(index,value){
                            erContainer.find('.alert.alert-danger').append('<p>'+value+'</p>');
                        });
                    },
                    success: function(res){
                        if(res.status=='success'){
                            window.location.reload();
                        }
                        if(res.status == 'failed'){
                            erContainer.html('<div class="alert alert-danger">'+res.msg+'</div>');
                        }
                    }


                });
            });

            // chat warning
            $(document).on('click','.contact_warning_chat_message',function(){
                toastr_warning_js("{{__('Please login as a client to chat with freelancer.')}}")
                return false;
            })


            //get user type
            @php
                $user_type = '';
                if(Auth::check()){
                    $user_type = Auth::user()->user_type == 1 ? 'client' : 'freelancer';
                    $user_type = route($user_type .'.'. 'wallet.history');
                }
            @endphp

            function normalizePrice(input, thousand_sep = '.', decimal_sep = ',') {
                if (!input) return 0;

                // Remove currency symbols or spaces
                let cleaned = input.replace(/[^0-9\.,]/g, '');

                // Remove thousand separators and replace decimal
                cleaned = cleaned
                    .replace(new RegExp('\\' + thousand_sep, 'g'), '')
                    .replace(new RegExp('\\' + decimal_sep), '.');

                let result = parseFloat(cleaned);
                return isNaN(result) ? 0 : result;
            }

            // Function to format price for display
            function formatPrice(price) {
                let thousand_separator = "{{ get_static_option('site_currency_thousand_separator') }}";
                let decimal_separator = "{{ get_static_option('site_currency_decimal_separator') }}";

                let formatted = price.toLocaleString('de-DE', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });

                // Replace separators according to settings if needed
                if (thousand_separator !== '.' || decimal_separator !== ',') {
                    formatted = formatted
                        .replace(/\./g, 'TEMP_THOUSAND')
                        .replace(/,/g, decimal_separator)
                        .replace(/TEMP_THOUSAND/g, thousand_separator);
                }

                return formatted;
            }

            // Function to calculate total price including extras (always from original price)
            function updateTotalPrice(tabId) {
                if (!tabId || !originalPrices[tabId]) return 0;

                let basePrice = originalPrices[tabId];
                let extraPrice = 0;

                // Calculate extra services price for current tab
                $(`#${tabId} .${tabId.toLowerCase()}-extra-checkbox:checked`).each(function() {
                    let price = parseFloat($(this).data('price')) || 0;
                    extraPrice += price;
                });

                let totalPrice = basePrice + extraPrice;

                // Update price display
                let priceElement = $(`#${tabId} .project-preview-tab-inner-item:last .price span:last`);
                priceElement.text(site_default_currency_symbol + formatPrice(totalPrice));

                // Store total price for transaction calculation
                $(`#${tabId}`).attr('data-total-price', totalPrice);

                return totalPrice;
            }

            // Function to calculate and display transaction fee
            function updateTransactionFee(currentTab) {
                let totalPrice = parseFloat($(`#${currentTab}`).attr('data-total-price')) || originalPrices[
                    currentTab] || 0;

                <?php
                $transaction_type = get_static_option('transaction_fee_type') ?? '';
                $transaction_charge = get_static_option('transaction_fee_charge') ?? 0;
                ?>

                if ("{{ $transaction_charge > 0 }}") {
                    $('.show_hide_transaction_section').removeClass('d-none');

                    let transaction_type = "{{ $transaction_type }}";
                    let transaction_charge = parseFloat("{{ $transaction_charge }}");

                    let transaction_amount = transaction_type === 'fixed' ?
                        transaction_charge :
                        (totalPrice * transaction_charge / 100);

                    $('.currency_symbol').text(site_default_currency_symbol);
                    $('.transaction_fee_amount').text(formatPrice(transaction_amount));
                } else {
                    $('.show_hide_transaction_section').addClass('d-none');
                }

                // Update wallet balance warning
                let wallet_balance = {{ Auth::check() ? Auth::user()->user_wallet?->balance ?? 0 : 0 }};
                if (totalPrice > wallet_balance && wallet_balance > 0) {
                    let shortage = totalPrice - wallet_balance;
                    $('.display_balance').html(
                        '<span class="text-danger">{{ __('Wallet Balance Shortage:') }}' +
                        site_default_currency_symbol + formatPrice(shortage) + '</span>');
                    $('.deposit_link').html(
                        '<a href="{{ $user_type }}" target="_blank">{{ __('Deposit') }}</a>');
                } else {
                    $('.display_balance').html('');
                    $('.deposit_link').html('');
                }
            }

            // Handle tab switching
            $(document).on('click', '.project-preview-tab .tabs li:not(.pe-none)', function(e) {
                e.preventDefault();

                let tabId = $(this).attr('data-tab');

                // Clear all extra service selections from all tabs before switching
                $('.basic-extra-checkbox, .standard-extra-checkbox, .premium-extra-checkbox').prop(
                    'checked', false);

                // Update active states
                $('.project-preview-tab .tabs li').removeClass('active');
                $('.tab-content-item').removeClass('active');
                $(this).addClass('active');
                $(`#${tabId}`).addClass('active');

                // Recalculate prices for the new active tab (will show original price since no extras selected)
                setTimeout(() => {
                    updateTotalPrice(tabId);
                }, 10);
            });

            // Handle extra service checkbox changes
            $(document).on('change',
                '.basic-extra-checkbox, .standard-extra-checkbox, .premium-extra-checkbox',
                function() {
                    let tabId = $(this).closest('.tab-content-item').attr('id');
                    updateTotalPrice(tabId);

                    // If this is the active tab, update transaction fees
                    if ($(this).closest('.tab-content-item').hasClass('active')) {
                        updateTransactionFee(tabId);
                    }
                });

            // Handle order button click
            $(document).on('click', '.basic_standard_premium', function() {
                let currentTab = $('.project-preview-tab .tabs .active').attr("data-tab");
                let project_id = $(this).data('project_id');

                $('.set_basic_standard_premium_type').text(currentTab);
                $('#project_id_for_order').val(project_id);
                $('#basic_standard_premium_type').val(currentTab);

                // Fresh calculation for the modal
                let finalPrice = updateTotalPrice(currentTab);
                updateTransactionFee(currentTab);
            });

            // Handle form submission - only pass selected extra service IDs
            $('#prevent_multiple_order_submit').on('submit', function(e) {
                $('#order_now_only_for_load_spinner').attr('disabled', 'true');

                $('input[name^="selected_extras"]').remove();

                let activeTab = $('.project-preview-tab .tabs .active').attr("data-tab");
                $(`#${activeTab} .${activeTab.toLowerCase()}-extra-checkbox:checked`).each(
                    function() {
                        let extraId = $(this).attr('name').match(/\d+/)[
                            0];
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'selected_extras[]',
                            value: extraId
                        }).appendTo('#prevent_multiple_order_submit');
                    });
            });

            //milestone show hide
            $(document).on('click', '#pay_by_milestone', function() {
                if ($(this).prop('checked') == true) {
                    $('.milestone_wrapper').removeClass('d-none');
                    $('#pay_by_milestone').val('pay-by-milestone');
                } else {
                    $('.milestone_wrapper').addClass('d-none');
                    $('#pay_by_milestone').val('');
                }
            });

            //description show hide
            $(document).on('click', '#order_description_btn', function() {
                if ($(this).prop('checked') == true) {
                    $('.description_wrapper').removeClass('d-none');
                } else {
                    $('.description_wrapper').addClass('d-none');
                }
            });

            $(document).on('click', '.wallet_selected_payment_gateway , .payment_getway_image ul li',
                function() {
                    let gateway = $('#order_from_user_wallet').val();
                    if (gateway == 'wallet' || gateway == 'manual_payment') {
                        $('.show_hide_transaction_section').addClass('d-none');
                    } else {
                        $('.show_hide_transaction_section').removeClass('d-none');
                    }
                });

            //load spinner
            $(document).on('click', '#order_now_only_for_load_spinner', function() {
                let manual_payment = $('#order_from_user_wallet').val();
                if (manual_payment == 'manual_payment') {
                    let manual_payment_image = $('input[name="manual_payment_image"]').val();
                    if (manual_payment_image == '') {
                        toastr_warning_js("{{ __('Image field is required') }}")
                        return false
                    }
                }

                $('#order_create_load_spinner').html('<i class="fas fa-spinner fa-pulse"></i>')
                setTimeout(function () {
                    $('#order_create_load_spinner').html('');
                }, 10000);
            });

        });
    }(jQuery));

    //toastr warning
    function toastr_warning_js(msg){
        Command: toastr["warning"](msg, "Warning !")
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
    }
</script>
