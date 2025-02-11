<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    session_start();

    require_once "config.php";

    $error = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $login = "";
        $password = isset($_POST['password']) ? trim($_POST['password']) : null;

        // Determine if login was via username or email
        if (!empty($_POST['username'])) {
            $login = trim($_POST['username']);
            $sql = "SELECT user_id, username, password_hash FROM users WHERE username = ?";
        } elseif (!empty($_POST['email'])) {
            $login = trim($_POST['email']);
            $sql = "SELECT user_id, username, password_hash FROM users WHERE email = ?";  // Always fetch username
        }


        if (!empty($login) && !empty($password)) {
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("s", $login);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($user_id, $fetched_login, $hashed_password);
                    $stmt->fetch();

                    if (password_verify($password, $hashed_password)) {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["user_id"] = $user_id;
                        $_SESSION["login"] = $fetched_login;

                        header("Location: dashboard.php");
                        exit;
                    } else {
                        $error = "Invalid password.";
                    }
                } else {
                    $error = "No account found with that username or email.";
                }

                $stmt->close();
            }
        } else {
            $error = "Please enter your credentials.";
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
    <link rel="stylesheet" href="../style/signin.css">
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <a href="../index.php" class="nav-brand">EventSync</a>
    </nav>

    <div class="container">
        <h2 class="title">Sign In</h2>

        <div class="login-box">
            <!-- Tabs -->
            <div class="tabs">
                <button class="tab active" onclick="switchTab('username')">Username</button>
                <button class="tab" onclick="switchTab('email')">Email</button>
            </div>

             <!-- Display Error Message Inside Login Box -->
             <?php if (!empty($error)) { echo "<div class='error-message'><p>$error</p></div>"; } ?>

            <!-- Username Login Form -->
            <form id="usernameForm" action="signIn.php" method="POST">
                <input type="hidden" name="login_type" value="username">
                
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit" class="login-btn">Login</button>
            </form>

            <!-- Email Login Form (Hidden Initially) -->
            <form id="emailForm" action="signIn.php" method="POST" style="display: none;">
                <input type="hidden" name="login_type" value="email">

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="passwordEmail">Password:</label>
                <input type="password" id="passwordEmail" name="password" required>

                <button type="submit" class="login-btn">Login</button>

            </form>
        </div>
        <p class="signup_link">Not a member yet?<a href="signup.php"> SIGN UP</a></p>
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

