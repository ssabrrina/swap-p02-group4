<?php
include '../config.php'; 
include '../security.php'; 

// only Role ID 1 (Admin) & Role ID 3 (Procurement Officer) can access
restrictAccess([1, 3], "../dashboard.php", "You do not have access to this page. Redirecting to Dashboard...");

// generate a CSRF token for the form
$csrf_token = generateCsrfToken();

// fetch service types into an associative array
$service_types = [];
$services = $conn->query("SELECT service_id, name FROM service_type");
while ($row = $services->fetch_assoc()) {
    $service_types[$row['service_id']] = $row['name'];
}

// fetch payment terms into an associative array
$payment_terms = [];
$payments = $conn->query("SELECT payment_id, name FROM payment_terms");
while ($row = $payments->fetch_assoc()) {
    $payment_terms[$row['payment_id']] = $row['name'];
}

// handle vendor deletion (Only Role ID 1 can delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    // validate CSRF token before processing deletion
    if (!validateCsrfToken($_POST['csrf_token'])) {
        $error = "CSRF validation failed. Please try again";
    } elseif ($_SESSION['session_role'] != 1) { 
        // prevent non-admins from attempting deletion
        $error = "You do not have permission to delete vendors.";
    } else {
        $delete_id = validateInput($_POST['delete_id']); // sanitize input

        try {
            $stmt = $conn->prepare("SELECT name FROM vendor WHERE vendor_id = ?");
            $stmt->bind_param("i", $delete_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $vendor = $result->fetch_assoc();

            if ($vendor) {
                $stmt = $conn->prepare("DELETE FROM vendor WHERE vendor_id = ?");
                $stmt->bind_param("i", $delete_id);
                if ($stmt->execute()) {
                    $success = "Vendor '{$vendor['name']}' deleted successfully!";
                } else {
                    $error = "Error deleting vendor: " . $stmt->error;
                }
            } else {
                $error = "Vendor not found.";
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log($e->getMessage());
            $error = "An error occurred while attempting to delete the vendor.";
        }
    }
}

// fetch all vendors from the database
try {
    $result = $conn->query("SELECT * FROM vendor");
} catch (Exception $e) {
    die("An error occurred while fetching vendor records.");
}
?>

<?php include '../header.php'; ?>
<?php include '../navigation.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Vendors</title>
    <link rel="stylesheet" href="../style.css">
    <script src="../functions.js"></script>
</head>
<body>
    <div class="container">
        <h1>Vendors List</h1>
        <div class="button-container">
            <button onclick="window.location.href='create.php'" class="add-vendor-btn">Add New Vendor</button>
        </div>

        <?php if (!empty($error)): ?>
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php elseif (!empty($success)): ?>
            <div class="message success"><?= htmlspecialchars($success) ?></div>
            <script>
                showSuccessMessageAndRedirect("<?= addslashes($success) ?>", "vendor.php");
            </script>
        <?php endif; ?>
        
        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Vendor ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Service Type</th>
                    <th>Payment Term</th>
                    <th>Options</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['VENDOR_ID']) ?></td>
                        <td><?= htmlspecialchars($row['NAME']) ?></td>
                        <td><?= htmlspecialchars($row['EMAIL']) ?></td>
                        <td><?= htmlspecialchars($row['TELEPHONE_NUMBER']) ?></td>
                        <td>
                            <?= htmlspecialchars($service_types[$row['SERVICE_ID']] ?? 'Unknown') ?>
                        </td>
                        <td>
                            <?= htmlspecialchars($payment_terms[$row['PAYMENT_ID']] ?? 'Unknown') ?>
                        </td>
                        <td>
                            <div class="button-group">
                                <button onclick="window.location.href='update.php?vendor_id=<?= $row['VENDOR_ID'] ?>'">Update</button>

                                <?php if ($_SESSION['session_role'] == 1): ?> <!-- only admin sees delete button -->
                                    <form method="post" class="inline-form">
                                        <input type="hidden" name="delete_id" value="<?= $row['VENDOR_ID'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <button type="submit" class="delete" onclick="return confirmDelete(<?= $row['VENDOR_ID'] ?>)">Delete</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No vendors found.</p>
        <?php endif; ?>
        <p><a href="../dashboard.php">Return to Dashboard</a></p>
    </div>
</body>
</html>
<?php include '../footer.php'; ?>
