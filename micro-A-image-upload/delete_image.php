<?php
require '../src/config.php'; // Database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    // Fetch the image path from the database
    $stmt = $conn->prepare("SELECT image_path FROM events WHERE event_id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->bind_result($image_path);
    $stmt->fetch();
    $stmt->close();

    if ($image_path) {
        // Delete the image from the local server
        $upload_dir = __DIR__ . '/uploads/';
        $target_path = $upload_dir . $image_path;

        if (file_exists($target_path)) {
            unlink($target_path); // Delete the file
        }

        // Send a request to the Vercel microservice to delete the image
        $vercel_url = "https://cs-361-micro-a.vercel.app/remove_image";
        $post_data = [
            'event_id' => $event_id,
            'image_path' => $image_path // Include the image_path in the request
        ];

        // Log the request being sent to Vercel as a string with { }
        $log_request = "Sending to Vercel: {event_id={$event_id}, image_path={$image_path}}";
        file_put_contents('vercel_request.log', $log_request . "\n", FILE_APPEND);

        $ch = curl_init($vercel_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        
        $vercel_response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
        curl_close($ch);

        // Log the response from Vercel
        $log_response = "Response from Vercel (HTTP $http_code): " . $vercel_response;
        file_put_contents('vercel_response.log', $log_response . "\n", FILE_APPEND);

        // Decode Vercel response
        $vercel_data = json_decode($vercel_response, true);

        if (isset($vercel_data['message']) && $vercel_data['message'] === "Image removed successfully") {
            // Update the database to remove the image path
            $stmt = $conn->prepare("UPDATE events SET image_path = NULL WHERE event_id = ?");
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $stmt->close();

            echo json_encode(["success" => true, "message" => "Image deleted successfully"]);
        } else {
            echo json_encode(["success" => false, "error" => "Failed to delete image from Vercel"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "No image found for this event"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request"]);
}
?>