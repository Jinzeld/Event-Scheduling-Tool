<?php
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    session_start();

    // Include config file
    require_once "config.php";

    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get form input
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate password match
        if ($password !== $confirm_password) {
            echo "Passwords do not match.";
            exit;
        }

        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Prepare the SQL query to insert the user data
        $sql = "INSERT INTO users (username, email, password_hash, created_at) VALUES (?, ?, ?, NOW())";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sss", $username, $email, $password_hash);
            
            if ($stmt->execute()) {
                echo "User registered successfully!";
            } else {
                echo "Error: " . $stmt->error;
            }
        }

        // Close the statement
        $stmt->close();
    }

    // Close the database connection
    $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventSync - Sign Up</title>
    <link rel="stylesheet" href="./style/signUp.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.php" class="nav-brand">Eventsync</a>
    </nav>

    <div class="signup-container">
        <h2>Sign Up</h2>

        <div class="signup-box">
            <form action="signup.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit">Submit</button> 
            </form>
        </div>
    </div>
</body>
</html>
    