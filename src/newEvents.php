<?php

session_start();

require_once "config.php"; 

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: signIn.php");
    exit;
}

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

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION["user_id"];
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $location = trim($_POST["location"]);
    $event_date = $_POST["event_date"];
    $event_time = $_POST["event_time"];

    if (empty($title) || empty($location) || empty($event_date) || empty($event_time)) {
        $error = "Please fill in all required fields.";
    } else {
        $sql = "INSERT INTO events (user_id, title, description, location, event_date, event_time) VALUES (?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isssss", $user_id, $title, $description, $location, $event_date, $event_time);
            if ($stmt->execute()) {
                $success = "Event created successfully!";
                header("Location: dashboard.php");
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - EventSync</title>
    <link rel="stylesheet" href="../style/event.css">
    <style>
        body {
        background: linear-gradient(to bottom, #4e4e4e, #515151, #4f4f4f, <?php echo $user_color; ?>);
        color: #333;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        flex-direction: column;
        overflow: auto;
    }

    /* Create Events Button */
    .nav-dashboard {
        background: linear-gradient(to right, #7a3d9d, <?php echo $user_color; ?>);
        color: white;
    }

    .nav-dashboard:hover {
        background: linear-gradient(to right, #9b59b6, <?php echo $user_color; ?>);
        transform: scale(1.05);
    }

    /* Logout Button */
    .nav-link {
        background: linear-gradient(to right, #7a3d9d, <?php echo $user_color; ?>);
        color: white;
    }

    .nav-link:hover {
        background: linear-gradient(to right, #9b59b6, <?php echo $user_color; ?>);
        transform: scale(1.05);
    }

    .submit-btn:hover {
        background-color: <?php echo $user_color; ?>;
    }

    </style>
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">EventSync</a>
        <a href="dashboard.php" class="nav-dashboard">Dashboard</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </nav>

    <div class="container">
        <h2>Create a New Event</h2>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="newEvents.php" method="POST">
            <label for="title">Event Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" rows="4"></textarea>

            <label for="location">Location:</label>
            <input type="text" id="location" name="location" required>

            <label for="event_date">Date:</label>
            <input type="date" id="event_date" name="event_date" required>

            <label for="event_time">Time:</label>
            <input type="time" id="event_time" name="event_time" required>

            <button type="submit" class="submit-btn">Create Event</button>
        </form>
    </div>

</body>
</html>
