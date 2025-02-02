<?php
// Include database connection file
include 'config.php';

// Error handling for form submission
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Grab the form input values
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Simple validation for empty fields
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm_password) {
        // Check if passwords match
        $error = 'Passwords do not match.';
    } else {
        // Hash the password before saving to the database
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // Check if the username or email already exists
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Insert the new user into the database
            $stmt = $pdo->prepare("INSERT INTO user (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $password_hash]);
            // Redirect to login page after successful sign-up
            header('Location: login.php');
            exit();
        }
    }
}

include 'header.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AMC System Register </title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <h2>Sign Up</h2>
        <!-- Display error message -->
        <?php if ($error): ?>
            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        
        <!-- Sign-up Form -->
        <form action="login.php" method="post">
            <div class="form-group">
                <label for="username">Username *</label>
                <input type="text" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit">Sign Up</button>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
<?php include 'footer.php'; ?>