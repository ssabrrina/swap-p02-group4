<?php
include '../security.php'; // Ensure this file contains necessary security functions
include '../config.php';
include '../header.php'; 
include '../navigation.php'; 
include '../back_button.php';

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

$creationSuccess = false;  // Flag to track creation success

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die('CSRF token mismatch');
    }

    // Sanitize and validate all inputs
    $name = validateInput($_POST['name']);
    $sku = validateInput($_POST['sku']);
    $price = validateInput($_POST['price']);
    $category_id = validateInput($_POST['category_id']);
    $description = validateInput($_POST['description']);
    $quantity = validateInput($_POST['quantity']);
    $stock = validateInput($_POST['stock']);

    $sql = "INSERT INTO inventory (NAME, SKU, PRICE, CATEGORY_ID, DESCRIPTION, QUANTITY, STOCK) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    try {
        if ($stmt->execute([$name, $sku, $price, $category_id, $description, $quantity, $stock])) {
            $creationSuccess = true; // Update success flag
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
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
    <h2>Add Inventory </h2>
        <form action="create.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="form-group">
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" required>
            </div>
                <div class="form-group">
                <label for="sku">SKU *</label>
                <input type="text" id="sku" name="sku" required pattern="[A-Za-z0-9]+" title="SKU should be alphanumeric.">
                </div>
            <div class="form-group">
                <label for="price">Price *</label>
                <input type="number" id="price" name="price" required step="0.01" min="0" title="Price must be a non-negative number.">
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
        <?php if ($creationSuccess): ?>
        <script>alert('ITEM SUCCESSFULLY CREATED!');
            window.location.href = 'inventory.php';
        </script>
        <?php endif; ?>
    </div>
</body>
</html>

<?php include '../footer.php'; ?>