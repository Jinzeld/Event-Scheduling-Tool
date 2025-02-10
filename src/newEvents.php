<?php

session_start();

require_once "config.php"; 

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: signIn.php");
    exit;
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
    <link rel="stylesheet" href="../style/Event.css">
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">EventSync</a>
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
