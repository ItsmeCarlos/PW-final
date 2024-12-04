<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book List</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center">Welcome to Your Personal Book List</h1>
        <p class="text-center">Keep track of your books and manage your personal library.</p>

        <div class="text-center mt-4">
            <a href="templates/login.php" class="btn btn-green">Log In</a>
            <a href="templates/signup.php" class="btn btn-secondary">Sign Up</a>
        </div>
    </div>
</body>
</html>
