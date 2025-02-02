<?php

include '../config.php'; 
include '../security.php'; 

// restrict access to Admin (1) and Procurement Officer (3)
restrictAccess([1, 3], "../dashboard.php", "You do not have access to this page. Redirecting to Dashboard...");

$error = ""; 
$success = ""; 
$vendor = null; // store vendor details
$redirect = false; // redirection default not needed

// generate a CSRF token for the form
$csrf_token = generateCsrfToken();

// fetch vendor data if `vendor_id` is provided in GET
if (isset($_GET['vendor_id'])) {
    $vendor_id = intval($_GET['vendor_id']); // sanitize input

    try {
        $stmt = $conn->prepare("SELECT * FROM vendor WHERE vendor_id = ?");
        $stmt->bind_param("i", $vendor_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $vendor = $result->fetch_assoc();
        $stmt->close();

        if (!$vendor) {
            $error = "Vendor not found.";
        }
    } catch (Exception $e) {
        $error = "Error fetching vendor data: " . $e->getMessage();
    }
} else {
    $error = "No vendor selected for update.";
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $vendor) {
    // validate CSRF token before processing the request
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        die("CSRF validation failed! Unauthorized request.");
    }

    // get updated inputs
    $name = validateInput($_POST['name']);
    $email = validateInput($_POST['email']);
    $telephone_number = validateInput($_POST['telephone_number']);
    $service_id = validateInput($_POST['SERVICE_ID']);
    $payment_id = validateInput($_POST['PAYMENT_ID']);

    // validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    }

    // validate phone number format (Only numbers, optional '+' sign, and length 8-15 digits allowed)
    elseif (!preg_match("/^\+?\d{8,15}$/", $telephone_number)) {
        $error = "Invalid phone number. Only numbers, optional '+' sign, and length 8-15 digits are allowed.";
    }

    // check if any changes were made
    elseif (
        $name === $vendor['NAME'] &&
        $email === $vendor['EMAIL'] &&
        $telephone_number === $vendor['TELEPHONE_NUMBER'] &&
        $service_id === $vendor['SERVICE_ID'] &&
        $payment_id === $vendor['PAYMENT_ID']
    ) {
        $error = "No changes detected. Please modify at least one field before updating.";
    } else {
        try {
            // prepare update query dynamically
            $fields = [];
            $params = [];
            $types = "";

            if ($name !== $vendor['NAME'] && !empty($name)) {
                $fields[] = "name = ?";
                $params[] = $name;
                $types .= "s";
            }
            if ($email !== $vendor['EMAIL'] && !empty($email)) {
                $fields[] = "email = ?";
                $params[] = $email;
                $types .= "s";
            }
            if ($telephone_number !== $vendor['TELEPHONE_NUMBER'] && !empty($telephone_number)) {
                $fields[] = "telephone_number = ?";
                $params[] = $telephone_number;
                $types .= "s";
            }
            if ($service_id !== $vendor['SERVICE_ID'] && !empty($service_id)) {
                $fields[] = "SERVICE_ID = ?";
                $params[] = $service_id;
                $types .= "i";
            }
            if ($payment_id !== $vendor['PAYMENT_ID'] && !empty($payment_id)) {
                $fields[] = "PAYMENT_ID = ?";
                $params[] = $payment_id;
                $types .= "i";
            }

            // if at least one field is changed, proceed with update
            if (!empty($fields)) {
                $params[] = $vendor_id;
                $types .= "i";

                $query = "UPDATE vendor SET " . implode(", ", $fields) . " WHERE vendor_id = ?";
                $stmt = $conn->prepare($query);
                $stmt->bind_param($types, ...$params);

                if ($stmt->execute()) {
                    $success = "Vendor updated successfully! Redirecting back to Main Vendor Page...";
                    $redirect = true; // set redirection flag
                } else {
                    $error = "Error updating vendor: " . $stmt->error;
                }
                $stmt->close();
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}

// fetch services and payment terms for dropdown menus
$services = $conn->query("SELECT SERVICE_ID, name FROM service_type");
$payment_terms = $conn->query("SELECT PAYMENT_ID, name FROM payment_terms");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Vendor - AMC System</title>
    <link rel="stylesheet" href="../style.css">
    <script src="../functions.js"></script> 
</head>
<body>
    <div class="container">
        <h2>Update Vendor</h2>

        <?php if (!empty($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($vendor): ?>
            <form action="update.php?vendor_id=<?= htmlspecialchars($vendor_id) ?>" method="POST">
            
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($vendor['NAME']) ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($vendor['EMAIL']) ?>">
                </div>

                <div class="form-group">
                    <label for="telephone_number">Phone:</label>
                    <input type="text" id="telephone_number" name="telephone_number" value="<?= htmlspecialchars($vendor['TELEPHONE_NUMBER']) ?>">
                </div>

                <div class="form-group">
                    <label for="SERVICE_ID">Service:</label>
                    <select id="SERVICE_ID" name="SERVICE_ID">
                        <option value="">Select Service</option>
                        <?php while ($service = $services->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($service['SERVICE_ID']) ?>"
                                <?= $service['SERVICE_ID'] == ($vendor['SERVICE_ID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($service['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="PAYMENT_ID">Payment Terms:</label>
                    <select id="PAYMENT_ID" name="PAYMENT_ID">
                        <option value="">Select Payment Terms</option>
                        <?php while ($term = $payment_terms->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($term['PAYMENT_ID']) ?>"
                                <?= $term['PAYMENT_ID'] == ($vendor['PAYMENT_ID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($term['name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit">Update Vendor</button>
            </form>
        <?php endif; ?>

        <p><a href="vendor.php">Return to Main Vendor Page</a></p>
    </div>

    <?php if ($redirect): ?>
        <script>
            showSuccessMessageAndRedirect("<?= addslashes($success) ?>", "vendor.php");
        </script>
    <?php endif; ?>
</body>
</html>
