// Listen for notification events
if (
    typeof window.Echo !== "undefined" &&
    typeof window.userId !== "undefined"
) {
    window.Echo.private(`App.Models.User.${window.userId}`).listen(
        "NotificationEvent",
        (e) => {
            // Create notification element
            const notificationElement = document.createElement("div");
            notificationElement.className =
                "alert alert-info alert-dismissible fade show";
            notificationElement.role = "alert";
            notificationElement.innerHTML = `
                <strong>New Notification:</strong> ${e.notification.data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

            // Add to notifications container
            const container = document.getElementById(
                "notifications-container"
            );
            if (container) {
                container.prepend(notificationElement);
            }

            // Update notification count
            const countElement = document.getElementById("notification-count");
            if (countElement) {
                const currentCount = parseInt(countElement.textContent) || 0;
                countElement.textContent = currentCount + 1;
                countElement.style.display = "inline";
            }

            // Play notification sound
            if (typeof window.notificationSound !== "undefined") {
                window.notificationSound
                    .play()
                    .catch((e) => console.log("Sound play prevented:", e));
            }
        }
    );
}
