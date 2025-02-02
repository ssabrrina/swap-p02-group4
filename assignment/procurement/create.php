<?php
include '../config.php'; // Database connection
include '../security.php';

// ✅ Restrict Access: Only Admins and Procurement Officers
restrictAccess([1, 3], "../dashboard.php", "You do not have permission to access this page.");

// ✅ Check if user is logged in
if (!isset($_SESSION['session_userid'])) {
    die("<script>alert('Error: User not logged in. Please log in again.'); window.location.href='../login.php';</script>");
}

// ✅ Fetch logged-in user ID
$user_id = $_SESSION['session_userid'];

// ✅ Fetch items, departments, and vendors
$item_list = [];
$item_query = $conn->query("SELECT item_id, name FROM inventory");
while ($row = $item_query->fetch_assoc()) {
    $item_list[$row['item_id']] = $row['name'];
}

$department_list = [];
$dept_query = $conn->query("SELECT department_id, name FROM department");
while ($row = $dept_query->fetch_assoc()) {
    $department_list[$row['department_id']] = $row['name'];
}

// ✅ Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST")   

    {
    if (!isset($_POST['item_id'], $_POST['quantity'], $_POST['department_id'], $_POST['priority_level'], $_POST['csrf_token'])) {
        die("Error: Missing form data.");
    }

    // ✅ Validate CSRF Token
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die("<script>alert('Invalid CSRF token. Please try again.'); window.location.href='procurement.php';</script>");
    }

    // ✅ Ensure user is logged in before inserting
    if (!$user_id) {
        die("<script>alert('Error: User not logged in.'); window.location.href='../login.php';</script>");
    }

    // ✅ Sanitize Inputs
    $item_id = intval($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    $department_id = intval($_POST['department_id']);
    $priority_level = htmlspecialchars($_POST['priority_level'], ENT_QUOTES, 'UTF-8');
    $date_requested = date('Y-m-d H:i:s');
    $status = 'pending';

    // ✅ Insert into procurement table
    $stmt = $conn->prepare("INSERT INTO procurement (item_id, quantity, department_id, priority_level, status, date_requested, user_id) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisssi", $item_id, $quantity, $department_id, $priority_level, $status, $date_requested, $user_id);

    if ($stmt->execute()) {
        echo "<script>alert('Procurement request created successfully!'); window.location.href='procurement.php';</script>";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Procurement Request</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
    <link rel="stylesheet" href="style.css"> 
</head>
<body>
    <nav class="navigation">
        <ul>
            <li><a href="../dashboard.php">Dashboard</a></li>
            <li><a href="read.php">Vendor</a></li>
            <li><a href="read.php">Inventory Management</a></li>
            <li><a href="procurement.php">Procurement Request</a></li>
            <li><a href="read.php">Report</a></li>
            <li><a href="../logout.php">Log Out</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>Create Procurement Request</h1>

        <!-- ✅ Procurement Request Form -->
        <form method="POST">
            <div class="form-group">
                <label>Item:</label>
                <select name="item_id" required>
                    <option value="">Select an Item</option>
                    <?php foreach ($item_list as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="quantity" required>
            </div>

            <div class="form-group">
                <label>Department:</label>
                <select name="department_id" id="department_id" required>
                    <option value="">Select a Department</option>
                    <?php foreach ($department_list as $id => $name): ?>
                        <option value="<?= $id ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Priority Level:</label>
                <select name="priority_level">
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
            </div>

            <!-- ✅ CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
            <button type="submit" class="create">Create Procurement Request</button>
        </form>
        
        <a href="procurement.php"><button class="return">Return to Procurement List</button></a>
    </div>

</body>
</html>

