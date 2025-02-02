<?php
include '../config.php';
include '../security.php';
include '../header.php';
include '../navigation.php';

// ✅ Restrict Access to Admins & Procurement Officers
restrictAccess([1, 3], "../dashboard.php", "You do not have permission to view orders.");

// ✅ Ensure a CSRF token exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate CSRF token if not set
}

// ✅ Fetch orders with Procurement ID & Status
$query = "SELECT o.order_id, p.procurement_id, i.name AS item_name, v.name AS vendor_name, o.status
        FROM `orders` o
        JOIN procurement p ON o.procurement_id = p.procurement_id
        JOIN inventory i ON p.item_id = i.item_id
        JOIN vendor v ON o.vendor_id = v.vendor_id
        ORDER BY o.order_id DESC"; // Ensures newest orders appear first

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Purchase Orders</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- ✅ Include jQuery -->
    <script>
        $(document).ready(function () {
            $(".status-dropdown").change(function () {
                var order_id = $(this).data("order-id"); // Get order ID
                var new_status = $(this).val(); // Get selected status

                // ✅ Fetch CSRF token from the hidden input field
                var csrf_token = $("#csrf_token").val(); 
        
                if (!csrf_token) {
                    alert("Error fetching CSRF token.");
                    return;
                }

                $.ajax({
                    url: "update_order_status.php", // ✅ Call AJAX handler
                    type: "POST",
                    data: { order_id: order_id, status: new_status, csrf_token: csrf_token },
                    success: function (response) {
                        alert(response); // ✅ Show success message
                        location.reload(); // ✅ Refresh the page to reflect the new status
                    },
                    error: function () {
                        alert("Error updating status.");
                    }
                });
            });
        });

    </script>


</head>
<body>
    <div class="container">
        <h1>Manage Purchase Orders</h1>
        <a href="create_order.php"><button class="create">+ Create Purchase Orders</button></a>
        
        <input type="hidden" id="csrf_token" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <table>
            <tr>
                <th>Order ID</th>
                <th>Procurement ID</th>
                <th>Procurement Request</th>
                <th>Vendor</th>
                <th>Status</th>
                <th>Delete</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['order_id']) ?></td>
                    <td><?= htmlspecialchars($row['procurement_id']) ?></td>
                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                    <td><?= htmlspecialchars($row['vendor_name']) ?></td>
                    <td>
                        <select class="status-dropdown" data-order-id="<?= $row['order_id'] ?>">
                            <option value="Pending" <?= $row['status'] == 'PENDING' ? 'selected' : '' ?>>Pending</option>
                            <option value="Approved" <?= $row['status'] == 'APPROVED' ? 'selected' : '' ?>>Approved</option>
                            <option value="Completed" <?= $row['status'] == 'COMPLETED' ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </td>
                    <td>
                        <div class="button-group"> 
                            <?php if ($_SESSION['session_role'] == 1 || $_SESSION['session_role'] == 3): ?> 
                                <?php if ($row['status'] == 'PENDING'): ?> <!-- ✅ Prevent Deleting Approved or Completed Orders -->
                                    <form action="delete.php" method="post" class="inline-form">
                                        <input type="hidden" name="delete_id" value="<?= htmlspecialchars($row['order_id']) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"> 
                                        <button type="submit" class="delete">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <button class="delete" disabled title="Cannot delete Approved/Completed orders">Locked</button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>

<?php include '../footer.php'; ?>
