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

        // Generate a unique image name (optional, if you want to save the file locally)
        $image_name = uniqid() . "_" . basename($_FILES['image']['name']);
        $target_path = $upload_dir . $image_name;

        // Move uploaded file to local server folder (optional, if you want to save the file locally)
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {

            // Change permissions of the uploaded image so it is accessible by the web server
            chmod($target_path, 0755); // Make the image publicly readable

            // Prepare form data to send to Vercel
            $post_data = [
                'event_id' => $event_id,
                'image' => new CURLFile($target_path) // Send the image file
            ];

            // Log the data being sent to Vercel as a string with { }
            $log_request = "Sending to Vercel: {event_id={$event_id}, image_name={$image_name}}";
            file_put_contents('vercel_request.log', $log_request . "\n", FILE_APPEND);

            // Send request to Vercel
            $vercel_url = "https://cs-361-micro-a.vercel.app/upload_image";
            $ch = curl_init($vercel_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
            curl_close($ch);

            // Decode Vercel response
            $vercel_data = json_decode($response, true);

            // Extract only the image name (not the full path)
            if (isset($vercel_data['image_name'])) {
                $vercel_data['image_name'] = basename($vercel_data['image_name']);
            }

            // Log the response from Vercel
            $log_response = "Response from Vercel (HTTP $http_code): " . json_encode($vercel_data);
            file_put_contents('vercel_response.log', $log_response . "\n", FILE_APPEND);

            if (isset($vercel_data['event_id']) && isset($vercel_data['image_name'])) {
                $event_id = $vercel_data['event_id'];
                $image_name = $vercel_data['image_name']; // Only the file name

                // Store image name in the database
                $stmt = $conn->prepare("UPDATE events SET image_path = ? WHERE event_id = ?");
                if (!$stmt) {
                    echo json_encode(["success" => false, "error" => "Database error: " . $conn->error]);
                    exit;
                }
                $stmt->bind_param("si", $image_name, $event_id);
                $stmt->execute();
                $stmt->close();

                // Return success response with only the file name
                echo json_encode(["success" => true, "event_id" => $event_id, "image_name" => $image_name]);
            } else {
                echo json_encode(["success" => false, "error" => "Invalid response from Vercel"]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Failed to upload"]);
        }

        // Redirect to dashboard (optional)
        header("Location: ../src/dashboard.php?event_id=" . $event_id);
        exit();
    } else {
        echo json_encode(["success" => false, "error" => "Invalid request method or missing image"]);
}
?>