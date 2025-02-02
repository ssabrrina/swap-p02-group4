<?php
session_start(); // ✅ Ensure that session handling starts in every file that includes this script

// ✅ Set session timeout (15 minutes)
define('SESSION_TIMEOUT', 900); // 900 seconds = 15 minutes

// ✅ Check if session timeout has been exceeded
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
    // If the last activity timestamp is older than the defined timeout, destroy the session and log the user out
    session_unset(); // Remove all session variables
    session_destroy(); // Destroy the session
    header("Location: login.php?timeout=1"); // Redirect to login with a timeout flag
    exit; // Ensure script execution stops after redirect
}

// ✅ Update last activity timestamp to the current time to maintain session validity
$_SESSION['last_activity'] = time();

// ✅ Secure session settings
if (session_status() === PHP_SESSION_NONE) { // Check if the session is not already started
    session_start(); // Start a new session
    ini_set('session.cookie_httponly', 1); // Prevent JavaScript from accessing session cookies (protection against XSS)
    ini_set('session.cookie_secure', 1); // Ensure cookies are sent over HTTPS only (enable only if using HTTPS)
    ini_set('session.use_strict_mode', 1); // Prevent session fixation attacks by rejecting uninitialized session IDs
}

// ✅ Input validation function to sanitize user input
function validateInput($data) {
    $data = trim($data); // Remove whitespace from the beginning and end of the string
    $data = stripslashes($data); // Remove backslashes (\) from the string
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8'); // Convert special characters to prevent XSS attacks
    return $data; // Return sanitized data
}

// ✅ CSRF (Cross-Site Request Forgery) Protection Functions

// Generate a CSRF token if one does not exist
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) { // If no CSRF token is set, generate a new one
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Create a random token
    }
    return $_SESSION['csrf_token']; // Return the token
}

// Validate the CSRF token received from the form submission
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token); // Compare the submitted token with the stored token
}

// ✅ Data Encryption & Decryption Functions

// Encrypt data using AES-256-CBC encryption method
function encryptData($data, $key) {
    $iv = random_bytes(16); // Generate a random initialization vector (IV)
    $ciphertext = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv); // Encrypt the data
    return base64_encode($iv . $ciphertext); // Return the encrypted data as a base64-encoded string (IV + ciphertext)
}

// Decrypt data that was encrypted using encryptData function
function decryptData($encryptedData, $key) {
    $data = base64_decode($encryptedData); // Decode the base64-encoded encrypted data
    $iv = substr($data, 0, 16); // Extract the first 16 bytes as the IV
    $ciphertext = substr($data, 16); // Extract the remaining part as the actual ciphertext
    return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, 0, $iv); // Decrypt the data and return it
}

// ✅ Role-Based Access Restriction Function

function restrictAccess($allowed_roles, $redirect_url, $message) {
    // ✅ Ensure user is logged in
    if (!isset($_SESSION['session_role'])) { // If user session role is not set
        $message = "You are not logged in. Redirecting to Login..."; // Update message
        $redirect_url = "login.php"; // Redirect to login page
    }

    // ✅ Check if the user has permission based on their role
    if (!isset($_SESSION['session_role']) || !in_array($_SESSION['session_role'], $allowed_roles)) {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8"> <!-- Set character encoding -->
            <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ensure responsive layout -->
            <title>Access Denied</title> <!-- Page title -->
            <link rel="stylesheet" href="style.css"> <!-- Include CSS for styling -->
            <script src="functions.js"></script> <!-- Include JavaScript functions -->
            <script>
                // ✅ Redirect user with a success message
                showSuccessMessageAndRedirect("<?= addslashes($message) ?>", "<?= addslashes($redirect_url) ?>");
            </script>
        </head>
        <body>
            <div class="container">
                <h2>Access Denied</h2> <!-- Display "Access Denied" message -->
                <p><?= htmlspecialchars($message) ?></p> <!-- Show the reason for denial -->
            </div>
        </body>
        </html>
        <?php
        exit; // Stop script execution to prevent further access
    }
}
?>
