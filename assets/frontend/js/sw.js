
// Service Worker Installation - Cache static assets if needed
self.addEventListener('install', function(event) {
    console.log('Service Worker installing.');
    // Skip waiting forces the waiting service worker to become the active service worker
    self.skipWaiting();
});

// Service Worker Activation
self.addEventListener('activate', function(event) {
    console.log('Service Worker activated.');
    // Claim control immediately, rather than waiting for reload
    event.waitUntil(clients.claim());
});

// Push Event Handler
self.addEventListener("push", function(event) {
    console.log('Push notification received', event);

    // Default notification data
    let title = "New Message!";
    let options = {
        body: "You have a new notification.",
        icon: "https://deexial.com/assets/uploads/media-uploader/Deexial.com%20favicon%20logo1704728989.png",
        vibrate: [200, 100, 200],
        badge: "https://deexial.com/assets/uploads/media-uploader/Deexial.com%20favicon%20logo1704728989.png",
        tag: 'deexial-notification', // Tag for grouping similar notifications
        renotify: true, // Notify even if there's an existing notification with same tag
        data: {
            url: self.location.origin // URL to open when notification is clicked
        }
    };

    // Try to extract data from the push event
    try {
        if (event.data) {
            const data = event.data.json();
            title = data.title || title;

            // Merge data options with default options
            if (data.options) {
                options = Object.assign(options, data.options);
            } else if (data.body) {
                options.body = data.body;
            }
        }
    } catch (error) {
        console.error('Error parsing push data:', error);
    }

    // Display the notification
    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification Click Handler
self.addEventListener('notificationclick', function(event) {
    console.log('Notification clicked', event);

    // Close the notification
    event.notification.close();

    // Get URL to open (default to origin if not specified)
    const urlToOpen = event.notification.data && event.notification.data.url
        ? event.notification.data.url
        : self.location.origin;

    // Open the URL in an existing window if possible, or a new one
    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then(function(clientList) {
            // Try to find an existing window to focus
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }

            // If no existing window, open a new one
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

// Error handling for failed notifications
self.addEventListener('notificationerror', function(event) {
    console.error('Notification error:', event.error);
});