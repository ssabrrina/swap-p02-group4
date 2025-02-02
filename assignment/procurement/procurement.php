<?php
include '../config.php'; // Database connection
include '../security.php';
include '../header.php';
include '../navigation.php';

// ✅ Restrict Access: Only Role ID 1 (Admin) & Role ID 3 (Procurement Officer) can access
restrictAccess([1, 3], "dashboard.php", "You do not have permission to access this page.");

// ✅ Fetch all procurement requests with JOIN to get actual names and the user who created the request
$query = "SELECT 
            p.procurement_id, 
            i.name AS item_name, 
            p.quantity, 
            d.name AS department_name, 
            p.priority_level, 
            p.status, 
            p.date_requested, 
            u.username AS requested_by
        FROM procurement p
        LEFT JOIN inventory i ON p.item_id = i.item_id
        LEFT JOIN department d ON p.department_id = d.department_id
        LEFT JOIN user u ON p.user_id = u.user_id";  // ✅ Join users table to get requester name

$result = $conn->query($query);

if (!$result) {
    die("Error fetching procurement requests: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Procurement Requests</title>
    <link rel="stylesheet" href="style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        
    </style>
</head>
<body>
    <h1>Procurement Requests</h1>
    <!-- ✅ Create Procurement Request Button -->
    <a href="create.php"><button class="button.create">+ Create Procurement Request</button></a>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Item</th>
                <th>Quantity</th>
                <th>Department</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Date Requested</th>
                <th>Requested By</th> 
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['procurement_id'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($row['item_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($row['quantity'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($row['department_name'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($row['priority_level'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($row['status'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($row['date_requested'] ?? 'Unknown') ?></td>
                    <td><?= htmlspecialchars($row['requested_by'] ?? 'Unknown') ?></td>
                    <td>
                        <div class="button-group"> <!-- Flexbox container -->
                            <button onclick="window.location.href='update.php?procurement_id=<?= $row['procurement_id'] ?>'">Update</button>
                            
                            <?php if ($_SESSION['session_role'] == 1): ?> <!-- ✅ Only Role ID 1 sees delete button -->
                                    <!-- ✅ Secure Form for Deletion with CSRF Protection -->
                                    <form method="post" action="delete.php" style="display:inline;">
                                        <input type="hidden" name="procurement_id" value="<?= $row['procurement_id'] ?>">
                                        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
                                        <button type="submit" class="delete" onclick="return confirm('Are you sure you want to delete this procurement request?');">Delete</button>
                                    </form>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No procurement requests found.</p>
    <?php endif; ?>

    <a href="../dashboard.php"><button class="return">Return to Dashboard</button></a>
</body>
</html>
<?php include '../footer.php'; ?>