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

    $sql = "SELECT event_id, title, description, location, event_date, event_time, image_path FROM events WHERE user_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();  
        
        // Bind result variables
        $stmt->bind_result($id, $title, $description, $location, $event_date, $event_time, $image_path);
        
        while ($stmt->fetch()) {
            $events[] = [
                'id' => $id,
                'name' => $title,
                'description' => $description,
                'location' => $location,
                'date' => $event_date,
                'time' => $event_time,
                'image_path' => $image_path
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../style/Dashboard1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>

        .add-image-icon:hover {
            color:rgb(187, 147, 255);
            transform: scale(1.1);
        }

        .event-image-container {
            position: relative;
            width: 100%;
            max-width: 300px;
            margin: 20px auto;
            margin-right: 150px;
            text-align: center;
        }

        .event-image {
            position: relative;
            width: 100%;
            height: 200px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
        }

        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }

        .delete-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color:rgb(187, 147, 255);
            color: white;
            padding: 5px;
            border-radius: 10%;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .event-image:hover .delete-icon {
            opacity: 1;
        }

        #settingsDropdown {
            position: absolute;
            right: 0;
            margin-top: 0.5rem; /* mt-2 */
            height: 12rem;
            width: 12rem; 
            background-color:rgb(143, 139, 139); /* Custom background color */
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* shadow-lg */
            display: none; /* hidden by default */
        }

        #colorForm {
            padding: 15px;
        }

        #settingsButton{
            margin-left: 700px;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 34px;
            height: 20px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            border-radius: 20px;
            transition: 0.3s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 14px;
            width: 14px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            border-radius: 50%;
            transition: 0.3s;
        }

        input:checked + .slider {
            background-color:rgb(108, 58, 195);  
        }

        input:checked + .slider:before {
            transform: translateX(14px);
        }

    </style>
</head>
<body>

    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">EventSync</a>
        <a href="newEvents.php" class="nav-events">Create Events +</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </nav>
    
    <div class="event-container">

        <div class="relative">
            <button id="settingsButton" class=" rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600">
                ⚙️
            </button>
            <!-- Dropdown Menu -->
            <div id="settingsDropdown">
                <form id="colorForm" method="POST" action="../micro-B-visual/update_preference.php">
                    <input type="hidden" id="userId" name="user_id" value="<?php echo $user_id; ?>">
                    <div class="p-2">
                        <!-- Dark Mode Toggle -->
                        <div class="flex items-center justify-between">
                            <span class="text-gray-800">Dark Mode</span>
                            <label class="switch">
                                <input type="checkbox" id="darkModeToggle">
                                <span class="slider"></span>
                            </label>
                        </div>

                        <!-- Background Color Picker -->
                        <div class="mt-2">
                            <span class="text-gray-800">Background Color</span>
                            <input type="color" id="bgColorPicker" class="w-full h-8 mt-1">
                        </div>
                    </div>
                </form>
            </div>
        </div>
            
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
                                <!-- event info -->
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

                         
                            <!-- Inside the event loop in dashboard.php -->
                            <div class="event-image-container">
                                <?php if (!empty($event['image_path'])): ?>
                                    <div class="event-image">
                                        <img src="../micro-A-image-upload/uploads/<?= htmlspecialchars($event['image_path']) ?>" alt="Event Image" />

                                        <!-- delete image icon -->
                                        <div class="delete-icon" data-id="<?= $event['id'] ?>">
                                            <i class="fa fa-trash"></i>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Image Upload Form -->
                            <form action="../micro-A-image-upload/upload_image.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                
                                <!-- File input (visible after clicking the icon) -->
                                <input 
                                    type="file" 
                                    name="image" 
                                    id="imageUpload-<?php echo $event['id']; ?>" 
                                    class="image-input" 
                                    accept="image/*" 
                                    style="display:none" 
                                    onchange="this.form.submit();" 
                                />
                            <!-- Event action icons -->
                            <div class="event-actions"> 
                                <a href="#">
                                    <i 
                                        class="fa fa-image add-image-icon" 
                                        onclick="document.getElementById('imageUpload-<?php echo $event['id']; ?>').click()" 
                                    ></i>
                                </a>
                                <a href="#" title="Edit" 
                                    data-id="<?php echo $event['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($event['name']); ?>"
                                    data-description="<?php echo htmlspecialchars($event['description']); ?>"
                                    data-location="<?php echo htmlspecialchars($event['location']); ?>"
                                    data-date="<?php echo $event['date']; ?>"
                                    data-time="<?php echo $event['time']; ?>">
                                    <i class="fa fa-edit" title="Edit"></i>
                                </a>
                                <a href="#" title="Delete" class="delete-btn" 
                                    data-id="<?php echo $event['id']; ?>">
                                    <i class="fa fa-trash" title="Delete"></i>
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
                                <a href="#" title="Edit" 
                                    data-id="<?php echo $event['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($event['name']); ?>"
                                    data-description="<?php echo htmlspecialchars($event['description']); ?>"
                                    data-location="<?php echo htmlspecialchars($event['location']); ?>"
                                    data-date="<?php echo $event['date']; ?>"
                                    data-time="<?php echo $event['time']; ?>">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a href="#" title="Delete" class="delete-btn" 
                                    data-id="<?php echo $event['id']; ?>">
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

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2>Edit Event</h2>
            <form id="editEventForm">
                <input type="hidden" id="editEventId">
                <div class="form-group">
                    <label for="editEventName">Name:</label>
                    <input type="text" id="editEventName" required>
                </div>
                <div class="form-group">
                    <label for="editEventDescription">Description:</label>
                    <textarea id="editEventDescription" required></textarea>
                </div>
                <div class="form-group">
                    <label for="editEventLocation">Location:</label>
                    <input type="text" id="editEventLocation" required>
                </div>
                <div class="form-group">
                    <label for="editEventDate">Date:</label>
                    <input type="date" id="editEventDate" required>
                </div>
                <div class="form-group">
                    <label for="editEventTime">Time:</label>
                    <input type="time" id="editEventTime" required>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="btn-submit">Update</button>
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2>Are you sure you want to delete this event?</h2>
            <input type="hidden" id="deleteEventId">
            <div class="form-buttons">
                <button onclick="confirmDeleteEvent()" class="btn-delete">Delete</button>
                <button id="closeDeleteModal" class="btn-cancel">Cancel</button> <!-- Added closeDeleteModal button -->
            </div>
        </div>
    </div>

    <!-- Help Button -->
    <button id="helpButton" class="help-btn">?</button>

    <!-- Help Modal -->
    <div id="helpModal" class="help-modal">
        <div class="help-modal-content">
            <span class="help-close-btn" onclick="closeHelpModal()">&times;</span>
            <h2>Help & Information</h2>
            <h3>Welcome to your dashboard! Here are some key features:</h3>
            <br>
            <ul>
                <li><strong>Create Event:</strong> To create an event click on the create event button on the middle of the nav-bar section.</li>
                <br>
                <li><strong>Add Events:</strong> When on the add event page you can fill out the required input boxes for Title of event, Description, Location, Date, and time to complete the event form then your can click submit and your done.</li>
                <br>
                <li><strong>Back to dashboard:</strong> To head back to the dashboard just click on the logo on that nav-bar to go back to dashboard.</li>
            </ul>
            <br><br>
            <h3>Click anywhere outside this box or press the close button to exit.</h3>
        </div>
    </div>

    <script src="script.js"></script> 
</body>
</html>
