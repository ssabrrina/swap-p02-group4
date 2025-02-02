<?php
include '../config.php'; // Database connection
include '../security.php';

// ✅ Restrict Access: Only Role ID 1 (Admin) & Role ID 3 (Procurement Officer) can access
restrictAccess([1, 3], "../dashboard.php", "You do not have permission to access this page.");

// ✅ Check if `procurement_id` is provided in URL
if (!isset($_GET['procurement_id'])) {
    die("Error: Procurement ID is missing.");
}

$procurement_id = intval($_GET['procurement_id']); // Convert to integer for security

// ✅ Fetch procurement details
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


// ✅ Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ✅ Validate CSRF Token
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die("<script>alert('Invalid CSRF token. Please try again.'); window.location.href='procurement.php';</script>");
    }

    // ✅ Ensure procurement_id is set
    if (!isset($_POST['procurement_id'])) {
        die("Error: Procurement ID is missing.");
    }

        // ✅ Sanitize Inputs
        $procurement_id = intval($_POST['procurement_id']);
        $quantity = intval($_POST['quantity']);
        $priority_level = htmlspecialchars($_POST['priority_level'], ENT_QUOTES, 'UTF-8');
        $status = htmlspecialchars($_POST['status'], ENT_QUOTES, 'UTF-8');

        // ✅ Debugging: Check if values are received
        if (empty($quantity) || empty($priority_level) || empty($status) ) {
            die("Error: Missing required fields.");
        }

        // ✅ Prepare update query
        $update_query = "UPDATE procurement 
                        SET quantity = ?, priority_level = ?, status = ? 
                        WHERE procurement_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("issi", $quantity, $priority_level, $status, $procurement_id);

        if ($stmt->execute()) {
            // ✅ Redirect after successful update
            header("Location: procurement.php?update_success=1");
            exit();
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
                <input type="number" name="quantity" value="<?= isset($procurement['quantity']) ? htmlspecialchars($procurement['quantity']) : '' ?>" required>
            </div>

            <div class="form-group">
                <label>Priority Level:</label>
                <select name="priority_level">
                    <option value="Low" <?= isset($procurement['priority_level']) && $procurement['priority_level'] == 'Low' ? 'selected' : '' ?>>Low</option>
                    <option value="Medium" <?= isset($procurement['priority_level']) && $procurement['priority_level'] == 'Medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="High" <?= isset($procurement['priority_level']) && $procurement['priority_level'] == 'High' ? 'selected' : '' ?>>High</option>
                </select>
            </div>

            <div class="form-group">
                <label>Status:</label>
                <select name="status">
                    <option value="pending" <?= isset($procurement['status']) && $procurement['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="approved" <?= isset($procurement['status']) && $procurement['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
                    <option value="completed" <?= isset($procurement['status']) && $procurement['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                </select>
            </div>

            
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
            <input type="hidden" name="procurement_id" value="<?= htmlspecialchars($procurement_id); ?>">
            <button type="submit" class="create">Update Procurement Request</button>
        </form>

        <a href="procurement.php"><button class="return">Return to Procurement List</button></a>
    </div>

</body>
</html>
