<?php

include '../config.php'; 
include '../security.php'; 

// Restrict access to Admin (1) and Procurement Officer (3)
restrictAccess([1, 3], "../dashboard.php", "You do not have access to this page. Redirecting to Dashboard...");

$error = ""; 
$success = ""; 
$redirect = false; // redirection by default not needed

// generate CSRF token for form security
$csrf_token = generateCsrfToken();

// handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token before processing the request
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        die("CSRF validation failed! Unauthorized request.");
    }

    // sanitize and validate user inputs
    $name = validateInput($_POST['name']);
    $email = validateInput($_POST['email']);
    $telephone_number = validateInput($_POST['telephone_number']);
    $service_id = validateInput($_POST['service_id']);
    $payment_id = validateInput($_POST['payment_id']);

    // validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "Invalid email format.";
    }

    // validate phone number format (Only numbers, dashes, and spaces allowed)
    elseif (!preg_match("/^\+?\d{8,15}$/", $telephone_number)) {
        $error = "Invalid phone number. Only numbers, optional '+' sign, and length 8-15 digits are allowed.";
    }
    // ensure required fields are filled
    elseif (empty($name) || empty($email) || empty($telephone_number) || empty($service_id) || empty($payment_id)) {
        $error = "All fields are required."; // Display error for missing fields
    } else {
        try {
            // check if the vendor name already exists to prevent duplicate entries
            $stmt = $conn->prepare("SELECT COUNT(*) FROM vendor WHERE name = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                $error = "A vendor with this name already exists. Please use a different name.";
            } else {
                // insert data into the vendors table using a prepared statement
                $stmt = $conn->prepare("INSERT INTO vendor (name, email, telephone_number, service_id, payment_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssii", $name, $email, $telephone_number, $service_id, $payment_id);

                // execute the query and check if successful
                if ($stmt->execute()) {
                    $success = "Vendor added successfully! Redirecting back to Main Vendor page...";
                    $redirect = true; // redirect needed
                } else {
                    $error = "Error adding vendor.";
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// fetch services and payment terms for dropdown menus
$services = $conn->query("SELECT service_id, name FROM service_type");
$payment_terms = $conn->query("SELECT payment_id, name FROM payment_terms");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Vendor - AMC System</title>
    <link rel="stylesheet" href="../style.css">
    <script src="../functions.js"></script>
</head>
<body>
    <div class="container">
        <h2>Add Vendor</h2>

        <!-- Display error messages if any -->
        <?php if (!empty($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Display success messages if any -->
        <?php if (!empty($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="create.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="telephone_number">Phone:</label>
                <input type="text" id="telephone_number" name="telephone_number" required>
            </div>

            <div class="form-group">
                <label for="service_id">Service:</label>
                <select id="service_id" name="service_id" required>
                    <option value="">Select Service</option>
                    <?php while ($service = $services->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($service['service_id']) ?>">
                            <?= htmlspecialchars($service['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="payment_id">Payment Terms:</label>
                <select id="payment_id" name="payment_id" required>
                    <option value="">Select Payment Terms</option>
                    <?php while ($term = $payment_terms->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($term['payment_id']) ?>">
                            <?= htmlspecialchars($term['name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <button type="submit">Add Vendor</button>
        </form>

        <p><a href="vendor.php">Return to Main Vendor Page</a></p>
    </div>

    <?php if ($redirect): ?>
        <script>
            showSuccessMessageAndRedirect("<?= addslashes($success) ?>", "vendor.php");
        </script>
    <?php endif; ?>
</body>
</html>
