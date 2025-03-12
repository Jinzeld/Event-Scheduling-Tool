<?php
    session_start();
    require_once "../src/config.php"; // Include your database configuration

    // Enable error reporting for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Function to send a POST request to the Flask microservice
    function sendSaveRequest($data) {
        $url = "https://micro-c-event-action.vercel.app/save_event"; // Updated endpoint URL

        // Define separate log files for requests and responses
        $request_log_file = 'vercel_request.log';
        $response_log_file = 'vercel_response.log';

        // Log the request being sent to Vercel
        $log_request = "Sending to Vercel: " . json_encode($data);
        file_put_contents($request_log_file, $log_request . "\n", FILE_APPEND);

        // Initialize cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // Updated to POST
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Execute the request
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Log the response from Vercel
        $log_response = "Response from Vercel (HTTP $http_code): " . $response;
        file_put_contents($response_log_file, $log_response . "\n", FILE_APPEND);

        return [$http_code, $response];
    }

    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if the user is logged in
        if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
            echo json_encode(["success" => false, "error" => "You must be logged in to update events."]);
            exit;
        }

        // Get form data
        $event_id = $_POST['event_id'] ?? null; // Still needed for local database update
        $name = $_POST['name'] ?? null;
        $description = $_POST['description'] ?? null;
        $location = $_POST['location'] ?? null;
        $date = $_POST['date'] ?? null;
        $time = $_POST['time'] ?? null;

        // Validate required fields
        if (!$name || !$description || !$location || !$date || !$time) {
            echo json_encode(["success" => false, "error" => "All fields are required"]);
            exit;
        }

        // Prepare the data to send to the Flask microservice
        $event_data = [
            'name' => $name,
            'description' => $description,
            'location' => $location,
            'date' => $date,
            'time' => $time
        ];

        // Send the save request to the Flask microservice
        list($http_code, $response) = sendSaveRequest($event_data);

        // Handle the response
        if ($http_code === 200) {
            $response_data = json_decode($response, true);
            if ($response_data['success']) {
                // Event saved successfully in the microservice
                // Now update the event in the local database

                // Prepare the SQL update query
                $sql = "UPDATE events SET title = ?, description = ?, location = ?, event_date = ?, event_time = ? WHERE event_id = ? AND user_id = ?";

                if ($stmt = $conn->prepare($sql)) {
                    // Bind parameters: "ssssssi" means 6 strings and 1 integer
                    $stmt->bind_param("ssssssi", $name, $description, $location, $date, $time, $event_id, $_SESSION['user_id']);
                    
                    // Execute and check if the event was updated successfully
                    if ($stmt->execute()) {
                        echo json_encode([
                            "success" => true,
                            "message" => "Event updated successfully in both microservice and local database."
                        ]);
                    } else {
                        echo json_encode([
                            "success" => false,
                            "error" => "Failed to update event in local database: " . $conn->error
                        ]);
                    }
                    $stmt->close();
                } else {
                    echo json_encode([
                        "success" => false,
                        "error" => "Error preparing statement for local database update: " . $conn->error
                    ]);
                }
            } else {
                // Error from the Flask microservice
                echo json_encode(["success" => false, "error" => $response_data['error']]);
            }
        } else {
            // HTTP error
            echo json_encode(["success" => false, "error" => "Failed to save event in microservice. HTTP code: $http_code"]);
        }
    } else {
        // Invalid request method
        echo json_encode(["success" => false, "error" => "Invalid request method"]);
    }
?>