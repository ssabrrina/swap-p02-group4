<?php
include '../config.php';
include '../security.php';

// ✅ Ensure it's a POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ Validate CSRF Token
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        die("Error: Invalid CSRF token.");
    }

    // ✅ Ensure required data is present
    if (!isset($_POST['order_id'], $_POST['status'])) {
        die("Error: Missing form data.");
    }

    // ✅ Sanitize Inputs
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']); // Trim extra spaces

    // ✅ Debugging - Check input values
    error_log("Updating Order ID: $order_id | Status: $status");

    // ✅ Prevent invalid statuses
    $valid_statuses = ['Pending', 'Approved', 'Completed'];
    if (!in_array($status, $valid_statuses)) {
        die("Error: Invalid status.");
    }

    // ✅ Update order status in database
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        echo "Order status updated successfully!";
    } else {
        echo "Error updating order status: " . $stmt->error;
    }

    $stmt->close();
} else {
    die("Invalid request.");
}
?>
