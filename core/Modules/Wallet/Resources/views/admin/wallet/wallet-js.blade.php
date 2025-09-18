<script>
    (function($) {
        "use strict";
        $(document).ready(function() {
            // pagination
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                let page = $(this).attr('href').split('page=')[1];
                histories(page);
            });

            function histories(page) {
                $.ajax({
                    url: "{{ route('admin.wallet.paginate.data') . '?page=' }}" + page,
                    success: function(res) {
                        $('.search_result').html(res);
                    }
                });
            }

            // search category
            $(document).on('keyup', '#string_search', function() {
                let string_search = $(this).val();
                $.ajax({
                    url: "{{ route('admin.wallet.search') }}",
                    method: 'GET',
                    data: {
                        string_search: string_search
                    },
                    success: function(res) {
                        if (res.status == 'nothing') {
                            $('.search_result').html(
                                '<h3 class="text-center text-danger">' +
                                "{{ __('Nothing Found') }}" + '</h3>');
                        } else {
                            $('.search_result').html(res);
                        }
                    }
                });
            })


            // Initialize Select2
            let userSelect = $('#userSelect').select2({
                placeholder: "{{ __('Search active user by name or email') }}",
                ajax: {
                    url: "{{ route('admin.user.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1,
                dropdownParent: $('#addWalletCreditModal')
            });

            // handle form submit
            $('#addWalletCreditForm').on('submit', function(e) {
                e.preventDefault();

                let $btn = $('#creditWalletBtn');
                let $spinner = $btn.find('.spinner-border');
                let $btnText = $btn.find('.btn-text');

                // Show spinner
                $spinner.removeClass('d-none');
                $btnText.text('{{ __('Processing...') }}');
                $btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.user.wallet.credit') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(resp) {
                        // Hide spinner
                        $spinner.addClass('d-none');
                        $btnText.text('{{ __('Credit Wallet') }}');
                        $btn.prop('disabled', false);

                        if (resp.status === 'success') {
                            toastr_success_js(resp.message);
                            $('#addWalletCreditModal').modal('hide');
                            $('.search_result').load(location.href + ' .search_result');
                        } else {
                            toastr_warning_js(resp.message);
                        }
                    },
                    error: function(xhr) {
                        $spinner.addClass('d-none');
                        $btnText.text('{{ __('Credit Wallet') }}');
                        $btn.prop('disabled', false);

                        let message = "{{ __('Something went wrong') }}";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        toastr_warning_js(message);
                    }
                });
            });

            // When modal closes
            $('#addWalletCreditModal').on('hidden.bs.modal', function() {
                $('#addWalletCreditForm')[0].reset();

                userSelect.val(null).trigger('change');
                userSelect.find('option').remove();
            });

            // Initialize Select2
            let deductUserSelect = $('#deductUserSelect').select2({
                placeholder: "{{ __('Search active user by name or email') }}",
                ajax: {
                    url: "{{ route('admin.user.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1,
                dropdownParent: $('#deductWalletModal')
            });

            // Handle deduction form submit
            $('#deductWalletForm').on('submit', function(e) {
                e.preventDefault();

                let $btn = $('#deductWalletBtn');
                let $spinner = $btn.find('.spinner-border');
                let $btnText = $btn.find('.btn-text');

                $spinner.removeClass('d-none');
                $btnText.text('{{ __('Processing...') }}');
                $btn.prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.user.wallet.deduct') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(resp) {
                        $spinner.addClass('d-none');
                        $btnText.text('{{ __('Deduct Wallet') }}');
                        $btn.prop('disabled', false);

                        if (resp.status === 'success') {
                            toastr_success_js(resp.message);
                            $('#deductWalletModal').modal('hide');
                            $('.search_result').load(location.href + ' .search_result');
                        } else {
                            toastr_warning_js(resp.message);
                        }
                    },
                    error: function(xhr) {
                        $spinner.addClass('d-none');
                        $btnText.text('{{ __('Deduct Wallet') }}');
                        $btn.prop('disabled', false);

                        let message = "{{ __('Something went wrong') }}";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        toastr_warning_js(message);
                    }
                });
            });

            // Reset modal when closed
            $('#deductWalletModal').on('hidden.bs.modal', function() {
                $('#deductWalletForm')[0].reset();
                deductUserSelect.val(null).trigger('change');
                deductUserSelect.find('option').remove();
            });

        });
    }(jQuery));
</script>
