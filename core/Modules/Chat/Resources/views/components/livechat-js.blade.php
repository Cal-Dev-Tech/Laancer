<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    if (typeof window.broadcastConfig === 'undefined') {
        window.broadcastConfig = @json(\App\Helper\BroadcastingHelper::getFrontendConfig());
    }
    if (typeof window.appUrl === 'undefined') {
        window.appUrl = "{{ url('/') }}";
    }
</script>

<script>
    if (typeof window.LiveChat === 'undefined') {
        class LiveChat {
            constructor() {
                this.pusher = null;
                this.echo = null;
                this.channel = null;
                this.notificationChannel = null;
                this.appUrl = window.appUrl;
                this.driver = window.broadcastConfig.driver; // "pusher" or "reverb"

                // Initialize based on driver
                this.initialize();
            }

            initialize() {
                try {
                    if (this.driver === 'reverb') {
                        this.initReverb();
                    } else {
                        this.initPusher();
                    }
                } catch (error) {
                    console.error('Failed to initialize LiveChat:', error);
                    // Fallback to Pusher
                    this.initPusher();
                }
            }

            initPusher() {
                const config = window.broadcastConfig;
                if (typeof Pusher === 'undefined') {
                    console.error('Pusher library not loaded');
                    return;
                }

                try {
                    this.driver = 'pusher';
                    this.pusher = new Pusher(config.key, {
                        cluster: config.cluster || 'mt1',
                        encrypted: true,
                        channelAuthorization: {
                            endpoint: `${this.appUrl}/broadcasting/auth`,
                        }
                    });
                } catch (error) {
                    console.error('Failed to initialize Pusher:', error);
                }
            }

            initReverb() {
                const config = window.broadcastConfig;

                if (typeof Pusher === 'undefined') {
                    console.error('Pusher library required for Reverb');
                    return;
                }

                try {
                    this.driver = 'reverb';
                    this.pusher = new Pusher(config.key, {
                        wsHost: config.host || window.location.hostname,
                        wsPort: config.port || 8080,
                        wssPort: config.port || 8080,
                        forceTLS: config.forceTLS || (window.location.protocol === 'https:'),
                        enabledTransports: ['ws', 'wss'],
                        cluster: '',
                        encrypted: config.forceTLS || (window.location.protocol === 'https:'),
                        channelAuthorization: {
                            endpoint: `${this.appUrl}/broadcasting/auth`,
                        }
                    });
                } catch (error) {
                    console.error('Failed to initialize Reverb:', error);
                    // Fallback to regular Pusher
                    this.initPusher();
                }
            }

            getCSRFToken() {
                const token = document.querySelector('meta[name="csrf-token"]');
                return token ? token.getAttribute('content') : '';
            }

            enableLog() {
                if (typeof Pusher !== 'undefined') {
                    Pusher.logToConsole = true;
                }
            }

            createChannel(client_id, freelancer_id, type) {
                let channelName;
                if (type === 'client') {
                    channelName = `private-livechat-freelancer-channel.${client_id}.${freelancer_id}`;
                } else {
                    channelName = `private-livechat-client-channel.${freelancer_id}.${client_id}`;
                }

                this.channel = this.pusher.subscribe(channelName);
            }

            removeChannel(client_id, freelancer_id, type) {
                let channelName;
                if (type === 'client') {
                    channelName = `private-livechat-freelancer-channel.${client_id}.${freelancer_id}`;
                } else {
                    channelName = `private-livechat-client-channel.${freelancer_id}.${client_id}`;
                }

                this.pusher.unsubscribe(channelName);
            }

            bindEvent(eventName, callback) {
                this.channel.bind(eventName, callback);
            }

            createNotificationChannel(user_id) {
                const channelName = `project-private-notifications-${user_id}`;

                this.notificationChannel = this.pusher.subscribe(channelName);
                this.notificationChannel.bind('App\\Events\\ProjectEvent', this.notificationHandler);
            }

            removeNotificationChannel(user_id) {
                const channelName = `project-private-notifications-${user_id}`;
                this.pusher.unsubscribe(channelName);
            }

            createAdminNotificationChannel(admin_id) {
                const channelName = `admin-private-notifications-${admin_id}`;
                this.notificationChannel = this.pusher.subscribe(channelName);
                this.notificationChannel.bind('App\\Events\\AdminEvent', this.adminNotificationHandler);
            }

            removeAdminNotificationChannel(admin_id) {
                const channelName = `admin-private-notifications-${admin_id}`;
                this.pusher.unsubscribe(channelName);
            }

            notificationHandler(data) {
                @if (moduleExists('FakeDataGenerator'))
                    if (isMobile()) {
                        sendPushNotification("{{ __('New Notification') }}", data.message);
                        $(".navbar-right-notification").load(location.href + " .navbar-right-notification");
                        notification_sound();
                    }
                    @if (get_static_option('push_notification_enable_disable') != 'disable')
                        showBrowserNotification("{{ __('New Notification') }}", data.message);
                    @endif
                @else
                    toastr_notification_js(data.message);
                @endif

                notification_sound();
                $(".navbar-right-notification").load(location.href + " .navbar-right-notification");

                if (!isChatboxOpen()) {
                    $(".reload_unseen_message_count").load(location.href + " .reload_unseen_message_count");
                }
            }

            adminNotificationHandler(data) {
                notification_sound();
                $(".dashboard__notification").load(location.href + " .dashboard__notification");
            }
        }
        window.LiveChat = LiveChat;
    }
</script>


<script>
    // Global variables
    let isPageVisible = true;

    // Document Ready Handler - Entry point
    document.addEventListener("DOMContentLoaded", function() {
        // Register service worker first
        registerServiceWorker();

        // Add visibility change listener
        document.addEventListener("visibilitychange", function() {
            isPageVisible = !document.hidden;
        });

        // Handle notification permission
        initializeNotificationPermission();
    });

    // SERVICE WORKER FUNCTIONS
    function registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register("{{ asset('assets/frontend/js/sw.js') }}")
                .then(function(registration) {
                    console.log("Service Worker Registered!", registration);
                })
                .catch(function(err) {
                    console.log("Service Worker Registration Failed!", err);
                });
        } else {
            console.log("Service Worker not supported in this browser");
        }
    }

    // NOTIFICATION PERMISSION FUNCTIONS
    function initializeNotificationPermission() {
        @if (moduleExists('FakeDataGenerator'))
            @if (get_static_option('push_notification_enable_disable') != 'disable')
                @if (Auth::guard('web')->check())
                    // Check if the user has already subscribed
                    const enableNotificationsBtn = document.getElementById('enableNotificationsBtn');
                    if (localStorage.getItem("notificationSubscribed") === "true") {
                        enableNotificationsBtn.disabled = true;
                        enableNotificationsBtn.style.opacity = "0.5";
                        enableNotificationsBtn.style.cursor = "not-allowed";
                        return;
                    }

                    // Select the button by its ID if it exists
                    if (enableNotificationsBtn) {
                        enableNotificationsBtn.addEventListener('click', function() {
                            showPermissionPrompt();
                        });
                    }
                @endif
            @endif
        @endif
    }

    function showPermissionPrompt() {
        Swal.fire({
            title: "{{ get_static_option('push_notification_permission_box_title') ?? 'Subscribe to Notifications!' }}",
            text: "{{ get_static_option('push_notification_permission_box_subtitle') ?? 'We will notify you each and everything related to you.' }}",
            icon: "info",
            showCancelButton: true,
            confirmButtonText: "Allow",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed) {
                requestNotificationPermission();
            }
        });
    }

    function requestNotificationPermission() {
        if (!("Notification" in window)) {
            console.log("Browser does not support notifications");
            return;
        }

        Notification.requestPermission().then(function(permission) {
            if (permission === "granted") {
                console.log("Notification permission granted.");
                localStorage.setItem("notificationSubscribed", "true");
                location.reload();
            } else {
                console.log("Notification permission denied.");
                localStorage.setItem("notificationSubscribed", "false");
                location.reload();
            }
        });
    }

    // PUSH NOTIFICATION FUNCTIONS
    function sendPushNotification(title, message) {
        console.log('Attempting to send push notification');

        // Check if service worker and push are supported
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            console.log('Service Worker or Push API not supported');
            return;
        }

        // Check permission first
        if (Notification.permission !== 'granted') {
            console.log('Notification permission not granted');
            return;
        }

        // Get the active service worker registration
        navigator.serviceWorker.getRegistrations()
            .then(function(registrations) {
                if (registrations.length === 0) {
                    console.log('No service workers registered');
                    return;
                }

                // Find and use the first active service worker
                for (let registration of registrations) {
                    if (registration.active) {
                        console.log('Found active service worker, showing notification');
                        registration.showNotification(title, {
                            body: message,
                            icon: "https://deexial.com/assets/uploads/media-uploader/Deexial.com%20favicon%20logo1704728989.png"
                        });
                        return; // Exit after showing notification
                    }
                }

                console.log('No active service worker found');
            })
            .catch(function(err) {
                console.log('Error getting service worker registrations', err);
            });
    }

    function showBrowserNotification(title, message) {
        // Don't show if chatbox is open and page is visible
        if (isChatboxOpen() && isPageVisible) {
            return;
        }

        // Check browser support
        if (!("Notification" in window)) {
            console.log("Browser does not support desktop notification");
            return;
        }

        // Check permission
        if (Notification.permission !== "granted") {
            console.log("Notification permission not granted");
            return;
        }

        // Create notification
        new Notification(title, {
            body: message,
            icon: "https://deexial.com/assets/uploads/media-uploader/Deexial.com%20favicon%20logo1704728989.png"
        });
    }

    // UTILITY FUNCTIONS
    function isMobile() {
        console.log('User Agent:', navigator.userAgent);
        return /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    }

    function isChatboxOpen() {
        let chatbox = document.querySelector(".chat-wrapper-details");
        if (!chatbox) {
            return false;
        }
        let style = window.getComputedStyle(chatbox);
        return style.display !== "none" && style.visibility !== "hidden" && style.opacity !== "0";
    }

    function notification_sound() {
        let audio = new Audio("{{ asset('assets/uploads/chat_image/sound/facebook_chat.mp3') }}");

        audio.play().then(() => {
            console.log("Audio played successfully");
        }).catch(error => {
            console.log("Audio autoplay blocked, waiting for user interaction", error);

            // Add an event listener to wait for a user interaction
            document.body.addEventListener("click", function playAudio() {
                audio.play();
                document.body.removeEventListener("click", playAudio);
            }, {
                once: true
            });
        });
    }

    // TOASTR NOTIFICATION FUNCTION
    function toastr_notification_js(msg) {
        Command: toastr["warning"](msg, "{{ __('New Notification!') }}")
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

    function updateMessageTimes() {
        $('.chat-wrapper-details-inner-chat-contents-time[data-timestamp]').each(function () {
            const timestamp = parseInt($(this).attr('data-timestamp'));
            if (!timestamp) return;

            const now = Math.floor(Date.now() / 1000);
            const diff = now - timestamp;

            let text;
            if (diff < 60) {
                text = `${diff} second${diff !== 1 ? 's' : ''} ago`;
            } else if (diff < 3600) {
                const mins = Math.floor(diff / 60);
                text = `${mins} minute${mins !== 1 ? 's' : ''} ago`;
            } else if (diff < 86400) {
                const hours = Math.floor(diff / 3600);
                text = `${hours} hour${hours !== 1 ? 's' : ''} ago`;
            } else {
                const days = Math.floor(diff / 86400);
                text = `${days} day${days !== 1 ? 's' : ''} ago`;
            }

            $(this).contents().filter(function() {
                return this.nodeType === 3;
            }).first().replaceWith(text + ' ');
        });
    }

    setInterval(updateMessageTimes, 60 * 1000); // every minute
</script>
