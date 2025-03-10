<?php

require '../src/config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $user_id = $data['user_id'] ?? null;
    $user_mode = $data['user_mode'] ?? null;
    $user_color = $data['user_color'] ?? null;

    if (!$user_id || !$user_mode || !$user_color) {
        echo json_encode(["error" => "Missing parameters"]);
        exit;
    }

    // Prepare data to send to Vercel
    $post_data = json_encode([
        "user_id" => $user_id,
        "theme" => $user_mode,
        "background_color" => $user_color
    ]);

    // Log the data being sent to Vercel
    file_put_contents('vercel_request.log', "Sending to Vercel: " . $post_data . "\n", FILE_APPEND);

    // Send request to Vercel
    $ch = curl_init("https://micro-b-visual-customization.vercel.app/set_preference");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
    curl_close($ch);

    // Log the response from Vercel
    file_put_contents('vercel_response.log', "Response from Vercel (HTTP $http_code): " . $response . "\n", FILE_APPEND);

    $data = json_decode($response, true);

    if (isset($data['error'])) {
        echo json_encode(["error" => "Flask API Error: " . $data['error']]);
        exit;
    }

    // Store preferences in MySQL
    $stmt = $conn->prepare("UPDATE users SET user_mode = ?, user_color = ? WHERE user_id = ?");
    if (!$stmt) {
        echo json_encode(["error" => "Database error: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("ssi", $user_mode, $user_color, $user_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => true, "message" => "Preferences updated successfully"]);
} else {
    echo json_encode(["error" => "Invalid request method"]);
}