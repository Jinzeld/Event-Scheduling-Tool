<?php
require '../src/config.php'; // Database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $event_id = $_POST['event_id'];
    
    // Use an absolute path for the uploads folder
    $upload_dir = __DIR__ . '/uploads/'; 

    // Check if the folder exists, if not, create it
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            die(json_encode(["success" => false, "error" => "Failed to create directory: $upload_dir"]));
        }
    }

    // Set correct permissions (optional)
    chmod($upload_dir, 0777);

    // Generate a unique image name
    $image_name = uniqid() . "_" . basename($_FILES['image']['name']);
    $target_path = $upload_dir . $image_name;

    // Move uploaded file to local server folder
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {

        // Change permissions of the uploaded image so it is accessible by the web server
        chmod($target_path, 0755); // Make the image publicly readable
        
        // Send image name to Vercel microservice
        $vercel_url = "https://cs-361-micro-a.vercel.app/";
        $post_data = [
            'event_id' => $event_id,
            'image_name' => $image_name
        ];

        $ch = curl_init($vercel_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        $vercel_response = curl_exec($ch);
        curl_close($ch);

        // Decode Vercel response
        $vercel_data = json_decode($vercel_response, true);
        if (isset($vercel_data['image_name'])) {
            $image_name = $vercel_data['image_name']; // Confirmed image name from Vercel
        }

        // Store image name in the database
        $stmt = $conn->prepare("UPDATE events SET image_path = ? WHERE event_id = ?");
        $stmt->bind_param("si", $image_name, $event_id);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["success" => true, "image_path" => $image_name]);
    } else {
        echo json_encode(["success" => false, "error" => "Failed to upload"]);
    }

    header("Location: ../src/dashboard.php?event_id=" . $event_id);
    exit();
}
?>
