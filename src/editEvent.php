<?php
    session_start();
    require_once "config.php";

    $user_id = $_SESSION["user_id"];

    // Fetch user preferences (color and mode) from the database
    $user_color = "#5a3d7a"; // Default color
    $user_mode = "Dark"; // Default mode

    $sql = "SELECT user_color, user_mode FROM users WHERE user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($db_user_color, $db_user_mode);
        $stmt->fetch();
        $stmt->close();
    
        // Override defaults if values exist in the database
        if ($db_user_color) {
            $user_color = $db_user_color;
        }
        if ($db_user_mode) {
            $user_mode = $db_user_mode;
        }
    } else {
        echo "Error: " . $conn->error;
    }

    // Determine gradient colors based on user_mode
    if ($user_mode === "dark") {
        $gradient_colors = "#4e4e4e, #515151, #4f4f4f, $user_color"; // Dark mode gradient
    } else {
        $gradient_colors = "rgb(123, 121, 121),rgb(129, 129, 129) ,rgb(106, 101, 101), $user_color"; // Light mode gradient
    }
    

?>

    <!-- edit_event.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>   
    <style>

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: linear-gradient(to bottom, <?php echo $gradient_colors; ?>);
            color: <?php echo $user_mode === "Dark" ? "#fff" : "#333"; ?>;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
            overflow: auto;
        }
        /* styles.css */
        .edit-event-container {
            max-width: 500px;
            width: 100%;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color:rgb(92, 92, 92); /* Dark background */
        }

        h2{
            color: white;
            text-align: center;
        }

        #editEventForm {
            padding: 20px;
            color: white;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color:rgb(125, 125, 125);
        }

        .form-buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn-submit,
        .btn-cancel {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }

        .btn-submit {
            background-color:rgb(64, 64, 64);
            color: white;
        }

        .btn-submit:hover {
            background-color: <?php echo $user_color; ?>;
        }

        .btn-cancel {
            background-color:rgb(65, 65, 65);
            color: white;
        }

        .btn-cancel:hover {
            background-color:<?php echo $user_color; ?>;
        }
    </style>
</head>

<body>
    <div class="edit-event-container">
        <h2>Edit Event</h2>
        <form id="editEventForm" action="updateEvent.php" method="POST">
            <input type="hidden" id="editEventId" name="event_id" value="<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>">
            <div class="form-group">
                <label for="editEventName">Name:</label>
                <input type="text" id="editEventName" name="name" value="<?php echo htmlspecialchars($_GET['name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="editEventDescription">Description:</label>
                <textarea id="editEventDescription" name="description" required><?php echo htmlspecialchars($_GET['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="editEventLocation">Location:</label>
                <input type="text" id="editEventLocation" name="location" value="<?php echo htmlspecialchars($_GET['location'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="editEventDate">Date:</label>
                <input type="date" id="editEventDate" name="date" value="<?php echo htmlspecialchars($_GET['date'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="editEventTime">Time:</label>
                <input type="time" id="editEventTime" name="time" value="<?php echo htmlspecialchars($_GET['time'] ?? ''); ?>" required>
            </div>
            <div class="form-buttons">
                <button type="submit" class="btn-submit">Update</button>
                <a href="dashboard.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        // JavaScript to handle form submission
        document.getElementById("editEventForm").addEventListener("submit", function (event) {
            event.preventDefault();

            console.log("Form submission intercepted");

            // Get form data
            const formData = new FormData(this);

            // Log form data for debugging
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }

            // Send form data using fetch
            fetch("../micro-C-event-actions/edit_event.php", {
                method: "POST",
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log("Server response:", data); // Log the server response
                if (data.success) {
                    alert(data.message); // Show success message
                    window.location.href = "dashboard.php"; // Redirect to dashboard
                } else {
                    alert('Error: ' + data.error); // Show error message
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    </script>
</body>
</html>