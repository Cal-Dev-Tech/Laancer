<script>
    (function ($) {
        "use strict";
        $(document).ready(function () {

            //prevent multiple submit
            $('#job_proposal_form').on('submit', function () {
                $('.send_job_proposal').attr('disabled', 'true');
            });

            // proposal validate
            $(document).on('click', '.send_job_proposal', function(e){
                let amount = $('#job_proposal_form #amount').val();
                let duration = $('#job_proposal_form #duration').val();
                let revision = $('#job_proposal_form #revision').val();
                let cover_letter = $('#job_proposal_form #cover_letter').val();

                if(amount == '' || duration == '' || cover_letter == '' || revision == ''){
                    toastr_warning_js("{{ __('Except attachment all fields required!') }}")
                    return false;
                }else if(amount<1){
                    toastr_warning_js("{{ __('Amount must be greater than 1.') }}")
                    return false;
                }else if(cover_letter.length<10){
                    toastr_warning_js("{{ __('Cover letter must be greater than 10 characters.') }}")
                    return false;
                }else{
                    $('#send_proposal_load_spinner').html('<i class="fas fa-spinner fa-pulse"></i>')

                }

            });

            //tooltip
            $("body").tooltip({ selector: '[data-toggle=tooltip]' });

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

        });
    }(jQuery));
</script>
