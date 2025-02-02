<?php
session_start();
session_destroy(); // Destroy the session to log out the user
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <link rel="stylesheet" href="style.css">
    <script src="functions.js"></script> <!-- Ensure this contains the function -->
</head>
<body>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            showSuccessMessageAndRedirect("You have successfully logged out!", "login.php");
        });
    </script>
</body>
</html>
