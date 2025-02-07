<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event and Scheduling Tool</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="./style/index.css">

</head>

<body>
    <?php
        // Include the configuration file
        require_once 'config.php';
    ?>
    <div class = "container">
        <nav>
            <ul>
                <li><a href="./sign-In.php">Sign In</a></li>
                <li><a href="./sign-Up.php">Sign Up</a></li>
            </ul>
            
        </nav>
    </div>


</body>
</html>


