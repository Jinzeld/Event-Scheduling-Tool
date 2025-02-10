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
    <link rel="stylesheet" href="../style/Dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">Eventsync</a>
        <a href="newEvents.php" class="nav-events">Create Events +</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </nav>

    <!-- Dashboard Container -->
    <div class="container">
        <h2 class="welcome-message">Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

        <h3 class="title">Your Events</h3>

        <!-- Tab Navigation -->
        <div class="tabs">
            <button class="tab-link active" onclick="switchTab(event, 'upcoming-events')">Upcoming Events</button>
            <button class="tab-link" onclick="switchTab(event, 'past-events')">Past Events</button>
        </div>

        <div class="events-container">
            <!-- Upcoming Events Section -->
            <div id="upcoming-events" class="tab-content active">
                <?php
                $hasUpcoming = false;
                foreach ($events as $event):
                    if (strtotime($event['date']) >= strtotime(date("Y-m-d"))):
                        $hasUpcoming = true; ?>
                        <div class="event-box upcoming-event">

                            <!-- Edit & Delete Buttons -->
                            <div class="event-actions">
                                <a href="editEvent.php?id=<?php echo $event['id']; ?>" title="Edit"><i class="fa fa-edit"></i></a>
                                <a href="deleteEvent.php?id=<?php echo $event['id']; ?>" title="Delete" class="delete-btn" onclick="return confirm('Are you sure you want to delete this event?');"><i class="fa fa-trash"></i></a>
                            </div>

                            <!-- main event display -->
                            <h3 class="event-name"><?php echo htmlspecialchars($event['name']); ?></h3>
                            <p class="event-date"><strong>Date:</strong> <?php echo date("F j, Y", strtotime($event['date'])); ?></p>
                            <p class="event-time"><strong>Time:</strong> <?php echo date("g:i A", strtotime($event['time'])); ?></p>
                            <p class="event-location"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                            <p class="event-description"><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        </div>
                    <?php endif;
                endforeach;
                if (!$hasUpcoming) echo "<p>No upcoming events found.</p>"; ?>
            </div>

            <!-- Past Events Section -->
            <div id="past-events" class="tab-content">
                <?php
                $hasPast = false;
                foreach ($events as $event):
                    if (strtotime($event['date']) < strtotime(date("Y-m-d"))):
                        $hasPast = true; ?>
                        <div class="event-box past-event">

                            <!-- Edit & Delete Buttons -->
                            <div class="event-actions">
                                <a href="editEvent.php?id=<?php echo $event['id']; ?>" title="Edit"><i class="fa fa-edit"></i></a>
                                <a href="deleteEvent.php?id=<?php echo $event['id']; ?>" title="Delete" class="delete-btn" onclick="return confirm('Are you sure you want to delete this event?');"><i class="fa fa-trash"></i></a>
                            </div>

                            <!-- main event display -->
                            <h3 class="event-name"><?php echo htmlspecialchars($event['name']); ?></h3>
                            <p class="event-date"><strong>Date:</strong> <?php echo date("F j, Y", strtotime($event['date'])); ?></p>
                            <p class="event-time"><strong>Time:</strong> <?php echo date("g:i A", strtotime($event['time'])); ?></p>
                            <p class="event-location"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                            <p class="event-description"><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        </div>
                    <?php endif;
                endforeach;
                if (!$hasPast) echo "<p>No past events found.</p>"; ?>
            </div>
        </div>
    </div>

    <script>
        function switchTab(evt, tabName) {
            var i, tabcontent, tablinks;

            // Hide all tab content
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }

            // Remove "active" class from all tab links
            tablinks = document.getElementsByClassName("tab-link");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].classList.remove("active");
            }

            // Show the selected tab and mark button as active
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.classList.add("active");
        }

        // Default to displaying the Upcoming Events tab on page load
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("upcoming-events").style.display = "block";
        });
    </script>


</body>
</html>
