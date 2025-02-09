<?php
    session_start();
    require_once "config.php"; // Include your database configuration

    // Check if the user is logged in
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("Location: signIn.php");
        exit;
    }

    // Fetch user information
    $username = $_SESSION["login"]; // Assuming "login" stores the username

    // Fetch events for the logged-in user
    $user_id = $_SESSION["user_id"];
    $events = [];

    $sql = "SELECT event_name, event_date, event_description FROM events WHERE user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($event_name, $event_date, $event_description);
        
        while ($stmt->fetch()) {
            $events[] = [
                'name' => $event_name,
                'date' => $event_date,
                'description' => $event_description
            ];
        }
        $stmt->close();
    }
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Your Events</title>
    <link rel="stylesheet" href="../style/dashboard.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">Eventsync</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </nav>

    <!-- Dashboard Container -->
    <div class="container">
        <h2 class="welcome-message">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

        <h3 class="title">Your Events</h3>

        <div class="events-container">
            <?php if (count($events) > 0): ?>
                <?php foreach ($events as $event): ?>
                    <div class="event-box">
                        <h3 class="event-name"><?php echo htmlspecialchars($event['name']); ?></h3>
                        <p class="event-date"><?php echo date("F j, Y", strtotime($event['date'])); ?></p>
                        <p class="event-description"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No events found. Create one to get started!</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
