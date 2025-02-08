<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

// Include database connection
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        // Prepare SQL statement to fetch user by email
        $sql = "SELECT id, username, password_hash FROM users WHERE email = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $username, $hashed_password);
                $stmt->fetch();

                // Verify the password
                if (password_verify($password, $hashed_password)) {
                    // Start user session
                    $_SESSION["loggedin"] = true;
                    $_SESSION["id"] = $id;
                    $_SESSION["username"] = $username;

                    // Redirect to a dashboard or homepage
                    header("Location: dashboard.php");
                    exit;
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "No account found with that email.";
            }

            $stmt->close();
        }
    } else {
        $error = "Please enter both email and password.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventSync - Sign In</title>
    <link rel="stylesheet" href="./style/signIn.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="index.php" class="nav-brand">Eventsync</a>
    </nav>

    <div class="container">
        <h2 class="title">Sign In</h2>

        <div class="login-box">
            <!-- Tabs -->
            <div class="tabs">
                <button class="tab active" onclick="switchTab('username')">Username</button>
                <button class="tab" onclick="switchTab('email')">Email</button>
            </div>

            <!-- Username Login Form -->
            <form id="usernameForm" action="signin.php" method="POST">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit" class="login-btn">Login</button>
            </form>

            <!-- Email Login Form (Hidden Initially) -->
            <form id="emailForm" action="signin.php" method="POST" style="display: none;">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="passwordEmail">Password:</label>
                <input type="password" id="passwordEmail" name="password" required>

                <button type="submit" class="login-btn">Login</button>
            </form>
        </div>
    </div>

    <script>
        function switchTab(option) {
            if (option === 'username') {
                document.getElementById('usernameForm').style.display = 'block';
                document.getElementById('emailForm').style.display = 'none';
                document.querySelectorAll('.tab')[0].classList.add('active');
                document.querySelectorAll('.tab')[1].classList.remove('active');
            } else {
                document.getElementById('usernameForm').style.display = 'none';
                document.getElementById('emailForm').style.display = 'block';
                document.querySelectorAll('.tab')[1].classList.add('active');
                document.querySelectorAll('.tab')[0].classList.remove('active');
            }
        }
    </script>

</body>
</html>
