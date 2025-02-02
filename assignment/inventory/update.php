<?php
include 'security.php'; // Include security functions
include 'config.php';
include 'header.php';
include 'navigation.php'; 
include 'back_button.php';

// Establish a new mysqli connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Generate CSRF token
generateCsrfToken();

// Initialize variables
$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$item = null;
$invalidId = false;
$error_message = ''; // Initialize an error message variable


// Fetch the item details if an ID is provided
if ($id) {
    $stmt = $conn->prepare("SELECT * FROM inventory WHERE ITEM_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
    }
    $stmt->close();
}

// Fetch categories for the dropdown
$categories = [];
try {
    $result = $conn->query("SELECT category_id, name FROM category");
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
} catch (Exception $e) {
    die("Error fetching categories: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $item) {
    // CSRF token validation
    if (!validateCsrfToken($_POST['csrf_token'])) {
        die('CSRF token mismatch');
    }

    // Sanitizing inputs
    $name = validateInput($_POST['name']);
    $sku = validateInput($_POST['sku']);  // Handle SKU
    if (!preg_match('/^[A-Za-z0-9]+$/', $sku)) {
        die('SKU must be alphanumeric.');
    }
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    if ($price < 0 || !preg_match('/^\d+(\.\d{1,2})?$/', $price)) {
        die('Price must be a non-negative number with up to two decimal places.');
    }
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
    $description = validateInput($_POST['description']);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_SANITIZE_NUMBER_INT);

    $stmt = $conn->prepare("UPDATE inventory SET NAME = ?, SKU = ?, PRICE = ?, CATEGORY_ID = ?, DESCRIPTION = ?, QUANTITY = ?, STOCK = ? WHERE ITEM_ID = ?");
    $stmt->bind_param("ssdisiii", $name, $sku, $price, $category_id, $description, $quantity, $stock, $id);
    if ($stmt->execute()) {
        echo "<script>alert('Item successfully updated!'); window.location.href = 'inventory.php';</script>";
        exit;
    } else {
        $error_message = 'Failed to update item: ' . $stmt->error;
    }
    $stmt->close();
}

$conn->close();
?>

<div class="container">
    <?php if (!$item && $id !== null): ?>
        <p>Invalid Item ID. Please try again.</p>
    <?php endif; ?>

    <?php if ($item): ?>
    <!-- Form for updating the item -->
    <form action="update.php?id=<?= htmlspecialchars($id) ?>" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div class="form-group">
            <label>Item ID: <?= htmlspecialchars($item['ITEM_ID']) ?></label>
        </div>
        <div class="form-group">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($item['NAME']) ?>" required>
        </div>
        <div class="form-group">
            <label for="sku">SKU *</label>
            <input type="text" id="sku" name="sku" value="<?= htmlspecialchars($item['SKU']) ?>" required>
        </div>
        <div class="form-group">
            <label for="price">Price *</label>
            <input type="number" id="price" name="price" value="<?= htmlspecialchars($item['PRICE']) ?>" required>
        </div>
        <div class="form-group">
            <label for="category_id">Category ID *</label>
            <select id="category_id" name="category_id" required>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= htmlspecialchars($category['category_id']); ?>"
                        <?= isset($item) && $item['CATEGORY_ID'] == $category['category_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category['category_id']) . " - " . htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($item['DESCRIPTION']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="quantity">Quantity *</label>
            <input type="number" id="quantity" name="quantity" value="<?= htmlspecialchars($item['QUANTITY']) ?>" required>
        </div>
        <div class="form-group">
            <label for="stock">Stock Level *</label>
            <input type="number" id="stock" name="stock" value="<?= htmlspecialchars($item['STOCK']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary btn-update">Update Item</button>
    </form>
<?php endif; ?>

</div> 


<?php include 'footer.php'; ?>
