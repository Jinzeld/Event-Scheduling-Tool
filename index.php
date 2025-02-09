<?php
    session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calsync - Calendar and Event Tool</title>
    
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    
    <link rel="stylesheet" href="./style/index1.css">

</head>
<body>
    <div class="header">
        <a href="./index.php"><h2 style="font-style: italic;">Eventsync</h2></a>
        <div>
            <a href="./src/signIn.php">Sign In</a>
            <a href="./src/signup.php" class="btn btn-primary">Sign Up</a>
        </div>
    </div>
    <div class="container">
        <div class="main-content">
            <h1>Scheduling and event tool.</h1>
            <p>We make it easy for you to take down your events and keep track of your schedule!</p>
            <div class="cta-buttons">
                <a href="./src/signup.php" class="btn btn-primary">Create your event ▶</a>
                <a href="#" class="btn btn-secondary">See how it works ▶</a>
            </div>
        </div>

        <div class="carousel-container">
            <div id="productCarousel" class="carousel slide" data-ride="carousel">
                <div class="carousel-inner">
                    <div class="item active">
                        <img src="./images/dashboard.png" alt="Product Image 1">
                    </div>
                    <div class="item">
                        <img src="./images/help.png" alt="Product Image 2">
                    </div>
                    <div class="item">
                        <img src="./images/delete.png" alt="Product Image 3">
                    </div>
                </div>
                <a class="left carousel-control" href="#productCarousel" data-slide="prev">
                    <span class="glyphicon glyphicon-chevron-left"></span>
                </a>
                <a class="right carousel-control" href="#productCarousel" data-slide="next">
                    <span class="glyphicon glyphicon-chevron-right"></span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
