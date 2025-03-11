<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database configuration
require_once "../src/config.php";

// Log file path
$log_file = 'vercel_event_conflict.log';

// Function to send a POST request to Vercel
function sendToVercel($url, $data) {
    // Define separate log files for requests and responses
    $request_log_file = 'vercel_request.log';
    $response_log_file = 'vercel_response.log';

    // Log the request being sent to Vercel
    $log_request = "Sending to Vercel: " . json_encode($data);
    file_put_contents($request_log_file, $log_request . "\n", FILE_APPEND);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Log the response from Vercel
    $log_response = "Response from Vercel (HTTP $http_code): " . $response;
    file_put_contents($response_log_file, $log_response . "\n", FILE_APPEND);

    return [$http_code, $response];
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $user_id = $_POST['user_id'] ?? null;
    $title = $_POST['title'] ?? null;
    $event_date = $_POST['event_date'] ?? null;
    $event_time = $_POST['event_time'] ?? null;

    // Validate required fields
    if (!$user_id || !$title || !$event_date || !$event_time) {
        echo json_encode(["success" => false, "error" => "All fields are required"]);
        exit;
    }

    // Fetch existing events for the user from the database
    $sql = "SELECT event_id, title, event_date, event_time FROM events WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_events = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Prepare the new event
    $new_event = [
        'user_id' => $user_id,
        'title' => $title,
        'event_date' => $event_date,
        'event_time' => $event_time
    ];

    // Send existing events and the new event to Vercel for conflict checking
    $vercel_url = "https://micro-d-event-alerts.vercel.app/check_conflicts";
    list($http_code, $response) = sendToVercel($vercel_url, [
        "existing_events" => $existing_events,
        "new_event" => $new_event
    ]);

    // Handle the response from Vercel
    if ($http_code === 200) {
        $response_data = json_decode($response, true);
        if ($response_data['success']) {
            if (!empty($response_data['conflicts'])) {
                // Conflicts found, alert the user
                echo json_encode([
                    "success" => false,
                    "message" => "Event conflicts with existing events.",
                    "conflicts" => $response_data['conflicts']
                ]);
            } else {
                // No conflicts found
                echo json_encode([
                    "success" => true,
                    "message" => "No conflicts found. Event can be created."
                ]);
            }
        } else {
            // Vercel returned an error
            echo json_encode(["success" => false, "error" => $response_data['error']]);
        }
    } else {
        // Vercel returned a non-200 status code
        echo json_encode(["success" => false, "error" => "Failed to check for conflicts. Vercel returned HTTP $http_code"]);
    }
} else {
    // Invalid request method
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>