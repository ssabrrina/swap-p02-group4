<?php
include '../config.php'; // Database connection
include '../security.php';
include '../header.php';
include '../navigation.php';

// Restrict Access: Only Role ID 1 (Admin) & Role ID 3 (Procurement Officer) can access
restrictAccess([1, 3], "../dashboard.php", "You do not have permission to access this page.");

// Check if `procurement_id` is provided in URL
if (!isset($_GET['procurement_id'])) {
    die("Error: Procurement ID is missing.");
}

$procurement_id = intval($_GET['procurement_id']); // Convert to integer for security

// Fetch procurement details
$query = "SELECT p.procurement_id, p.item_id, p.quantity, p.priority_level, p.status, 
                i.name AS item_name 
        FROM procurement p
        LEFT JOIN inventory i ON p.item_id = i.item_id
        WHERE p.procurement_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $procurement_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Procurement request not found.");
}

$procurement = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF Token
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die("<script>alert('Invalid CSRF token. Please try again.'); window.location.href='procurement.php';</script>");
    }

    // Ensure procurement_id is set
    if (!isset($_POST['procurement_id'])) {
        die("Error: Procurement ID is missing.");
    }

    // Sanitize Inputs
    $procurement_id = intval($_POST['procurement_id']);
    $quantity = trim($_POST['quantity']);
    $priority_level = trim($_POST['priority_level']);
    $status = trim($_POST['status']);

    // Ensure Quantity is a Whole Positive Number
    if (!ctype_digit($quantity) || intval($quantity) <= 0) {
        die("<script>alert('Error: Quantity must be a positive whole number greater than 0.'); window.location.href='update.php?procurement_id=$procurement_id';</script>");
    }

    //  Validate Status - Prevent SQL Injection
    $allowed_statuses = ["PENDING", "APPROVED", "COMPLETED"];
    if (!in_array(strtoupper($status), $allowed_statuses)) {
        die("<script>alert('Error: Invalid status.'); window.location.href='update.php?procurement_id=$procurement_id';</script>");
    }

    // Validate Priority Level - Prevent SQL Injection
    $allowed_priorities = ["Low", "Medium", "High"];
    if (!in_array($priority_level, $allowed_priorities)) {
        die("<script>alert('Error: Invalid priority level.'); window.location.href='update.php?procurement_id=$procurement_id';</script>");
    }

    // Prevent Reverting Completed Requests
    if ($procurement['status'] === "COMPLETED" && $status !== "COMPLETED") {
        die("<script>alert('Error: Cannot revert a completed procurement request.'); window.location.href='update.php?procurement_id=$procurement_id';</script>");
    }

    // Convert to integer only after validation
    $quantity = intval($quantity);

    // Prepare update query (SQL Injection Safe)
    $update_query = "UPDATE procurement 
                    SET quantity = ?, priority_level = ?, status = ? 
                    WHERE procurement_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("issi", $quantity, $priority_level, $status, $procurement_id);

    if ($stmt->execute()) {
        echo "<script>alert('Procurement request updated successfully!'); window.location.href='procurement.php';</script>";
    } else {
        die("Error updating procurement request: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Procurement Request</title>
    <link rel="stylesheet" href="../style.css"> 
</head>
<body>

    <div class="container">
        <h1>Update Procurement Request</h1>

        <form method="POST">
            <div class="form-group">
                <label>Item:</label>
                <input type="text" value="<?= htmlspecialchars($procurement['item_name'] ?? 'Unknown') ?>" readonly>
            </div>

            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="quantity" value="<?= isset($procurement['quantity']) ? htmlspecialchars($procurement['quantity']) : '' ?>" min="1" required>
            </div>

            <div class="form-group">
                <label>Priority Level:</label>
                <input type="text" name="priority_level" list="priority_levels" value="<?= htmlspecialchars($procurement['priority_level']) ?>" required>
                <datalist id="priority_levels">
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </datalist>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <input type="text" name="status" list="statuses" value="<?= htmlspecialchars($procurement['status']) ?>" required>
                <datalist id="statuses">
                    <option value="PENDING">Pending</option>
                    <option value="APPROVED">Approved</option>
                    <option value="COMPLETED">Completed</option>
                </datalist>
            </div>

            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
            <input type="hidden" name="procurement_id" value="<?= htmlspecialchars($procurement_id); ?>">
            <button type="submit" class="create">Update Procurement Request</button>
        </form>

        <a href="procurement.php"><button class="return">Return to Procurement List</button></a>
    </div>

</body>
</html>

<?php include '../footer.php'; ?>
