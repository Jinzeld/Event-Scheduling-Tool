<?php
session_start();
require_once "../src/config.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to send a POST request to the Flask microservice
function sendDeleteRequest($data) {
    $url = "https://micro-c-event-action.vercel.app/delete_event"; // Flask microservice endpoint

    // Define separate log files for requests and responses
    $request_log_file = 'vercel_request.log';
    $response_log_file = 'vercel_response.log';

    // Log the request being sent to Vercel
    $log_request = "Sending to Vercel: " . json_encode($data);
    file_put_contents($request_log_file, $log_request . "\n", FILE_APPEND);

    // Initialize cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // Use POST method
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
        echo json_encode(["success" => false, "error" => "You must be logged in to delete events."]);
        exit;
    }

    // Get the event_id from the POST data
    $event_id = $_POST['event_id'] ?? null;

    // Validate event_id
    if (!$event_id) {
        echo json_encode(["success" => false, "error" => "Event ID is required"]);
        exit;
    }

    // Fetch event details from the local database
    $sql = "SELECT title, event_date, event_time FROM events WHERE event_id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($name, $date, $time);
            $stmt->fetch();

            // Prepare the data to send to the Flask microservice
            $event_data = [
                'name' => $name,
                'date' => $date,
                'time' => $time
            ];

            // Send the delete request to the Flask microservice
            list($http_code, $response) = sendDeleteRequest($event_data);

            // Handle the response
            if ($http_code === 200) {
                $response_data = json_decode($response, true);
                if ($response_data['success']) {
                    // Event deleted successfully in the microservice
                    // Now delete the event from the local database

                    $delete_sql = "DELETE FROM events WHERE event_id = ? AND user_id = ?";
                    if ($delete_stmt = $conn->prepare($delete_sql)) {
                        $delete_stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
                        if ($delete_stmt->execute()) {
                            echo json_encode([
                                "success" => true,
                                "message" => "Event deleted successfully in both microservice and local database."
                            ]);
                        } else {
                            echo json_encode([
                                "success" => false,
                                "error" => "Failed to delete event in local database: " . $conn->error
                            ]);
                        }
                        $delete_stmt->close();
                    } else {
                        echo json_encode([
                            "success" => false,
                            "error" => "Error preparing statement for local database deletion: " . $conn->error
                        ]);
                    }
                } else {
                    // Error from the Flask microservice
                    echo json_encode(["success" => false, "error" => $response_data['error']]);
                }
            } else {
                // HTTP error
                echo json_encode(["success" => false, "error" => "Failed to delete event in microservice. HTTP code: $http_code"]);
            }
        } else {
            // Event not found in the local database
            echo json_encode(["success" => false, "error" => "Event not found in the local database"]);
        }
        $stmt->close();
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Error preparing statement for fetching event details: " . $conn->error
        ]);
    }
} else {
    // Invalid request method
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>