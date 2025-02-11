<?php
// deleteEvent.php
require_once "config.php"; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = $_POST['event_id'];

    $sql = "DELETE FROM events WHERE event_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        echo "Event deleted successfully";
    } else {
        echo "Error: " . $conn->error;
    }

    $conn->close();
}
?>
