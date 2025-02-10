<?php
include_once 'config.php'; // Include the database connection file
include_once 'security.php'; // Include security functions for validation and protection

$error = ''; // Variable to store error messages
$success = ''; // Variable to store success messages

//  Generate CSRF Token for the form
$csrf_token = generateCsrfToken();

// Get the token from the URL
$token = $_GET['token'] ?? null; // Retrieve the token from the URL or set it to null if not provided
$time = time(); // Get the current timestamp
$token_valid = false;

// Check if the token is missing or invalid
if (!$token) {
    $error = "Invalid or missing token."; // Set an error message if token is not present
} else {
    try {
        // Fetch the user associated with the token if it is still valid
        $stmt = $conn->prepare("SELECT username FROM user WHERE token = ? AND token_expires > ?");
        $stmt->bind_param("si", $token, $time); // Bind the token and current timestamp to the query
        $stmt->execute(); // Execute the query
        $result = $stmt->get_result(); // Get the result set

        if ($result->num_rows === 1) { // Check if exactly one user matches the token
            $user = $result->fetch_assoc(); // Fetch the user data
            $username = $user['username']; // Store the username for later use
            $token_valid = true;
        } else {
            $error = "Invalid or expired token. Please request a new password reset."; // Set an error if token is expired or invalid
        }

        $stmt->close(); // Close the prepared statement
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage(); // Catch and display any database errors
    }
}

// Handle form submission when the user submits a new password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($error)) { // Check if the request is POST and no errors exist
    // Validate CSRF token before processing the request
    if (!validateCsrfToken($_POST['csrf_token'])) {
        $error = "Invalid request. Please try again.";
    } else {
        $new_password = validateInput($_POST['new_password']); // Sanitize the new password input
        $confirm_password = validateInput($_POST['confirm_password']); // Sanitize the confirm password input

        // Validate the new password fields
        if (empty($new_password) || empty($confirm_password)) { // Check if any field is empty
            $error = "All fields are required."; // Set an error message
        } elseif ($new_password !== $confirm_password) { // Check if passwords match
            $error = "Passwords do not match."; // Set an error message if they don't match
        } else {
            try {
                //  Hash the new password for security before storing in the database
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the user's password and clear the token
                $stmt = $conn->prepare("UPDATE user SET password = ?, token = NULL, token_expires = NULL, needs_password_reset = 0 WHERE username = ?");
                $stmt->bind_param("ss", $hashed_password, $username); // Bind new password and username

                if ($stmt->execute()) { // Execute the query
                    $success = "Password reset successfully!"; // Set success message
                    session_unset(); // Clear session variables
                    session_destroy(); // Destroy session to log out the user
                    $token_valid = false;
                } else {
                    $error = "Error updating password."; // Set error message if update fails
                }

                $stmt->close(); // Close the prepared statement
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage(); // Catch and display any database errors
            }
        }
    }
}
?>

<?php include 'header.php'; ?> <!-- Include the header file -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Set character encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ensure responsiveness -->
    <title>Reset Password</title> <!-- Page title -->
    <link rel="stylesheet" href="style.css"> <!-- Link to external CSS file -->
</head>
<body>
    <div class="container">
        <h1>Reset Password</h1>

        <!-- Display error messages if any exist -->
        <?php if (!empty($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div> <!-- Display the success message safely -->
            <a href="login.php">Login</a> <!-- Provide a link to login after a successful password reset -->
        <?php elseif ($token_valid): ?>
            <!-- Password Reset Form -->
            <form method="post">
                <!-- CSRF Protection: Add a hidden field for CSRF token -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required> <!-- Input field for new password -->
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required> <!-- Input field for confirming password -->
                </div>
                <button type="submit">Reset Password</button> <!-- Submit button to reset password -->
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

<?php include 'footer.php'; ?> <!-- Include the footer file -->
