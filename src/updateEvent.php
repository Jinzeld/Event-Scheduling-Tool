<?php
session_start();
require_once "config.php"; // Include your database configuration

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if the user is logged in
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        echo "You must be logged in to update events.";
        exit;
    }

    // Collect POST data
    $event_id = $_POST['event_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $location = $_POST['location'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Prepare the SQL update query
    $sql = "UPDATE events SET title = ?, description = ?, location = ?, event_date = ?, event_time = ? WHERE event_id = ? AND user_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters: "ssssssi" means 6 strings and 1 integer
        $stmt->bind_param("ssssssi", $name, $description, $location, $date, $time, $event_id, $_SESSION['user_id']);
        
        // Execute and check if the event was updated successfully
        if ($stmt->execute()) {
            echo "Event updated successfully!";
        } else {
            echo "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
    $conn->close();
}
?>
