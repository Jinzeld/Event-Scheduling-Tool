

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


// Javascript for updating evets and deleting events
document.addEventListener("DOMContentLoaded", function () {

    // Attach event listeners to all edit buttons
    document.querySelectorAll(".event-actions a[title='Edit']").forEach(function (button) {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            let eventId = this.getAttribute("data-id");
            let name = this.getAttribute("data-name");
            let description = this.getAttribute("data-description");
            let location = this.getAttribute("data-location");
            let date = this.getAttribute("data-date");
            let time = this.getAttribute("data-time");

            openEditModal(eventId, name, description, location, date, time);
        });
    });

    // Attach event listeners to all delete buttons
    document.querySelectorAll(".delete-btn").forEach(function (button) {
        button.addEventListener("click", function (event) {
            event.preventDefault();
            let eventId = this.getAttribute("data-id");
            openDeleteModal(eventId);
        });
    });

    // Function for displaying edit modal
    function openEditModal(eventId, name, description, location, date, time) {
        document.getElementById("editEventId").value = eventId;
        document.getElementById("editEventName").value = name;
        document.getElementById("editEventDescription").value = description;
        document.getElementById("editEventLocation").value = location;
        document.getElementById("editEventDate").value = date;
        document.getElementById("editEventTime").value = time;
        
        document.getElementById("editModal").style.display = "flex"; // Fix display issue
    }

    // Function for displaying delete modal
    function openDeleteModal(eventId) {
        document.getElementById("deleteEventId").value = eventId;
        document.getElementById("deleteModal").style.display = "flex"; // Fix display issue
    }

    // Handling the form submission for editing event
    document.getElementById("editEventForm")?.addEventListener("submit", function (event) {
        event.preventDefault();

        const eventId = document.getElementById("editEventId").value;
        const name = document.getElementById("editEventName").value;
        const description = document.getElementById("editEventDescription").value;
        const location = document.getElementById("editEventLocation").value;
        const date = document.getElementById("editEventDate").value;
        const time = document.getElementById("editEventTime").value;

        // Use AJAX to submit the form data to the server for updating the event
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "updateEvent.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
                // Close the modal after the event is updated
                closeEditModal();  // Ensure this function is available to close the modal

                // Optionally, you can update the UI with the new event data dynamically without a full reload
                updateEventInUI(eventId, name, description, location, date, time);
                
                location.reload(); 
                // Alert the user about the successful update
                alert("Event updated successfully!");
            } else {
                alert("Error updating event.");
            }
        };

        xhr.send(`event_id=${eventId}&name=${encodeURIComponent(name)}&description=${encodeURIComponent(description)}&location=${encodeURIComponent(location)}&date=${date}&time=${time}`);
    });

    // Function to update the event in the UI after submission (without page reload)
    function updateEventInUI(eventId, name, description, location, date, time) {
        // Find the event in the UI and update the information (this depends on your specific HTML structure)
        const eventElement = document.getElementById(`event-${eventId}`);  // Assuming each event has a unique ID in the DOM
        if (eventElement) {
            eventElement.querySelector(".event-name").textContent = name;
            eventElement.querySelector(".event-description").textContent = description;
            eventElement.querySelector(".event-location").textContent = location;
            eventElement.querySelector(".event-date").textContent = date;
            eventElement.querySelector(".event-time").textContent = time;
        }
    }

    // Handling the delete event action
    function confirmDeleteEvent() {
        const eventId = document.getElementById("deleteEventId").value;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "deleteEvent.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
                location.reload(); // Reload the page after deletion
            } else {
                alert("Error deleting event.");
            }
        };

        xhr.send(`event_id=${eventId}`);
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
