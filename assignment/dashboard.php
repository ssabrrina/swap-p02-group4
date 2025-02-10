<?php
// ✅ Include database connection and security functions
include_once 'config.php';
include_once 'security.php';

restrictAccess([1, 2, 3], "../login.php", "Unidentified permission. Redirecting to login page...");

// ✅ Retrieve user details from session
$role_id = $_SESSION['session_role']; // Get user's role ID from session
$username = $_SESSION['session_username']; // Get username from session
?>

<?php include 'header.php'; ?> <!-- ✅ Include the header file -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"> <!-- ✅ Set character encoding -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- ✅ Ensure responsive design -->
    <title>Dashboard - AMC System</title> <!-- ✅ Set the page title -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="container">
        <!-- ✅ Profile Header Section -->
        <div class="profile-container">
            <h2 class="welcome-text">Welcome, <?= htmlspecialchars($username) ?>!</h2> <!-- Display username -->
            <a href="profile.php" class="profile-link"> <!-- ✅ Link to profile page -->
                <img src="imgs/profile.jpg" alt="Profile"> <!-- Profile icon -->
                <span>View Profile</span> <!-- Text next to profile icon -->
            </a>
        </div>

        <!-- ✅ Display Access Panel -->
        <h3>Your Access Panel</h3> <!-- Section heading -->
        <div class="form-group">
            <?php if ($role_id == '1'): ?> <!-- ✅ If user is Admin (Role ID 1) -->
                <button onclick="window.location.href='vendors/vendor.php'">Manage Vendors</button> <!-- Button to manage vendors -->
                <button onclick="window.location.href='procurement/procurement.php'">Procurement Records</button> <!-- Button for procurement -->
                <button onclick="window.location.href='inventory/inventory.php'">Inventory</button> <!-- Button for inventory -->
                <button onclick="window.location.href='report/report.php'">Reports</button> <!-- Button for user management -->
                <button onclick="window.location.href='order/read_orders.php'">Purchase Orders</button> <!-- Create purchase requests -->
                <?php endif; ?>

            <?php if ($role_id == '2'): ?> <!-- ✅ If user is Department Head (Role ID 2) -->
                <button onclick="window.location.href='procurement/create.php'">Create Procurement Records</button> <!-- Button for procurement -->
            <?php endif; ?>

            <?php if ($role_id == '3'): ?> <!-- ✅ If user is Procurement officer (Role ID 3) -->
                <button onclick="window.location.href='vendors/vendor.php'">Manage Vendors</button> <!-- Button to manage vendors -->
                <button onclick="window.location.href='procurement/procurement.php'">Procurement Records</button> <!-- View only procurement records -->
                <button onclick="window.location.href='inventory/inventory.php'">Inventory</button> <!-- Create purchase requests -->
            <?php endif; ?>
        </div>
    </div>

</body>
</html>

<?php include 'footer.php'; ?> <!-- ✅ Include the footer file -->
