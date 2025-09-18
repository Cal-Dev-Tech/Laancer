@extends('frontend.layout.master')
@section('site_title', __('User Login'))
@section('style')
    <style>
        .autoLogin {
            border: none;
            background-color: green;
            color: #fff;
            border-radius: 5px;
            padding: 5px;
            transition: all 300ms;
            cursor: pointer;
        }
    </style>
@endsection

@section('content')
    <!-- login Area Starts -->
    <section class="login-area pat-100 pab-100">
        <div class="container">
            <div class="row gy-5 align-items-center justify-content-between">
                <div class="col-lg-5">
                    <div class="login-wrapper">
                        <div class="login-wrapper-contents">
                            <h3 class="login-wrapper-contents-title">
                                {{ get_static_option('login_page_title') ?? __('Log In') }}</h3>

                            <x-validation.error />
                            <div class="error-message"></div>

                            <form class="login-wrapper-contents-form custom-form" id="user_auto_login_form" method="post"
                                action="{{ route('user.login') }}">
                                @csrf
                                <div class="single-input mt-4">
                                    <label class="label-title mb-3">{{ __('Email Or User Name') }}</label>
                                    <input class="form--control" type="text" name="username" id="username"
                                        placeholder="{{ __('Email Or User Name') }}">
                                </div>
                                <div class="single-input mt-4">
                                    <label class="label-title mb-3"> {{ __('Password') }} </label>
                                    <div class="single-input-inner">
                                        <input class="form--control" type="password" name="password" id="password"
                                            placeholder="{{ __('Type Password') }}">
                                        <div class="icon toggle-password">
                                            <div class="show-icon"> <i class="fas fa-eye-slash"></i> </div>
                                            <span class="hide-icon"> <i class="fas fa-eye"></i> </span>
                                        </div>
                                    </div>
                                </div>
                                <button id="signin_form" class="submit-btn w-100 mt-4" type="submit">
                                    {{ get_static_option('login_page_button_title') ?? __('Sign In Now') }}
                                </button>
                                <span class="account color-light mt-3">{{ __("Don't have an account?") }} <a
                                        class="color-one" href="{{ route('user.register') }}">
                                        {{ __('SignUp Now') }}</a></span>
                            </form>

                            <div class="single-checkbox mt-3">
                                <div class="checkbox-inline">
                                    <input class="check-input" name="remember" type="checkbox" id="check15">
                                    <label class="checkbox-label" for="check15"> {{ __('Remember Me') }} </label>
                                </div>
                                <div class="forgot-password">
                                    <a href="{{ route('user.forgot.password') }}"
                                        class="forgot-btn color-one">{{ __('Forgot Password') }} </a>
                                </div>
                            </div>

                            {{-- demo login --}}
                            @if (url()->current() == 'https://xilancer.xgenious.com/login')
                                <div class="mt-5">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Username') }}</th>
                                                <th>{{ __('Password') }}</th>
                                                <th>{{ __('Action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody style="border-top: none;">
                                            <tr>
                                                <td id="freelancer_username">freelancer</td>
                                                <td id="freelancer_password">12345678</td>
                                                <td><button type="button" class="autoLogin"
                                                        id="freelancerLogin">{{ __('Freelancer Login') }}</button></td>
                                            </tr>
                                            <tr>
                                                <td id="client_username">client</td>
                                                <td id="client_password">12345678</td>
                                                <td><button type="button" class="autoLogin"
                                                        id="clientLogin">{{ __('Client Login') }}</button></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            {{-- Social login --}}
                            @if (get_static_option('login_page_social_login_enable_disable') == 'on')
                                <div class="login-bottom-contents">
                                    <div class="or-contents mb-3">
                                        <span class="or-contents-para"> {{ __('Or') }} </span>
                                    </div>
                                    <div class="login-others social-login-btn">
                                        <div class="login-others-single">
                                            <a href="{{ route('login.google.redirect') }}" data-provider="google"
                                                class="login-others-single-btn w-100">
                                                <i class="fa-brands fa-google"></i>
                                                <span class="login-para"> {{ __('Sign In With Google') }} </span>
                                            </a>
                                        </div>
                                        @if (!moduleExists('CurrencySwitcher'))
                                            <div class="login-others-single">
                                                <a href="{{ route('login.facebook.redirect') }}" data-provider="facebook"
                                                    class="login-others-single-btn w-100">
                                                    <i class="fa-brands fa-facebook"></i>
                                                    <span class="login-para"> {{ __('Sign In With Facebook') }} </span>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="login-right">
                        <div class="login-right-item">
                            <div class="login-right-shapes">
                                <div class="login-right-thumb">
                                    @if (empty(get_static_option('login_page_sidebar_image')))
                                        <img src="{{ asset('assets/static/single-page/login_page.png') }}" alt="loginImg">
                                    @else
                                        {!! render_image_markup_by_attachment_id(get_static_option('login_page_sidebar_image')) !!}
                                    @endif
                                </div>
                            </div>
                            <div class="login-right-contents text-white">
                                <h4 class="login-right-contents-title">
                                    {{ get_static_option('login_page_sidebar_title') ?? __('Login and start discover') }}
                                </h4>
                                <p class="login-right-contents-para">
                                    {{ get_static_option('login_page_sidebar_description') ?? __('Once login you will see the magic of xilancer marketplace.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- login Area end -->

    <!-- Social Login Role Selection Modal -->
    <div class="modal fade" id="socialRoleModal" tabindex="-1" aria-labelledby="socialRoleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-4">
                <h5 class="modal-title mb-3">{{ get_static_option('register_page_choose_role_title') ?? __('Choose a Role') }}</h5>

                <div class="choose-account-flex d-flex mt-4">
                    <div class="choose-account-single select-social-role" data-role="2">
                        <div class="choose-account-single-thumb">
                            <svg width="80" height="80" viewBox="0 0 80 80" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M40 70.625C41.2944 70.625 42.3438 69.5757 42.3438 68.2812C42.3438 66.9868 41.2944 65.9375 40 65.9375C38.7056 65.9375 37.6562 66.9868 37.6562 68.2812C37.6562 69.5757 38.7056 70.625 40 70.625Z"
                                    fill="#6176F6" />
                                <path
                                    d="M2.34375 80H77.6562C78.9506 80 80 78.9506 80 77.6562V75.3125C80 70.09 75.6075 65.9375 70.4688 65.9375C69.6594 65.9375 68.8623 66.0442 68.0941 66.2456L70.4238 54.5192C70.5605 53.8311 70.3817 53.1178 69.9366 52.5755C69.4914 52.0331 68.8267 51.7188 68.125 51.7188H65.7812V49.375C65.7812 42.9133 60.5242 37.6562 54.0625 37.6562H49.375C48.0827 37.6562 47.0312 36.6048 47.0312 35.3125V35.3061C49.8756 33.1659 51.7188 29.7631 51.7188 25.9375C51.7188 24.4055 51.7188 13.3697 51.7188 11.7188C51.7188 7.84172 48.5645 4.6875 44.6875 4.6875H37.6562C36.3639 4.6875 35.3125 3.63609 35.3125 2.34375C35.3125 1.04937 34.2631 0 32.9688 0H30.625C25.4556 0 21.25 4.20563 21.25 9.375C21.25 13.7353 24.2423 17.41 28.2812 18.4533V25.9375C28.2812 29.7631 30.1244 33.1659 32.9688 35.3061V35.3125C32.9688 36.6048 31.9173 37.6562 30.625 37.6562H25.9375C19.4758 37.6562 14.2188 42.9133 14.2188 49.375V51.7188H11.875C11.1733 51.7188 10.5086 52.0331 10.0634 52.5755C9.61828 53.1178 9.43953 53.8311 9.57625 54.5192L11.9059 66.2456C11.1377 66.0442 10.3406 65.9375 9.53125 65.9375C4.39141 65.9375 0 70.0912 0 75.3125V77.6562C0 78.9506 1.04937 80 2.34375 80V80ZM65.2698 56.4062L61.5136 75.3125H18.4864L14.7302 56.4062H65.2698ZM70.4688 70.625C73.0944 70.625 75.3125 72.7716 75.3125 75.3125H66.2927L66.897 72.2706C67.932 71.1598 69.108 70.625 70.4688 70.625ZM40 32.9688C36.123 32.9688 32.9688 29.8145 32.9688 25.9375V18.75H47.0312V25.9375C47.0312 29.8145 43.877 32.9688 40 32.9688ZM25.9375 9.375C25.9375 6.79031 28.0403 4.6875 30.625 4.6875H31.0266C31.9939 7.41578 34.6006 9.375 37.6562 9.375H44.6875C45.9798 9.375 47.0312 10.4264 47.0312 11.7188V14.0625H30.625C28.0403 14.0625 25.9375 11.9597 25.9375 9.375ZM40 37.6562C40.9102 37.6562 41.7952 37.5486 42.6463 37.3513C43.2692 39.4036 44.8073 41.0602 46.7834 41.8467C45.9702 44.8305 43.238 47.0312 40 47.0312C36.762 47.0312 34.0298 44.8305 33.2166 41.8467C35.1927 41.0602 36.7308 39.4036 37.3537 37.3513C38.2048 37.5486 39.0898 37.6562 40 37.6562ZM18.9062 49.375C18.9062 45.498 22.0605 42.3438 25.9375 42.3438H28.517C29.6058 47.6863 34.3408 51.7188 40 51.7188C45.6592 51.7188 50.3941 47.6863 51.483 42.3438H54.0625C57.9395 42.3438 61.0938 45.498 61.0938 49.375V51.7188H18.9062V49.375ZM9.53125 70.625C10.8784 70.625 12.0572 71.1489 13.103 72.2706L13.7073 75.3125H4.6875C4.6875 72.7716 6.90562 70.625 9.53125 70.625V70.625Z"
                                    fill="#667085" />
                            </svg>
                        </div>
                        <div class="choose-account-single-contents mt-3">
                            <h6 class="choose-account-single-contents-title">{{ get_static_option('register_page_choose_join_freelancer_title') ?? __('Join as a freelancer') }}</h6>
                        </div>
                    </div>
                    <div class="choose-account-single jselect-social-role" data-role="1">
                        <div class="choose-account-single-thumb">
                            <svg width="62" height="80" viewBox="0 0 62 80" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M57.4738 56.8037C54.8975 53.6169 51.272 51.3891 47.265 50.5305L39.5938 48.8866V44.4384C43.6458 41.9366 46.5327 37.7223 47.2381 32.8125H48.1875C52.0645 32.8125 55.2188 29.6583 55.2188 25.7812C55.2188 21.9042 52.0645 18.75 48.1875 18.75H47.4062V8.59375C47.4062 3.85516 43.5511 0 38.8125 0H20.0625C13.6008 0 8.34375 5.25703 8.34375 11.7188C8.34375 14.6011 9.39172 17.242 11.1241 19.2853C8.57719 20.3433 6.78125 22.8559 6.78125 25.7812C6.78125 29.6583 9.93547 32.8125 13.8125 32.8125H14.7619C15.4673 37.7223 18.3542 41.9366 22.4062 44.4384V48.8866L14.735 50.5305C10.728 51.3891 7.1025 53.617 4.52625 56.8037C1.95 59.9905 0.53125 64.0023 0.53125 68.1003V77.6562C0.53125 78.9506 1.58062 80 2.875 80H59.125C60.4194 80 61.4688 78.9506 61.4688 77.6562V68.1003C61.4688 64.0023 60.05 59.9905 57.4738 56.8037ZM47.4062 23.4375H48.1875C49.4798 23.4375 50.5312 24.4889 50.5312 25.7812C50.5312 27.0736 49.4798 28.125 48.1875 28.125H47.4062V23.4375ZM14.5938 28.125H13.8125C12.5202 28.125 11.4688 27.0736 11.4688 25.7812C11.4688 24.4889 12.5202 23.4375 13.8125 23.4375H14.5938V28.125ZM17.7188 14.8438V18.3484C14.9905 17.3813 13.0312 14.7745 13.0312 11.7188C13.0312 7.84172 16.1855 4.6875 20.0625 4.6875H38.8125C40.9664 4.6875 42.7188 6.43984 42.7188 8.59375V12.5H20.0625C18.7681 12.5 17.7188 13.5494 17.7188 14.8438ZM19.2812 30.4688V23.4086C19.5398 23.4258 19.7997 23.4375 20.0625 23.4375C21.3569 23.4375 22.4062 22.3881 22.4062 21.0938V17.1875H42.7188V30.4688C42.7188 36.9305 37.4617 42.1875 31 42.1875C24.5383 42.1875 19.2812 36.9305 19.2812 30.4688ZM22.7877 75.3125H5.21875V68.1003C5.21875 61.8791 9.63406 56.4173 15.717 55.1139L23.1898 53.5125L25.4628 60.3314L22.7877 75.3125ZM27.5494 75.3125L29.8373 62.5H32.1627L34.4506 75.3125H27.5494ZM28.4223 54.3867C29.2756 54.5219 30.1377 54.5913 31 54.5913C31.8623 54.5913 32.7244 54.522 33.5777 54.3867L32.4356 57.8125H29.5642L28.4223 54.3867ZM34.9062 49.2355C32.3877 50.1292 29.6123 50.1292 27.0938 49.2355V46.4039C28.3463 46.7109 29.6542 46.875 31 46.875C32.3458 46.875 33.6538 46.7109 34.9062 46.4039V49.2355ZM56.7812 75.3125H39.2123L36.5372 60.3314L38.8102 53.5125L46.283 55.1139C52.3659 56.4173 56.7812 61.8789 56.7812 68.1003V75.3125Z"
                                    fill="#667085" />
                            </svg>
                        </div>
                        <div class="choose-account-single-contents mt-3">
                            <h6 class="choose-account-single-contents-title">{{ get_static_option('register_page_choose_join_client_title') ?? __('Join as a Client') }}</h6>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-primary w-100 mt-3"
                    id="confirmSocialRole">{{ __('Continue') }}</button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                let selectedSocialProvider = null;
                let selectedUserRole = null;

                // Auto login demo
                $('#clientLogin').on('click', function() {
                    $('#username').val($('#client_username').text());
                    $('#password').val($('#client_password').text());
                    $('#signin_form').trigger('click');
                });
                $('#freelancerLogin').on('click', function() {
                    $('#username').val($('#freelancer_username').text());
                    $('#password').val($('#freelancer_password').text());
                    $('#signin_form').trigger('click');
                });

                // Normal login
                $('#signin_form').on('click', function(e) {
                    e.preventDefault();
                    let el = $(this);
                    let erContainer = $(".error-message");
                    erContainer.html('');
                    el.text('{{ __('Please Wait..') }}');

                    $.ajax({
                        url: "{{ route('user.login') }}",
                        type: "POST",
                        data: {
                            username: $('#username').val(),
                            password: $('#password').val(),
                            remember: $('#remember').is(':checked') ? 1 : 0,
                        },
                        error: function(data) {
                            var errors = data.responseJSON;
                            erContainer.html('<div class="alert alert-danger"></div>');
                            $.each(errors.errors, function(index, value) {
                                erContainer.find('.alert.alert-danger').append(
                                    '<p>' + value + '</p>');
                            });
                            el.text('{{ __('Login') }}');
                        },
                        success: function(data) {
                            $('.alert.alert-danger').remove();
                            if (data.status == 'client-login') {
                                el.text('{{ __('Redirecting') }}..');
                                erContainer.html('<div class="alert alert-' + data.type +
                                    '">' + data.msg + '</div>');
                                let redirectPath = "{{ route('client.dashboard') }}";
                                @if (!empty(request()->get('return')))
                                    redirectPath =
                                        "{{ url('/' . request()->get('return')) }}";
                                @endif
                                window.location = redirectPath;
                            } else if (data.status == 'freelancer-login') {
                                el.text('{{ __('Redirecting') }}..');
                                erContainer.html('<div class="alert alert-' + data.type +
                                    '">' + data.msg + '</div>');
                                let redirectPath = "{{ route('freelancer.dashboard') }}";
                                @if (!empty(request()->get('return')))
                                    redirectPath =
                                        "{{ url('/' . request()->get('return')) }}";
                                @endif
                                window.location = redirectPath;
                            } else {
                                erContainer.html('<div class="alert alert-' + data.type +
                                    '">' + data.msg + '</div>');
                                el.text('{{ __('Login') }}');
                            }
                        }
                    });
                });

                // Social login
                $('.login-others-single-btn').on('click', function(e) {
                    e.preventDefault();
                    selectedSocialProvider = $(this).data('provider');
                    selectedUserRole = null;

                    // Show modal
                    $('#socialRoleModal').modal('show');
                });

                // Select role
                $(document).on('click', '.select-social-role', function() {
                    $('.select-social-role').removeClass('active');
                    $(this).addClass('active');
                    selectedUserRole = $(this).data('role');
                });

                // Confirm role
                $('#confirmSocialRole').on('click', function() {
                    if (!selectedUserRole) {
                        toastr_warning_js("{{ __('Please select account type to continue') }}");
                        return;
                    }

                    let redirectUrl = '';
                    if (selectedSocialProvider === 'google') {
                        redirectUrl = '{{ route('login.google.redirect') }}' + '?user_type=' +
                            selectedUserRole;
                    } else if (selectedSocialProvider === 'facebook') {
                        redirectUrl = '{{ route('login.facebook.redirect') }}' + '?user_type=' +
                            selectedUserRole;
                    }

                    if (redirectUrl) {
                        window.location.href = redirectUrl;
                    }
                });
            });
        }(jQuery));
    </script>
@endsection
