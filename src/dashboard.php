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
    <link rel="stylesheet" href="../style/Dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
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

        /* Create Events Button */
        .nav-events {
            background: linear-gradient(to right, #7a3d9d, <?php echo $user_color; ?>);
            color: white;
        }

        .nav-events:hover {
            background: linear-gradient(to right, #9b59b6,<?php echo $user_color; ?>);
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

        .tab-link.active {
            background-color: <?php echo $user_color; ?>;
            color: white;
        }

        .add-image-icon:hover {
            color: <?php echo $user_color; ?>;
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
            box-shadow: <?php echo $user_color; ?>;
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
            background-color: <?php echo $user_color; ?>;
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

        .apply-button {
            margin-top: 0.5rem; /* mt-2 */
            padding: 0.5rem; /* p-2 */
            background-color: #4e4e4e;
            color: white; /* text-white */
            border-radius: 0.25rem; /* rounded */
            width: 100%; /* w-full */
            border: none;
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        .apply-button:hover {
            background-color: <?php echo $user_color; ?>;
        }

        .event-actions a:hover {

            color: <?php echo $user_color; ?>;
        }

        #settingsDropdown {
            position: absolute;
            right: 0;
            margin-right: 650px;
            margin-top: 0.5rem; /* mt-2 */
            height: 12rem;
            width: 12rem; 
            background-color:rgb(143, 139, 139); /* Custom background color */
            border-radius: 0.5rem; /* rounded-lg */
            box-shadow: <?php echo $user_color; ?>; /* shadow-lg */
            display: none; /* hidden by default */
        }

        #colorForm {
            padding: 15px;
        }

        #settingsButton{
            margin-left: 20px;
            Background-color: <?php echo $user_color; ?>;
            height: 50px;
            width: 50px;
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
            background-color: <?php echo $user_color; ?>;  
        }

        input:checked + .slider:before {
            transform: translateX(14px);
        }

        .modal-content button {
            background-color: #4e4e4e;
            color: #fff;
            padding: 10px 20px;
            margin: 10px 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-submit {
            background-color: #4e4e4e;
            color: white;
        }

        .btn-submit:hover {
            background-color: <?php echo $user_color; ?>;
        }

        .btn-cancel {
            background-color: <?php echo $user_color; ?>;
            color: white;
        }

        .btn-cancel:hover {
            background-color: <?php echo $user_color; ?>;
        }

        .btn-delete {
            background-color:rgb(69, 69, 69);
            color: white;
        }

        .btn-delete:hover {
            background-color: <?php echo $user_color; ?>;
        }

        /* Unique Help Button Styling */
        .help-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color:<?php echo $user_color; ?>;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
        }
        /* Notification Container */
        .notification-container {
            position: relative;
            display: inline-block;
            margin-left: 800px;
        }

        /* Bell Icon */
        .notification-icon {
            font-size: 24px;
            cursor: pointer;
            color: #333;
            position: relative;
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 12px;
        }

        /* Notification Dropdown */
        .notification-dropdown {
            display: none;
            position: absolute;
            right: 25;
            color: black;
            background-color: <?php echo $user_color; ?>;
            border: 1px solid #ccc;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px;
            max-height: 400px;
            overflow-y: auto;
            z-index: 1000;
        }

        /* Notification Item */
        .notification-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-item:hover {
            background: linear-gradient(to right, #7a3d9d, <?php echo $user_color; ?>);
        }

        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: fadeIn 0.5s, fadeOut 0.5s 4.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
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

        <!-- Bell Icon and Notification Dropdown -->
        <div class="notification-container">
            <input type="hidden" id="userId" name="user_id" value="<?php echo $user_id; ?>">
            <div class="notification-icon" onclick="toggleNotifications()">
                <i class="fas fa-bell"></i>
                <span id="notification-badge" class="notification-badge">0</span>
            </div>
            <div id="notification-dropdown" class="notification-dropdown">
                <div id="notification-content">
                    <!-- Notifications will be dynamically inserted here -->
                </div>
            </div>
        </div>

        <!-- user settings and preference menu -->
        <div class="relative">
            <button id="settingsButton">
            <i class="fa fa-bars" aria-hidden="true"></i>
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
                                <input type="checkbox" id="darkModeToggle" <?php echo $user_mode === 'dark' ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div> 

                        <!-- Background Color Picker -->
                        <div class="mt-2">
                            <span class="text-gray-800">Background Color</span>
                            <input type="color" id="bgColorPicker" class="w-full h-8 mt-1" value="<?php echo $user_color; ?>">
                        </div>
                        <!-- Apply Changes Button -->
                        <button type="submit" class="apply-button">Apply Changes</button>
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
                                <a href="editEvent.php?id=<?php echo $event['id']; ?>&name=<?php echo urlencode($event['name']); ?>&description=<?php echo urlencode($event['description']); ?>&location=<?php echo urlencode($event['location']); ?>&date=<?php echo $event['date']; ?>&time=<?php echo $event['time']; ?>" title="Edit">
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
                            <a href="edit_event.php?id=<?php echo $event['id']; ?>&name=<?php echo urlencode($event['name']); ?>&description=<?php echo urlencode($event['description']); ?>&location=<?php echo urlencode($event['location']); ?>&date=<?php echo $event['date']; ?>&time=<?php echo $event['time']; ?>" title="Edit">
                                <i class="fa fa-edit" title="Edit"></i>
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
    <!-- <div id="editModal" class="modal">
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
     </div> -->


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

    <script src="script1.js"></script> 
</body>
</html>
