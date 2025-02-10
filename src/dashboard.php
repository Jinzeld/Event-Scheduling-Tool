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

    $sql = "SELECT event_id, title, description, location, event_date, event_time FROM events WHERE user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();
        
        // Bind result variables
        $stmt->bind_result($id, $title, $description, $location, $event_date, $event_time);
        
        while ($stmt->fetch()) {
            $events[] = [
                'id' => $id,
                'name' => $title,
                'description' => $description,
                'location' => $location,
                'date' => $event_date,
                'time' => $event_time
            ];
        }
        $stmt->close();
    } else {
        echo "Error: " . $conn->error;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">Eventsync</a>
        <a href="newEvents.php" class="nav-events">Create Events +</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </nav>
    
    <div class="container">
        <h2 class="welcome-message">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

        <h3 class="title">Your Events</h3>

        <!-- Tab Navigation -->
        <div class="tabs">
            <button class="tab-link active" onclick="switchTab(event, 'upcoming-events')">Upcoming Events</button>
            <button class="tab-link" onclick="switchTab(event, 'past-events')">Past Events</button>
        </div>
        <!-- Upcoming Events -->
        <div id="upcoming-events" class="tab-content active">
            <ul class="event-list">
                <?php
                $hasUpcomingEvents = false;
                foreach ($events as $event):
                    if (strtotime($event['date']) >= strtotime(date("Y-m-d"))):
                        $hasUpcomingEvents = true; ?>
                        <li class="event-item upcoming-event">
                            <div class="event-info">
                                <h3 class="event-name"><?php echo htmlspecialchars($event['name']); ?></h3>
                                <p class="event-date">
                                    <i class="fa fa-calendar"></i> <?php echo date("F j, Y", strtotime($event['date'])); ?>
                                    <span class="event-time">
                                        <i class="fa fa-clock"></i> <?php echo date("g:i A", strtotime($event['time'])); ?>
                                    </span>
                                </p>
                                <p class="event-location">
                                    <i class="fa fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                                </p>
                                <p class="event-description"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                            </div>
                            <div class="event-actions">
                                <a href="editEvent.php?event_id=<?php echo $event['event_id']; ?>" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a href="deleteEvent.php?event_id=<?php echo $event['event_id']; ?>" title="Delete" class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this event?');">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </li>
                    <?php endif;
                endforeach;

                if (!$hasUpcomingEvents): ?>
                    <p class="no-events">No upcoming events found.</p>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Past Events -->
        <div id="past-events" class="tab-content">
            <ul class="event-list">
                <?php
                $hasPastEvents = false;
                foreach ($events as $event):
                    if (strtotime($event['date']) < strtotime(date("Y-m-d"))):
                        $hasPastEvents = true; ?>
                        <li class="event-item past-event">
                            <div class="event-info">
                                <h3 class="event-name"><?php echo htmlspecialchars($event['name']); ?></h3>
                                <p class="event-date">
                                    <i class="fa fa-calendar"></i> <?php echo date("F j, Y", strtotime($event['date'])); ?>
                                    <span class="event-time">
                                        <i class="fa fa-clock"></i> <?php echo date("g:i A", strtotime($event['time'])); ?>
                                    </span>
                                </p>
                                <p class="event-location">
                                    <i class="fa fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                                </p>
                                <p class="event-description"><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                            </div>
                            <div class="event-actions">
                                <a href="editEvent.php?event_id=<?php echo $event['event_id']; ?>" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a href="deleteEvent.php?event_id=<?php echo $event['event_id']; ?>" title="Delete" class="delete-btn"
                                onclick="return confirm('Are you sure you want to delete this event?');">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </li>
                    <?php endif;
                endforeach;

                if (!$hasPastEvents): ?>
                    <p class="no-events">No past events found.</p>
                <?php endif; ?>
            </ul>
        </div>
    </div>



    <script>
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
    </script>




</body>
</html>
