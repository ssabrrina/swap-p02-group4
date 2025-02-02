<?php
include '../config.php';
include '../security.php';


// Restrict access to Admins only
restrictAccess([1], "../dashboard.php", "You do not have permission to delete procurement requests.");

// Establish a new mysqli connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'id' is present and is a valid integer
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare a delete statement to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM inventory WHERE ITEM_ID = ?");
    $stmt->bind_param("i", $id);

    // Execute the statement with parameter binding
    if ($stmt->execute()) {
        // If the deletion is successful, redirect to the inventory page with a success message
        echo "<script>alert('Inventory item deleted successfully!'); window.location.href='inventory.php';</script>";
        exit();
    } else {
        // If the deletion fails, redirect back to the inventory page with an error message
        header("Location: inventory.php?error=deletionfailed");
        exit();
    }
    $stmt->close();
} else {
    // If the ID is not set or not valid, redirect back with an error message
    header("Location: inventory.php?error=invalidid");
    exit();
}
?>
