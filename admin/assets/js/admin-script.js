document.addEventListener('DOMContentLoaded', function() {
    // Handle Mark as Read button click
    document.querySelectorAll('.mark-read-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent dropdown from closing
            const notificationItem = this.closest('[data-notification-id]');
            const notificationId = notificationItem.dataset.notificationId;

            fetch('api/notification_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_read&id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    notificationItem.remove(); // Remove notification from dropdown
                    updateNotificationCount();
                } else {
                    console.error('Failed to mark notification as read:', data.message);
                }
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        });
    });

    // Handle Delete button click
    document.querySelectorAll('.delete-notification-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.stopPropagation(); // Prevent dropdown from closing
            const notificationItem = this.closest('[data-notification-id]');
            const notificationId = notificationItem.dataset.notificationId;

            if (confirm('Are you sure you want to delete this notification?')) {
                fetch('api/notification_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=delete&id=' + notificationId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        notificationItem.remove(); // Remove notification from dropdown
                        updateNotificationCount();
                    } else {
                        console.error('Failed to delete notification:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error deleting notification:', error);
                });
            }
        });
    });

    function updateNotificationCount() {
        const notificationBadge = document.querySelector('#navbarDropdownNotifications .badge');
        if (notificationBadge) {
            let currentCount = parseInt(notificationBadge.textContent);
            currentCount = Math.max(0, currentCount - 1); // Ensure count doesn't go below 0
            notificationBadge.textContent = currentCount;
            if (currentCount === 0) {
                // If no notifications, remove the badge or hide it
                notificationBadge.style.display = 'none';
                // Optionally, add a "No new notifications" message
                const dropdownMenu = document.querySelector('#navbarDropdownNotifications + .dropdown-menu');
                if (dropdownMenu) {
                    dropdownMenu.innerHTML = '<a class="dropdown-item" href="#">No new notifications</a>';
                }
            }
        }
    }

    // Select All Checkbox for Files
    const selectAllFilesCheckbox = document.getElementById('select-all-files');
    if (selectAllFilesCheckbox) {
        selectAllFilesCheckbox.addEventListener('change', function() {
            const fileCheckboxes = document.querySelectorAll('.file-checkbox');
            fileCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Select All Checkbox for Articles
    const selectAllArticlesCheckbox = document.getElementById('select-all-articles');
    if (selectAllArticlesCheckbox) {
        selectAllArticlesCheckbox.addEventListener('change', function() {
            const articleCheckboxes = document.querySelectorAll('.article-checkbox');
            articleCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Select All Checkbox for Pages
    const selectAllPagesCheckbox = document.getElementById('select-all-pages');
    if (selectAllPagesCheckbox) {
        selectAllPagesCheckbox.addEventListener('change', function() {
            const pageCheckboxes = document.querySelectorAll('.page-checkbox');
            pageCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }
});