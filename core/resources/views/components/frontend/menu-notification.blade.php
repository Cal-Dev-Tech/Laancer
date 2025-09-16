@if (Auth::guard('web')->user()->user_type == 1 && Session::get('user_role') != 'freelancer')
    @php
        $client_notifications = \App\Models\ClientNotification::where('client_id', Auth::guard('web')->user()->id)
            ->latest()
            ->take(200)
            ->get();
        $client_new_notifications = \App\Models\ClientNotification::where('is_read', 'unread')
            ->where('client_id', Auth::guard('web')->user()->id)
            ->latest()
            ->get();
    @endphp
    <div class="navbar-right-item">
        <div class="navbar-right-notification">
            <a href="javascript:void(0)" class="navbar-right-notification-icon">
                <i class="fa-regular fa-bell"></i>
                @if ($client_new_notifications->count() > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $client_new_notifications->count() ?? 0 }}</span>
                @endif
            </a>
            <div class="navbar-right-notification-wrapper">
                <div class="navbar-right-notification-wrapper-list">
                    @if ($client_notifications->count() > 0)
                        @foreach ($client_notifications as $notification)
                            <span href="javascript:void(0)"
                                  class="navbar-right-notification-wrapper-list-item click-notification">
                                                <div class="navbar-right-notification-wrapper-list-item-left">
                                                    <div class="navbar-right-notification-wrapper-list-item-icon decline">
                                                        @if($notification->is_read == 'read')
                                                            <i class="fa-regular fa-bell opacity-25 pe-none"></i>
                                                        @else
                                                            <i class="fa-regular fa-bell"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="navbar-right-notification-wrapper-list-item-content">
                                                    @if ($notification->type == 'Offer')
                                                        <a
                                                                href="{{ route('client.offer.details', $notification->identity) }}?mark_as_read=true">
                                                            <span
                                                                    class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                        </a>
                                                        <span
                                                                class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif
                                                    @if ($notification->type == 'Proposal')
                                                        <a
                                                                href="{{ route('client.job.proposal.details', $notification->identity) }}?mark_as_read=true">
                                                            <span
                                                                    class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                        </a>
                                                        <span
                                                                class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif
                                                    @if ($notification->type == 'Order')
                                                        <a
                                                                href="{{ route('client.order.details', $notification->identity) }}?mark_as_read=true">
                                                            <span
                                                                    class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                        </a>
                                                        <span
                                                                class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif
                                                    @if ($notification->type == 'Job')
                                                        <a
                                                                href="{{ route('client.job.details', $notification->identity) }}?mark_as_read=true">
                                                            <span
                                                                    class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                        </a>
                                                        <span
                                                                class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif
                                                    @if ($notification->type == 'Ticket Update' || $notification->type == 'Ticket')
                                                        <a href="{{ route('client.ticket.details',$notification->identity) }}?mark_as_read=true">
                                                        <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                    </a>
                                                        <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif
                                                    @if ($notification->type == 'Subscription')
                                                        <a href="{{ route('client.subscriptions.all') }}?mark_as_read=true">
                                                        <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                        </a>
                                                        <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif
                                                    @if ($notification->type == 'Deposit')
                                                        <a href="{{ route('client.wallet.history') }}?mark_as_read=true">
                                                        <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                        </a>
                                                        <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif
                                                </div>
                                            </span>
                        @endforeach
                    @else
                        <a href="javascript:void(0)"
                           class="navbar-right-notification-wrapper-list-item click-notification">
                            <div class="navbar-right-notification-wrapper-list-item-left">
                                <div class="navbar-right-notification-wrapper-list-item-icon decline">
                                    <i class="fa-regular fa-bell"></i>
                                </div>
                            </div>
                            <div class="navbar-right-notification-wrapper-list-item-content">
                                <span class="navbar-right-notification-wrapper-list-item-content-title">
                                    <strong>{{ __('No Notification') }}</strong>
                                </span>
                            </div>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@else
    @if (Auth::guard('web')->user()->user_type == 2 && Session::get('user_role') == 'client')
        {{--this is for freelancer he switch as a client--}}
        @php
            $client_notifications = \App\Models\ClientNotification::where('client_id', Auth::guard('web')->user()->id)
                ->latest()
                ->take(200)
                ->get();

            $client_new_notifications = \App\Models\ClientNotification::where('is_read', 'unread')
            ->where('client_id', Auth::guard('web')->user()->id)
            ->latest()
            ->get();
        @endphp
        <div class="navbar-right-item">
            <div class="navbar-right-notification">
                <a href="javascript:void(0)" class="navbar-right-notification-icon">
                    <i class="fa-regular fa-bell"></i>
                    @if ($client_new_notifications->count() > 0)
                        <span
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $client_new_notifications->count() ?? 0 }}</span>
                    @endif
                </a>
                <div class="navbar-right-notification-wrapper">
                    <div class="navbar-right-notification-wrapper-list">
                        @if ($client_notifications->count() > 0)
                            @foreach ($client_notifications as $notification)
                                <span href="javascript:void(0)"
                                      class="navbar-right-notification-wrapper-list-item click-notification">
                                                <div class="navbar-right-notification-wrapper-list-item-left">
                                                    <div class="navbar-right-notification-wrapper-list-item-icon decline">
                                                         @if($notification->is_read == 'read')
                                                            <i class="fa-regular fa-bell opacity-25 pe-none"></i>
                                                        @else
                                                            <i class="fa-regular fa-bell"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="navbar-right-notification-wrapper-list-item-content">
                                                    @if ($notification->type == 'Offer')
                                                        <a
                                                                href="{{ route('client.offer.details', $notification->identity) }}?mark_as_read=true">
                                                            <span
                                                                    class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                        </a>
                                                        <span
                                                                class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif
                                                    @if ($notification->type == 'Proposal')
                                                        <a
                                                                href="{{ route('client.job.proposal.details', $notification->identity) }}?mark_as_read=true">
                                                            <span
                                                                    class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                        </a>
                                                        <span
                                                                class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif
                                                    @if ($notification->type == 'Order')
                                                        <a
                                                                href="{{ route('client.order.details', $notification->identity) }}?mark_as_read=true">
                                                            <span
                                                                    class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                        </a>
                                                        <span
                                                                class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif
                                                    @if ($notification->type == 'Job')
                                                        <a
                                                                href="{{ route('client.job.details', $notification->identity) }}?mark_as_read=true">
                                                            <span
                                                                    class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                        </a>
                                                        <span
                                                                class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif

                                                        @if ($notification->type == 'Email Verify')
                                                            <a href="javascript:void(0)">
                                                                <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                            </a>
                                                            <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                        @endif
                                                        @if ($notification->type == 'Account')
                                                            <a href="javascript:void(0)">
                                                            <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                        </a>
                                                            <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                        @endif

                                                    @if ($notification->type == 'Identity Verify')
                                                        <a href="javascript:void(0)">
                                                            <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                        </a>
                                                        <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif
                                                    @if ($notification->type == 'Ticket Update' || $notification->type == 'Ticket')
                                                        <a href="{{ route('client.ticket.details',$notification->identity) }}?mark_as_read=true">
                                                        <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                    </a>
                                                        <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif
                                                    @if ($notification->type == 'Subscription')
                                                        <a href="{{ route('client.subscriptions.all') }}?mark_as_read=true">
                                                            <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                        </a>
                                                        <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                                    @endif
                                                </div>
                                            </span>
                            @endforeach
                        @else
                            <a href="javascript:void(0)"
                               class="navbar-right-notification-wrapper-list-item click-notification">
                                <div class="navbar-right-notification-wrapper-list-item-left">
                                    <div class="navbar-right-notification-wrapper-list-item-icon decline">
                                        <i class="fa-regular fa-bell"></i>
                                    </div>
                                </div>
                                <div class="navbar-right-notification-wrapper-list-item-content">
                                <span class="navbar-right-notification-wrapper-list-item-content-title">
                                    <strong>{{ __('No Notification') }}</strong>
                                </span>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        {{--this is for freelancer and also for client when he switch as a freelancer--}}
        @php
            $freelancer_notifications = \App\Models\FreelancerNotification::where('freelancer_id', Auth::guard('web')->user()->id)
                ->latest()->take(200)->get();

             $freelancer_new_notifications = \App\Models\FreelancerNotification::where('is_read', 'unread')
                ->where('freelancer_id', Auth::guard('web')->user()->id)
                ->latest()->get();
        @endphp
        <div class="navbar-right-item">
            <div class="navbar-right-notification">
                <a href="javascript:void(0)" class="navbar-right-notification-icon">
                    <i class="fa-regular fa-bell"></i>
                    @if ($freelancer_new_notifications->count() > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $freelancer_new_notifications->count() ?? 0 }}</span>
                    @endif
                </a>
                <div class="navbar-right-notification-wrapper">
                    <div class="navbar-right-notification-wrapper-list">
                        @if ($freelancer_notifications->count() > 0)
                            @foreach ($freelancer_notifications as $notification)
                                <span href="javascript:void(0)"
                                      class="navbar-right-notification-wrapper-list-item click-notification">
                                    <div
                                            class="navbar-right-notification-wrapper-list-item-left show_and_read_freelancer_notification">
                                        <div class="navbar-right-notification-wrapper-list-item-icon decline">
                                            @if($notification->is_read == 'read')
                                                <i class="fa-regular fa-bell opacity-25 pe-none"></i>
                                            @else
                                                <i class="fa-regular fa-bell"></i>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="navbar-right-notification-wrapper-list-item-content">

                                        @if ($notification->type == 'Offer')
                                            <a
                                                    href="{{ route('freelancer.offer.details', $notification->identity) }}">
                                                <span
                                                        class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                            </a>
                                            <span
                                                    class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                        @endif

                                        @if ($notification->type == 'Order')
                                            <a
                                                    href="{{ route('freelancer.order.details', $notification->identity) }}?mark_as_read=true">
                                                <span
                                                        class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                            </a>
                                            <span
                                                    class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                        @endif

                                        @if ($notification->type == 'Withdraw')
                                            <a href="{{ route('freelancer.wallet.withdraw.history') }}">
                                                <span
                                                        class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                            </a>
                                            <span
                                                    class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                        @endif

                                        @if ($notification->type == 'Reject Project')
                                            <a href="{{ route('freelancer.profile.details',Auth::guard('web')->user()->username) }}?mark_as_read=true">
                                                <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                            </a>
                                            <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                        @endif
                                        @if ($notification->type == 'Activate Project' || $notification->type == 'Inactivate Project' || $notification->type == 'Project' || $notification->type == 'Profile')
                                            <a href="{{ route('freelancer.profile.details',Auth::guard('web')->user()->username) }}?mark_as_read=true">
                                                <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                            </a>
                                            <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                        @endif
                                        @if ($notification->type == 'Email Verify')
                                            <a href="javascript:void(0)">
                                            <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                        </a>
                                            <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                        @endif
                                        @if ($notification->type == 'Account')
                                            <a href="javascript:void(0)">
                                                    <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                </a>
                                            <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                        @endif
                                        @if ($notification->type == 'Identity Verify')
                                            <a href="javascript:void(0)">
                                                    <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                </a>
                                            <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                        @endif
                                        @if ($notification->type == 'New Job')
                                            @php $job_details = \App\Models\JobPost::select('id','user_id','slug')->where('id', $notification->identity)->first() @endphp
                                            @if(isset($job_details))
                                                <a href="{{ route('job.details', ['username' => $job_details->job_creator?->username, 'slug' => $job_details->slug]) }}">
                                                    <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                </a>
                                            @endif
                                            <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                        @endif
                                        @if ($notification->type == 'Ticket Update' || $notification->type == 'Ticket')
                                            <a href="{{ route('freelancer.ticket.details',$notification->identity) }}">
                                                <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                            </a>
                                            <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                        @endif
                                        @if ($notification->type == 'Subscription')
                                            <a href="{{ route('freelancer.subscriptions.all') }}?mark_as_read=true">
                                                        <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                    </a>
                                            <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                        @endif
                                        @if ($notification->type == 'Deposit')
                                            <a href="{{ route('freelancer.wallet.history') }}?mark_as_read=true">
                                                    <span class="navbar-right-notification-wrapper-list-item-content-title">{{ __($notification->message) }}</span>
                                                    </a>
                                            <span class="navbar-right-notification-wrapper-list-item-content-time">{{ $notification->created_at->toFormattedDateString() }}</span>
                                        @endif
                                    </div>
                                </span>
                            @endforeach
                        @else
                            <a href="javascript:void(0)"
                               class="navbar-right-notification-wrapper-list-item click-notification">
                                <div class="navbar-right-notification-wrapper-list-item-left">
                                    <div class="navbar-right-notification-wrapper-list-item-icon decline">
                                        <i class="fa-regular fa-bell"></i>
                                    </div>
                                </div>
                                <div class="navbar-right-notification-wrapper-list-item-content">
                                    <span class="navbar-right-notification-wrapper-list-item-content-title">
                                        <strong>{{ __('No Notification') }}</strong>
                                    </span>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif