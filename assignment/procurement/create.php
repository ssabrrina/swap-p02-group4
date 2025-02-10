<?php
include '../config.php'; // Database connection
include '../security.php';
include '../header.php';
include '../navigation.php';

// Check if user is logged in
if (!isset($_SESSION['session_userid'])) {
    die("<script>alert('Error: User not logged in. Please log in again.'); window.location.href='../login.php';</script>");
}

// Fetch logged-in user ID
$user_id = $_SESSION['session_userid'];

// Fetch items, departments, and vendors
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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if required fields exist
    if (!isset($_POST['item_id'], $_POST['quantity'], $_POST['department_id'], $_POST['priority_level'], $_POST['csrf_token'])) {
        die("<script>alert('Error: Missing form data.'); window.location.href='create.php';</script>");
    }

    // Validate CSRF Token
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die("<script>alert('Invalid CSRF token. Please try again.'); window.location.href='create.php';</script>");
    }

    // Ensure user is logged in before inserting
    if (!$user_id) {
        die("<script>alert('Error: User not logged in.'); window.location.href='../login.php';</script>");
    }

    // Sanitize and Validate Inputs
    $item_id = trim($_POST['item_id']);
    $quantity = trim($_POST['quantity']);
    $department_id = trim($_POST['department_id']);
    $priority_level = trim($_POST['priority_level']);

    // Check for SQL Injection attempts
    if (preg_match("/[\"'%;()=]/", $item_id) || preg_match("/[\"'%;()=]/", $department_id) || preg_match("/[\"'%;()=]/", $priority_level)) {
        die("<script>alert('Error: Invalid input detected.'); window.location.href='create.php';</script>");
    }

    // Validate Numeric Fields
    if (!ctype_digit($quantity) || intval($quantity) <= 0) {
        die("<script>alert('Error: Quantity must be a positive whole number greater than 0.'); window.location.href='create.php';</script>");
    }
    
    // Convert to integer after validation
    $quantity = intval($quantity);

    // Insert into procurement table using prepared statements
    $stmt = $conn->prepare("INSERT INTO procurement (item_id, quantity, department_id, priority_level, status, date_requested, user_id) 
                            VALUES (?, ?, ?, ?, 'pending', NOW(), ?)");
    $stmt->bind_param("iiisi", $item_id, $quantity, $department_id, $priority_level, $user_id);

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
    <div class="container">
        <h1>Create Procurement Request</h1>

        <form method="POST">
            <div class="form-group">
                <label>Item:</label>
                <input type="text" name="item_id" list="items" required>
                <datalist id="items">
                    <?php foreach ($item_list as $id => $name): ?>
                        <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="form-group">
                <label>Quantity:</label>
                <input type="number" name="quantity" id="quantity" min="1" required>
            </div>

            <div class="form-group">
                <label>Department:</label>
                <input type="text" name="department_id" list="departments" required>
                <datalist id="departments">
                    <?php foreach ($department_list as $id => $name): ?>
                        <option value="<?= htmlspecialchars($name) ?>"><?= htmlspecialchars($name) ?></option>
                    <?php endforeach; ?>
                </datalist>
            </div>

            <div class="form-group">
                <label>Priority Level:</label>
                <input type="text" name="priority_level" list="priority_levels" required>
                <datalist id="priority_levels">
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </datalist>
            </div>

            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
            <button type="submit" class="create">Create Procurement Request</button>
        </form>

        <a href="procurement.php"><button class="return">Return to Procurement List</button></a>
    </div>
</body>
</html>

<?php include '../footer.php'; ?>
