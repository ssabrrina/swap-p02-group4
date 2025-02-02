<?php
include '../config.php'; // Database connection
include '../security.php';

// ✅ Restrict access to Admins only
restrictAccess([1], "../dashboard.php", "You do not have permission to delete procurement requests.");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ✅ Check if CSRF token exists before validating
    if (!isset($_POST['csrf_token']) || empty($_POST['csrf_token'])) {
        die("<script>alert('CSRF token is missing. Please try again.'); window.location.href='procurement.php';</script>");
    }

    // ✅ Validate CSRF Token
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die("<script>alert('Invalid CSRF token. Please try again.'); window.location.href='procurement.php';</script>");
    }

    // ✅ Check if procurement_id exists in the request
    if (!isset($_POST['procurement_id'])) {
        die("<script>alert('Error: Procurement ID is missing.'); window.location.href='procurement.php';</script>");
    }

    $procurement_id = intval($_POST['procurement_id']);

    // ✅ Prevent deletion of approved/completed requests
    $check_stmt = $conn->prepare("SELECT status FROM procurement WHERE procurement_id = ?");
    $check_stmt->bind_param("i", $procurement_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row && in_array($row['status'], ['approved', 'completed'])) {
        die("<script>alert('Cannot delete an approved or completed request.'); window.location.href='procurement.php';</script>");
    }

    // ✅ Proceed with deletion
    $stmt = $conn->prepare("DELETE FROM procurement WHERE procurement_id = ?");
    $stmt->bind_param("i", $procurement_id);

    if ($stmt->execute()) {
        echo "<script>alert('Procurement request deleted successfully!'); window.location.href='procurement.php';</script>";
    } else {
        die("Error deleting procurement request: " . $stmt->error);
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Procurement Request</title>
</head>
<body>
    <h1>Delete Procurement Request</h1>

    <form method="POST">
        <label>Procurement ID:</label> 
        <input type="number" name="procurement_id" required><br>
        <!-- ✅ CSRF Token-->
        <input type="hidden" name="csrf_token" value="<?= generateCsrfToken(); ?>">
        <button type="submit">Delete Request</button>
    </form>

    <p><a href="procurement.php">Back to Procurement List</a></p>
</body>
</html>
