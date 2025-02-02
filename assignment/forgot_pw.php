<?php
// ✅ Include required files for database connection and security functions
include_once 'config.php'; // Database connection
include_once 'security.php'; // Security functions
require 'vendor/autoload.php'; // Include PHPMailer for sending emails

// ✅ Import PHPMailer classes for email handling
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = ''; // Variable to store error messages
$success = ''; // Variable to store success messages

// ✅ Generate a CSRF token for the form
$csrf_token = generateCsrfToken();

// ✅ Check if a user is logged in and retrieve their username from the session
$logged_in_username = isset($_SESSION['session_username']) ? $_SESSION['session_username'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // ✅ Check if the form is submitted
    // ✅ Validate CSRF Token before processing the request
    if (!validateCsrfToken($_POST['csrf_token'])) {
        $error = "Invalid request. Please try again.";
    } else {
        $username = validateInput($_POST['username']); // Sanitize the username input

        if (empty($username)) { // ✅ Validate if username is provided
            $error = "Username is required."; // Set error message
        } else {
            try {
                // ✅ Check if the username exists in the database
                $stmt = $conn->prepare("SELECT * FROM user WHERE username = ?");
                $stmt->bind_param("s", $username); // Bind username to the query
                $stmt->execute(); // Execute the query
                $result = $stmt->get_result(); // Get the result set

                if ($result->num_rows === 1) { // ✅ If user exists, proceed with password reset
                    $user = $result->fetch_assoc(); // Fetch user details

                    // ✅ Generate a unique token for password reset
                    $token = bin2hex(random_bytes(32)); // Create a random secure token
                    $token_expires = time() + (60 * 15); // Token valid for 15 minutes

                    // ✅ Store the token and expiry time in the database
                    $stmt = $conn->prepare("UPDATE user SET token = ?, token_expires = ? WHERE username = ?");
                    $stmt->bind_param("sis", $token, $token_expires, $username);
                    $stmt->execute();

                    // ✅ Prepare the reset password email
                    $reset_link = "http://localhost/assignment/reset_pw.php?token=" . $token; // Construct reset link

                    $mail = new PHPMailer(true); // Initialize PHPMailer
                    try {
                        // ✅ Set up SMTP email server configuration
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com'; // SMTP host
                        $mail->SMTPAuth = true;
                        $mail->Username = 'jaimiepehhx@gmail.com'; // Sender email
                        $mail->Password = 'xfzk todl nfim ggvr'; // Email password or app password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Secure connection
                        $mail->Port = 587; // SMTP port

                        // ✅ Set email recipients
                        $mail->setFrom('limpeh12345678910@gmail.com', 'gmail'); // Sender email
                        $mail->addAddress($user['EMAIL'], $user['USERNAME']); // Recipient email and name

                        // ✅ Configure email content
                        $mail->isHTML(true);
                        $mail->Subject = 'Password Reset Request';
                        $mail->Body = "Hello " . htmlspecialchars($user['USERNAME']) . "!,<br><br>"
                            . "Click the link below to reset your password:<br>"
                            . "<a href='" . htmlspecialchars($reset_link) . "'>" . htmlspecialchars($reset_link) . "</a><br><br>"
                            . "This link will expire in 15 minutes.";

                        // ✅ Send the email
                        $mail->send();
                        $success = "A reset link has been sent to your email."; // Display success message

                        // ✅ Destroy session after sending the reset link if user was logged in
                        if (!empty($logged_in_username)) {
                            session_unset(); // Unset all session variables
                            session_destroy(); // Destroy the session
                        }
                        
                    } catch (Exception $e) {
                        $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"; // Handle email errors
                    }
                } else {
                    $error = "No username found."; // Display error if username is not found
                }

                $stmt->close(); // Close the prepared statement
            } catch (Exception $e) {
                $error = "Error: " . $e->getMessage(); // Handle database errors
            }
        }
    }
}
?>

<?php include 'header.php'; ?> <!-- ✅ Include the page header -->

<!-- ✅ Forgot Password HTML form -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- Set character encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ensure responsiveness -->
    <title>Reset Password - Request</title> <!-- Page title -->
    <link rel="stylesheet" href="style.css"> <!-- Link to external CSS file -->
</head>
<body>
    <div class="container">
        <h2>Reset Your Password</h2>
        <p>
            <?php if (!empty($logged_in_username)): ?> <!-- ✅ Check if user is logged in -->
                Confirm your username to change your password.
            <?php else: ?>
                Please enter your username to reset your password.
            <?php endif; ?>
        </p>

        <!-- ✅ Display error messages -->
        <?php if (!empty($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div> <!-- Show error message -->
        <?php elseif (!empty($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div> <!-- Show success message -->
        <?php endif; ?>

        <!-- ✅ Password Reset Request Form -->
        <form action="forgot_pw.php" method="post">
            <!-- ✅ CSRF Protection: Add a hidden field for CSRF token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="username">Username:</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    value="<?= htmlspecialchars($logged_in_username) ?>" 
                    <?= !empty($logged_in_username) ? 'readonly' : '' ?> 
                    required> <!-- If user is logged in, auto-fill and make read-only -->
            </div>
            <button type="submit">Send Reset Link</button> <!-- Submit button -->
        </form>
    </div>
</body>
</html>

<?php include 'footer.php'; ?> <!-- ✅ Include the page footer -->
