<?php
header('Content-Type: application/json'); // Ensure the response is J

require '../src/config.php'; // Database connection file

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true); // Read JSON input
        $user_id = $input['user_id'] ?? null;

        if (!$user_id) {
            throw new Exception("user_id is required");
        }

        // Fetch events for the user from the database
        $stmt = $conn->prepare("SELECT event_id, title, event_date, event_time FROM events WHERE user_id = ? ORDER BY event_date ASC, event_time ASC");
        if (!$stmt) {
            throw new Exception("Failed to prepare SQL statement: " . $conn->error);
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $events = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($events)) {
            throw new Exception("No events found for this user");
        }

        // Find the event with the closest date and time
        $now = time();
        $closest_event = null;
        $closest_diff = PHP_INT_MAX;

        foreach ($events as $event) {
            // Combine event_date and event_time to create a full timestamp
            $event_timestamp = strtotime($event['event_date'] . ' ' . $event['event_time']);
            $time_diff = abs($event_timestamp - $now);

            if ($time_diff < $closest_diff) {
                $closest_diff = $time_diff;
                $closest_event = $event;
            }
        }

        if (!$closest_event) {
            throw new Exception("No upcoming events found");
        }

        // Prepare data to send to Vercel
        $post_data = [
            'user_id' => $user_id,
            'event_id' => $closest_event['event_id'],
            'title' => $closest_event['title'],
            'event_date' => $closest_event['event_date'], // Include event_date
            'event_time' => $closest_event['event_time'] // Include event_time
        ];

        // Log the request being sent to Vercel
        $log_request = "Sending to Vercel: " . json_encode($post_data);
        file_put_contents('vercel_request.log', $log_request . "\n", FILE_APPEND);

        // Send request to Vercel
        $vercel_url = "https://micro-d-event-alerts.vercel.app/check_upcoming_events";
        $ch = curl_init($vercel_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
        curl_close($ch);

        // Log the response from Vercel
        $log_response = "Response from Vercel (HTTP $http_code): " . $response;
        file_put_contents('vercel_response.log', $log_response . "\n", FILE_APPEND);

        // Decode Vercel response
        $vercel_data = json_decode($response, true);

        if (isset($vercel_data['success']) && $vercel_data['success']) {
            // Notify the user in the app
            $notification_message = "Upcoming Event: {$closest_event['title']} on {$closest_event['event_date']} at {$closest_event['event_time']}.";
            echo json_encode([
                "success" => true,
                "notifications" => [$closest_event], // Return the closest event as a notification
                "message" => $notification_message // Include the notification message
            ]);
        } else {
            throw new Exception("Failed to send notification");
        }
    } else {
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    // Log the error and return a JSON response
    error_log("Error in event_notification.php: " . $e->getMessage());
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>