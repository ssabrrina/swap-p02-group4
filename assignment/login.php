<?php
include_once 'config.php'; // Include database connection
include_once 'security.php'; // Include security functions

$error = ''; // Variable for storing error messages
$success = ''; // Variable for storing success messages

// ✅ Check if session timeout happened
if (isset($_GET['timeout']) && $_GET['timeout'] == 1) { // If the URL contains 'timeout=1', it means the session expired
    $error = "Your session has expired due to inactivity. Please log in again."; // Set an error message for session timeout
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Check if the request method is POST (form submission)
    // Sanitize and validate inputs
    $username = validateInput($_POST['username']); // Remove extra spaces, slashes, and escape special characters for security
    $password = validateInput($_POST['password']); // Sanitize the password input

    // Check for empty fields
    if (empty($username) || empty($password)) { // If either the username or password is empty
        $error = "Both username and password are required."; // Set an error message
    } else {
        try {
            // Check if the user exists in the database
            $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?"); // Prepare SQL statement to prevent SQL injection
            $stmt->bind_param("s", $username); // Bind the username parameter to the SQL query
            $stmt->execute(); // Execute the query
            $result = $stmt->get_result(); // Get the result of the executed query

            if ($result->num_rows === 0) { // If no user is found in the database
                $error = "No such username found."; // Set an error message
            } else {
                $user = $result->fetch_assoc(); // Fetch user data as an associative array

                // Verify the password entered by the user
                if (password_verify($password, $user['PASSWORD'])) { // Compare the hashed password in the database with the entered password
                    // Store user session variables
                    $_SESSION['session_userid'] = $user['USER_ID']; // Store user ID in session
                    $_SESSION['session_username'] = $user['USERNAME']; // Store username in session
                    $_SESSION['session_role'] = $user['ROLE_ID']; // Store user role in session
                    $_SESSION['needs_password_reset'] = $user['needs_password_reset']; // Store password reset requirement flag

                    // Check if the user needs to reset their password
                    if ($user['needs_password_reset'] == 1) { // If the user is required to reset their password
                        $error = "Your password needs to be reset. Please click on 'Forgot your password?' to reset it."; // Display an error message
                    } else {
                        $redirect = "dashboard.php"; // Redirect the user to the dashboard upon successful login

                        // Set success message and call JavaScript function for delayed redirect
                        $success = "Login successful! Redirecting..."; // Set a success message
                        echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    showSuccessMessageAndRedirect('" . addslashes($success) . "', '" . $redirect . "'); // Call JavaScript function for redirect
                                });
                              </script>";
                    }
                } else {
                    $error = "Invalid password."; // Set an error message if the password is incorrect
                }
            }

            $stmt->close(); // Close the prepared statement
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage(); // Catch and display any database-related errors
        }
    }
}
?>

<?php include 'header.php'; ?> <!-- Include the header file -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Set character encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Set viewport for responsive design -->
    <title>Login - AMC System</title> <!-- Set page title -->
    <link rel="stylesheet" href="style.css"> <!-- Link to external CSS file -->
    <script src="functions.js"></script> <!-- Include JavaScript functions -->
</head>
<body>
    <div class="container">
        <h2>Login</h2>

        <!-- Display error messages -->
        <?php if (!empty($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div> <!-- Display error messages safely -->
        <?php endif; ?>

        <!-- Display success messages -->
        <?php if (!empty($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div> <!-- Display success messages safely -->
        <?php endif; ?>

        <!-- Login form -->
        <form action="login.php" method="post"> <!-- Form submission sends data to 'login.php' -->
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required> <!-- Username input field (required) -->
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required> <!-- Password input field (required) -->
            </div>
            <button type="submit">Login</button> <!-- Submit button for login -->
        </form>

        <!-- Forgot password link -->
        <p>Forgot your password? <a href="forgot_pw.php">Reset here</a></p>
    </div>
</body>
</html>

<?php include 'footer.php'; ?> <!-- Include the footer file -->
