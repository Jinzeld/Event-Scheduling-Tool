

// javascript for switching the tabs between upcoming events and past due events
function switchTab(event, tabName) {
    var i, tabContent, tabLinks;

    // Hide all tab contents
    tabContent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabContent.length; i++) {
        tabContent[i].style.display = "none";
    }

    // Remove "active" class from all tabs
    tabLinks = document.getElementsByClassName("tab-link");
    for (i = 0; i < tabLinks.length; i++) {
        tabLinks[i].classList.remove("active");
    }

    // Show the selected tab
    document.getElementById(tabName).style.display = "block";
    event.currentTarget.classList.add("active");
}

// Ensure the first tab is shown by default
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("upcoming-events").style.display = "block";
});



    // // Attach event listeners to all edit buttons
    // document.querySelectorAll(".event-actions a[title='Edit']").forEach(function (button) {
    //     button.addEventListener("click", function (event) {
    //         event.preventDefault();
    //         let eventId = this.getAttribute("data-id");
    //         let name = this.getAttribute("data-name");
    //         let description = this.getAttribute("data-description");
    //         let location = this.getAttribute("data-location");
    //         let date = this.getAttribute("data-date");
    //         let time = this.getAttribute("data-time");

    //         openEditModal(eventId, name, description, location, date, time);
    //     });
    // });

    // // Function for displaying edit modal
    // function openEditModal(eventId, name, description, location, date, time) {
    //     document.getElementById("editEventId").value = eventId;
    //     document.getElementById("editEventName").value = name;
    //     document.getElementById("editEventDescription").value = description;
    //     document.getElementById("editEventLocation").value = location;
    //     document.getElementById("editEventDate").value = date;
    //     document.getElementById("editEventTime").value = time;
        
    //     document.getElementById("editModal").style.display = "flex"; // Fix display issue
    // }


    // // Handling the form submission for editing event
    // document.getElementById("editEventForm")?.addEventListener("submit", function (event) {
    //     event.preventDefault();
    //     const eventId = document.getElementById("editEventId").value;
    //     const name = document.getElementById("editEventName").value;
    //     const description = document.getElementById("editEventDescription").value;
    //     const location = document.getElementById("editEventLocation").value;
    //     const date = document.getElementById("editEventDate").value;
    //     const time = document.getElementById("editEventTime").value;

    //     // Use AJAX to submit the form data to the server for updating the event
    //     const xhr = new XMLHttpRequest();
    //     xhr.open("POST", "updateEvent.php", true);
    //     xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    //     xhr.onload = function () {
    //         if (xhr.status === 200) {
    //             // Reload the page or update the event in the UI
    //             location.reload();
    //         } else {
    //             alert("Error updating event.");
    //         }
    //     };

    //     xhr.send(`event_id=${eventId}&name=${encodeURIComponent(name)}&description=${encodeURIComponent(description)}&location=${encodeURIComponent(location)}&date=${date}&time=${time}`);
        
    // });

    // // Function to update the event in the UI after submission (without page reload)
    // function updateEventInUI(eventId, name, description, location, date, time) {
    //     console.log("Updating UI for event ID:", eventId);

    //     // Find the event in the UI and update the information
    //     const eventElement = document.getElementById(`event-${eventId}`);
    //     if (eventElement) {
    //         eventElement.querySelector(".event-name").textContent = name;
    //         eventElement.querySelector(".event-description").textContent = description;
    //         eventElement.querySelector(".event-location").textContent = location;
    //         eventElement.querySelector(".event-date").textContent = date;
    //         eventElement.querySelector(".event-time").textContent = time;
    //     } else {
    //         console.error("Event element not found in the DOM!");
    //     }
    // }


    ///////////////////////////////
    //// DELETE MODAL SECTIION ////
    ///////////////////////////////

// Javascript for deleting events
document.addEventListener("DOMContentLoaded", function () {
    // Attach event listeners to all delete buttons
    document.querySelectorAll(".delete-btn").forEach(function (button) {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            let eventId = this.getAttribute("data-id");
            openDeleteModal(eventId);
        });
    });

    // Function for displaying delete modal
    function openDeleteModal(eventId) {
        document.getElementById("deleteEventId").value = eventId;
        document.getElementById("deleteModal").style.display = "flex"; // Fix display issue
    }

    // Handling the delete event action
    function confirmDeleteEvent() {
        const eventId = document.getElementById("deleteEventId").value;

        // Send the event_id to deleteEvent.php using fetch
        fetch("../micro-C-event-actions/delete_event.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `event_id=${eventId}`,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message); // Show success message
                location.reload(); // Reload the page after deletion
            } else {
                alert("Error: " + data.error); // Show error message
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("An error occurred. Please try again.");
        });
    }

    // Attach event listeners to the "Delete" button in delete modal
    document.getElementById("deleteModal").querySelector("button").addEventListener("click", confirmDeleteEvent);
});

// Delete modal helper for closing the pop-up
const modal = document.getElementById("deleteModal");
const closeButton = document.getElementById("closeDeleteModal");

// Function to close the modal
function closeDeleteModal() {
    modal.style.display = "none"; // Hide the modal
}

// Event listener to close the modal when clicking the close button
closeButton.addEventListener("click", function() {
    closeDeleteModal();
});

// Event listener to close the modal when clicking outside of the modal content
window.addEventListener("click", function(event) {
    if (event.target === modal) { // Check if the user clicked outside the modal-content
        closeDeleteModal();
    }
});


// Edit modal helper for closing the pop-up
const editModal = document.getElementById("editModal");
const closeEditButton = document.querySelector(".btn-cancel");  // Using class to select Cancel button

// Function to close the Edit modal
function closeEditModal() {
    editModal.style.display = "none"; // Hide the modal
}

// Event listener to close the modal when clicking the close button (Cancel)
closeEditButton.addEventListener("click", function() {
    closeEditModal();
});

// Event listener to close the modal when clicking outside of the modal content
window.addEventListener("click", function(event) {
    if (event.target === editModal) { // Check if the user clicked outside the modal-content
        closeEditModal();
    }
});

// Get elements for help modal
const helpModal = document.getElementById("helpModal");
const helpButton = document.getElementById("helpButton");

// Open the help modal
helpButton.addEventListener("click", function () {
    helpModal.style.display = "flex";
});

// Close the help modal
function closeHelpModal() {
    helpModal.style.display = "none";
}

// Close modal when clicking outside of content
window.addEventListener("click", function (event) {
    if (event.target.classList.contains("help-modal")) {
        closeHelpModal();
    }
});


// Add this to your script.js or within a <script> tag in dashboard.php
document.addEventListener('DOMContentLoaded', function () {
    const deleteIcons = document.querySelectorAll('.delete-icon');

    deleteIcons.forEach(icon => {
        icon.addEventListener('click', function (e) {
            e.preventDefault();
            const eventId = this.getAttribute('data-id');

            if (confirm('Are you sure you want to delete this image?')) {
                fetch('../micro-A-image-upload/delete_image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `event_id=${eventId}`,
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            alert('Failed to delete image: ' + data.error);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }
        });
    });
});

// Toggle user color setting dropdown
document.addEventListener("DOMContentLoaded", function () {
    const settingsButton = document.getElementById("settingsButton");
    const settingsDropdown = document.getElementById("settingsDropdown");

    // Toggle dropdown visibility
    settingsButton.addEventListener("click", function () {
        settingsDropdown.style.display =
            settingsDropdown.style.display === "block" ? "none" : "block";
    });

    // Hide dropdown when clicking outside
    document.addEventListener("click", function (event) {
        if (!settingsButton.contains(event.target) && !settingsDropdown.contains(event.target)) {
            settingsDropdown.style.display = "none";
        }
    });
});

// Toggle dark mode
const darkModeToggle = document.getElementById('darkModeToggle');
const body = document.body;

darkModeToggle.addEventListener('change', (event) => {
    if (event.target.checked) {
        body.classList.remove('light-mode');
        body.classList.add('dark-mode');
    } else {
        body.classList.remove('dark-mode');
        body.classList.add('light-mode');
    }
});


// Change gradient color in real-time
const bgColorPicker = document.getElementById('bgColorPicker');

bgColorPicker.addEventListener('input', (event) => {
    // Get the selected color
    const color = event.target.value;
    
    // Update the gradient color (the first color stop in the gradient)
    body.style.background = `linear-gradient(to bottom, #4e4e4e, #515151, #4f4f4f, ${color})`;

    // Get the user_id from the hidden input field
    const user_id = document.getElementById('userId').value;

    // Get the user_mode (dark or light)
    const user_mode = body.classList.contains('dark-mode') ? 'dark' : 'light';
});

document.getElementById('colorForm').addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent the form from submitting the traditional way

    // Log to confirm the form submission is intercepted
    console.log('Form submission intercepted');

    const formData = new FormData(this); // Get form data
    const user_id = formData.get('user_id'); // Get user_id from the form
    const user_mode = document.getElementById('darkModeToggle').checked ? 'dark' : 'light'; // Get user_mode
    const user_color = document.getElementById('bgColorPicker').value; // Get selected color directly from the color picker

    // Log the data being sent
    console.log('Sending data:', { user_id, user_mode, user_color });

    // Send data to the server using fetch
    fetch('../micro-B-visual/update_preference.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: user_id,
            user_mode: user_mode,
            user_color: user_color
        }),
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        console.log('Server response:', data); // Log the server response
        if (data.success) {
            console.log('Color updated successfully');
            // Refresh the page to apply changes
            window.location.reload();
        } else {
            console.error('Error updating color:', data.error);
        }
    })
    .catch((error) => {
        console.error('Error:', error);
    });
});

// Toggle the notification dropdown
function toggleNotifications() {
    const dropdown = document.getElementById('notification-dropdown');
    if (dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
    } else {
        dropdown.style.display = 'block';
        fetchNotifications(); // Fetch notifications when the dropdown is opened
    }
}

// Fetch notifications from the server
function fetchNotifications() {
    const user_id = document.getElementById('userId').value;

    fetch('../micro-D-event-alerts/event_notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: user_id })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.text(); // First, get the response as text
    })
    .then(text => {
        console.log("Raw response:", text); // Log the raw response
        return JSON.parse(text); // Try to parse it as JSON
    })
    .then(data => {
        console.log("Parsed data:", data); // Log the parsed data
        if (data.success) {
            updateNotificationBadge(data.notifications.length);
            renderNotifications(data.notifications);

            if (data.notifications.length > 0) {
                notifyUser(data.notifications);
            }
        } else {
            console.error('Failed to fetch notifications:', data.error);
        }
    })
    .catch(error => {
        console.error('Error fetching notifications:', error);
        alert('An error occurred while fetching notifications. Please try again later.');
    });
}

// Update the notification badge count
function updateNotificationBadge(count) {
    const badge = document.getElementById('notification-badge');
    badge.textContent = count;
    badge.style.display = count > 0 ? 'block' : 'none';
}

// Render notifications in the dropdown
function renderNotifications(notifications) {
    const notificationContent = document.getElementById('notification-content');
    notificationContent.innerHTML = '';

    if (notifications.length === 0) {
        notificationContent.innerHTML = '<div class="notification-item">No upcoming events.</div>';
        return;
    }

    notifications.forEach(notification => {
        const notificationItem = document.createElement('div');
        notificationItem.className = 'notification-item';
        notificationItem.innerHTML = `
            <strong>${notification.title}</strong><br>
            <small>Date: ${notification.event_date}, Time: ${notification.event_time}</small>
        `;
        notificationContent.appendChild(notificationItem);
    });
}

// Notify the user about upcoming events
function notifyUser(notifications) {
    // Example: Show a toast notification for each upcoming event
    notifications.forEach(notification => {
        const message = `Upcoming Event: ${notification.title} on ${notification.event_date} at ${notification.event_time}`;
        showToast(message);
    });
}

// Show a toast notification
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;

    document.body.appendChild(toast);

    // Remove the toast after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// Fetch notifications when the page loads
document.addEventListener('DOMContentLoaded', () => {
    fetchNotifications(); // Fetch notifications immediately on page load

    // Periodically check for new notifications (e.g., every 5 minutes)
    setInterval(fetchNotifications, 5 * 30 * 1000); // 5 minutes in milliseconds    
});

// Close the dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('notification-dropdown');
    const icon = document.querySelector('.notification-icon');
    if (!icon.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});

// // Handle form submission with AJAX
// document.addEventListener('DOMContentLoaded', function () {
    
//     if (!editForm) {
//         console.error('editEventForm1 not found in the DOM!');
//         return;
//     }

//     document.getElementById('editEventForm').addEventListener('submit', function (e) {
//         e.preventDefault(); // Prevent the default form submission
        
//         console.log("This event pass");

//         const formData = new FormData(this);

//         fetch('../microservice-D-event-actions/edit_event.php', {
//             method: 'POST',
//             body: formData
//         })
//         .then(response => response.json())
//         .then(data => {
//             if (data.success) {
//                 alert(data.message); // Show success message
//                 window.location.href = 'dashboard.php'; // Redirect to dashboard
//             } else {
//                 alert('Error: ' + data.error); // Show error message
//             }
//         })
//         .catch(error => {
//             console.error('Error:', error);
//             alert('An error occurred. Please try again.');
//         });
//     });
// });