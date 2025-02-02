<?php
// ✅ Include database connection
include 'config.php';

// ✅ Include security functions
include 'security.php';

// ✅ Restrict access to authorized roles only
restrictAccess([1, 2, 3], "dashboard.php", "You do not have access to this page. Redirecting to Dashboard...");

// ✅ Retrieve user ID from session
$user_id = $_SESSION['session_userid'];

$error = ""; // Variable to store errors
$procurement_records = []; // Array to store procurement records

// ✅ Generate a CSRF token for the form
$csrf_token = generateCsrfToken();

// ✅ Fetch associated item names from the inventory table
$inventory = [];
$items = $conn->query("SELECT item_id, name FROM inventory");
while ($row = $items->fetch_assoc()) {
    $inventory[$row['item_id']] = $row['name'];
}

// ✅ Fetch associated department names from the department table
$departments = [];
$department = $conn->query("SELECT department_id, name FROM department");
while ($row = $department->fetch_assoc()) {
    $departments[$row['department_id']] = $row['name'];
}

// ✅ Fetch associated vendor names from the vendor table
$vendors = [];
$vendor = $conn->query("SELECT vendor_id, name FROM vendor");
while ($row = $vendor->fetch_assoc()) {
    $vendors[$row['vendor_id']] = $row['name'];
}

// ✅ Fetch user details from the database
try {
    $stmt = $conn->prepare("SELECT USERNAME, EMAIL, ROLE_ID FROM user WHERE USER_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) { // If user details are not found
        $error = "User data not found.";
    }

    // ✅ Fetch procurement records linked to the user
    $stmt = $conn->prepare("SELECT PROCUREMENT_ID, ITEM_ID, QUANTITY, DEPARTMENT_ID, PRIORITY_LEVEL, STATUS, DATE_REQUESTED 
                            FROM procurement WHERE USER_ID = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $procurement_records = $stmt->get_result();
    $stmt->close();

} catch (Exception $e) {
    $error = "Error fetching profile: " . $e->getMessage();
}
?>

<?php include 'header.php'; ?> <!-- ✅ Include the header file -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- ✅ Set character encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- ✅ Ensure responsive design -->
    <title>Profile - AMC System</title> <!-- ✅ Set the page title -->
    <link rel="stylesheet" href="style.css"> <!-- ✅ Link external CSS for styling -->
</head>
<body>
    <div class="container">
        <!-- ✅ Profile Header Section -->
        <div class="profile-container">
            <h2>Your Profile</h2> <!-- Profile title -->
            <a href="logout.php" class="profile-link"> <!-- ✅ Logout link -->
                <img src="imgs/leave.jpg" alt="Logout"> <!-- Logout icon -->
                <span>Logout</span> <!-- Logout text -->
            </a>
        </div>

        <?php if (!empty($error)): ?> <!-- ✅ Display error message if any -->
            <div class="message error"><?= htmlspecialchars($error) ?></div>
        <?php else: ?> <!-- ✅ If no error, display user details -->
            <p><strong>Username:</strong> <?= htmlspecialchars($user['USERNAME']) ?></p> <!-- Display username -->
            <p><strong>Email:</strong> <?= htmlspecialchars($user['EMAIL']) ?></p> <!-- Display email -->
            <p><strong>Role:</strong> 
                <?php 
                    if ($user['ROLE_ID'] == 1) { // ✅ If user is an Admin
                        echo "Admin";
                    } elseif ($user['ROLE_ID'] == 2) { // ✅ If user is a Department Head
                        echo "Department Head";
                    } elseif ($user['ROLE_ID'] == 3) { // ✅ If user is a Procurement Officer
                        echo "Procurement Officer";
                    }
                ?>
            </p>

            <!-- ✅ CSRF-Protected Change Password Form -->
            <form action="forgot_pw.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                <input type="hidden" name="username" value="<?= htmlspecialchars($user['USERNAME']) ?>"> <!-- ✅ Pass username -->
                <button type="submit">Change Password</button> <!-- ✅ Button to reset password -->
            </form>

            <!-- ✅ Display Procurement Records -->
            <h3>Your Procurement Records</h3>
            <?php if ($procurement_records->num_rows > 0): ?> <!-- ✅ Check if user has procurement records -->
                <table>
                    <tr>
                        <th>Procurement ID</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Department</th>
                        <th>Priority Level</th>
                        <th>Status</th>
                        <th>Date Requested</th>
                    </tr>
                    <?php while ($row = $procurement_records->fetch_assoc()): ?> <!-- ✅ Loop through procurement records -->
                        <tr>
                            <td><?= htmlspecialchars($row['PROCUREMENT_ID']) ?></td> <!-- Procurement ID -->
                            <td><?= htmlspecialchars($inventory[$row['ITEM_ID']] ?? 'Unknown') ?></td> <!-- Item name -->
                            <td><?= htmlspecialchars($row['QUANTITY']) ?></td> <!-- Quantity -->
                            <td><?= htmlspecialchars($departments[$row['DEPARTMENT_ID']] ?? 'Unknown') ?></td> <!-- Department name -->
                            <td><?= htmlspecialchars($row['PRIORITY_LEVEL']) ?></td> <!-- Priority level -->
                            <td><?= htmlspecialchars($row['STATUS']) ?></td> <!-- Status -->
                            <td><?= htmlspecialchars($row['DATE_REQUESTED']) ?></td> <!-- Date requested -->
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?> <!-- ✅ If no procurement records found -->
                <p>No procurement records found.</p>
            <?php endif; ?>

        <?php endif; ?>
        
        <a href="dashboard.php"><button>Back to Dashboard</button></a> <!-- ✅ Button to go back to Dashboard -->
    </div>
</body>
</html>

<?php include 'footer.php'; ?> <!-- ✅ Include the footer file -->
