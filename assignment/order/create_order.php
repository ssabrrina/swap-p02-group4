<?php
include '../config.php';
include '../security.php';
include '../header.php';
include '../navigation.php';

// ✅ Restrict access to Procurement Officers only
restrictAccess([1, 3], "dashboard.php", "You do not have permission to create purchase orders.");

// ✅ Fetch only "approved" procurement requests
$procurement_list = [];
$procurement_query = $conn->query("SELECT p.procurement_id, i.name AS item_name
                                FROM procurement p
                                JOIN inventory i ON p.item_id = i.item_id
                                WHERE p.status = 'approved'");
while ($row = $procurement_query->fetch_assoc()) {
    $procurement_list[$row['procurement_id']] = "[ID: " . $row['procurement_id'] . "] " . $row['item_name'];
}

// ✅ Fetch vendors
$vendor_list = [];
$vendor_query = $conn->query("SELECT vendor_id, name FROM vendor");
while ($row = $vendor_query->fetch_assoc()) {
    $vendor_list[$row['vendor_id']] = $row['name'];
}

// ✅ Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['procurement_id'], $_POST['vendor_id'], $_POST['csrf_token'])) {
        die("Error: Missing form data.");
    }

    // ✅ Validate CSRF Token
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die("<script>alert('Invalid CSRF token. Please try again.'); window.location.href='read_orders.php';</script>");
    }

    // ✅ Sanitize Inputs
    $procurement_id = intval($_POST['procurement_id']);
    $vendor_id = intval($_POST['vendor_id']);
    $status = 'pending'; // Default status

    // ✅ Insert Purchase Order
    $stmt = $conn->prepare("INSERT INTO `orders` (procurement_id, vendor_id, status) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $procurement_id, $vendor_id, $status);

    if ($stmt->execute()) {
        echo "<script>alert('Order created successfully!'); window.location.href='read_orders.php';</script>";
    } else {
        die("Error: " . $stmt->error);
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Order</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Create Purchase Order</h1>
        <form method="POST">
            <div class="form-group">
                <label>Procurement Request:</label>
                <select name="procurement_id" required>
                    <option value="">Select a Procurement Request</option>
                    <?php foreach ($procurement_list as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Vendor:</label>
                <select name="vendor_id" required>
                    <option value="">Select a Vendor</option>
                    <?php foreach ($vendor_list as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
            <button type="submit" class="create">Create Order</button>
        </form>
    </div>
</body>
</html>

<?php include '../footer.php'; ?>