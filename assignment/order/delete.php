<?php
include '../config.php'; // Database connection
include '../security.php';

// ✅ Restrict Access: Only Role ID 1 (Admin) & Role ID 3 (Procurement Officer) can delete orders
restrictAccess([1, 3], "read_orders.php", "You do not have permission to delete orders.");


// ✅ Check if request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ Ensure necessary data exists
    if (!isset($_POST['delete_id'], $_POST['csrf_token'])) {
        die("Error: Missing form data.");
    }

    // ✅ Validate CSRF Token
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die("Error: Invalid CSRF token.");
    }

    // ✅ Sanitize Order ID
    $order_id = intval($_POST['delete_id']);

    // ✅ Fetch the order status before deletion
    $stmt = $conn->prepare("SELECT status FROM `orders` WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();

    // ✅ Debugging: Ensure order data is retrieved
    if (!$order) {
        die("<script>alert('Error: Order not found.'); window.location.href='read_orders.php';</script>");
    } else {
        echo "<script>console.log('Order Retrieved: " . json_encode($order) . "');</script>";
    }

    // ✅ Check if the order status is "Approved" or "Completed"
    if (trim($order['status']) === "APPROVED" || trim($order['status']) === "COMPLETED") {
        die("<script>alert('Error: You cannot delete an Approved or Completed order.'); window.location.href='read_orders.php';</script>");
    }

    // ✅ Proceed with Deletion if status is "Pending"
    $stmt = $conn->prepare("DELETE FROM `orders` WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        echo "<script>alert('Order deleted successfully!'); window.location.href='read_orders.php';</script>";
    } else {
        echo "<script>alert('Error: Unable to delete order.'); window.location.href='read_orders.php';</script>";
    }

    $stmt->close();
}
?>
