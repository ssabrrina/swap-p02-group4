<?php
include '../security.php'; // Ensure security functions are included
include '../config.php';
include '../header.php'; 
include '../navigation.php'; 
include '../back_button.php';

restrictAccess([1, 3], "../dashboard.php", "You do not have permission to create inventory.");

// Establish a new mysqli connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Generate CSRF token
generateCsrfToken();

// Fetch categories
$categories = [];
try {
    $result = $conn->query("SELECT category_id, name FROM category");
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
} catch (Exception $e) {
    die("Error fetching categories: " . $e->getMessage());
}

// Initialize variables
$creationSuccess = false;
$error_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (!validateCsrfToken($_POST['csrf_token'])) {
        $error_message = 'CSRF token mismatch.';
    }

    // Sanitize and validate inputs
    $name = validateInput($_POST['name']);
    $sku = validateInput($_POST['sku']);
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
    $description = validateInput($_POST['description']);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_SANITIZE_NUMBER_INT);

    // Input validation
    if (!preg_match('/^[A-Za-z0-9 ]+$/', $name)) {
        $error_message = "Invalid Name: Only letters, numbers, and spaces are allowed.";
    } elseif (!preg_match('/^[A-Za-z0-9]+$/', $sku)) {
        $error_message = "Invalid SKU: Must be alphanumeric.";
    } elseif ($price < 0 || !preg_match('/^\d+(\.\d{1,2})?$/', $price)) {
        $error_message = "Invalid Price: Must be a non-negative number with up to two decimal places.";
    }

    if (empty($error_message)) {
        // Prepared statement to insert data
        $stmt = $conn->prepare("INSERT INTO inventory (NAME, SKU, PRICE, CATEGORY_ID, DESCRIPTION, QUANTITY, STOCK) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdisii", $name, $sku, $price, $category_id, $description, $quantity, $stock);

        try {
            if ($stmt->execute()) {
                $creationSuccess = true; 
            }
        } catch (Exception $e) {
            $error_message = "Error: " . $e->getMessage();
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Add Inventory</h2>

        <!-- Show error message if validation fails -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <form action="create.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" required pattern="[A-Za-z0-9 ]+" title="Only letters, numbers, and spaces are allowed.">
            </div>

            <div class="form-group">
                <label for="sku">SKU *</label>
                <input type="text" id="sku" name="sku" required pattern="[A-Za-z0-9]+" title="SKU must be alphanumeric.">
            </div>

            <div class="form-group">
                <label for="price">Price *</label>
                <input type="number" id="price" name="price" required step="0.01" min="0" title="Price must be a non-negative number with up to two decimal places.">
            </div>

            <div class="form-group">
                <label for="category_id">Category ID *</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['category_id']); ?>">
                            <?= htmlspecialchars($category['category_id']) . " - " . htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description *</label>
                <textarea id="description" name="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity *</label>
                <input type="number" id="quantity" name="quantity" required>
            </div>

            <div class="form-group">
                <label for="stock">Stock Level *</label>
                <input type="number" id="stock" name="stock" required>
            </div>

            <button type="submit" class="btn-create">Create Item</button>
        </form>

        <!-- Redirect if successfully created -->
        <?php if ($creationSuccess): ?>
            <script>
                alert('ITEM SUCCESSFULLY CREATED!');
                window.location.href = 'inventory.php';
            </script>
        <?php endif; ?>
    </div>
</body>
</html>

<?php include '../footer.php'; ?>
