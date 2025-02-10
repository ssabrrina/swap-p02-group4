<?php
include '../security.php'; // Ensure this file contains necessary security functions
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

// Generate or check for CSRF token if necessary
if (empty($_SESSION['csrf_token'])) {
    generateCsrfToken();
}

// Fetch inventory data with error handling
try {
    $result = $conn->query("SELECT * FROM inventory");
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
} catch (Exception $e) {
    die("Error fetching inventory: " . $e->getMessage());
}

echo '<div style="width: 100%; max-width: 1000px; margin: 50px auto; padding: 5px;">
<h2>Inventory Management</h2>
<div class="add-inventory-button" style="margin-bottom: 20px; text-align: center;">
    <button onclick="window.location.href=\'create.php\';" class="btn btn-primary">+ Add Inventory</button>
</div>
<table class="inventory-table">
    <thead>
        <tr>
            <th>Item ID</th>
            <th>Product Name</th>
            <th>SKU</th>
            <th>Price</th>
            <th>Category ID</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Stock Level</th>
            <th>Edit</th>
            <th>Delete</th>
        </tr>
    </thead>
    <tbody>';
foreach ($items as $item) {
    echo '<tr>
        <td>' . htmlspecialchars($item['ITEM_ID']) . '</td>
        <td>' . htmlspecialchars($item['NAME']) . '</td>
        <td>' . htmlspecialchars($item['SKU']) . '</td>
        <td>' . htmlspecialchars($item['PRICE']) . '</td>
        <td>' . htmlspecialchars($item['CATEGORY_ID']) . '</td>
        <td>' . htmlspecialchars($item['DESCRIPTION']) . '</td>
        <td>' . htmlspecialchars($item['QUANTITY']) . '</td>
        <td>' . htmlspecialchars($item['STOCK']) . '</td>
        <td>
            <button onclick="window.location.href=\'update.php?id=' . $item['ITEM_ID'] . '\';" class="btn btn-edit">Edit</button>
        </td>
        <td>
            <a href="delete.php?id=' . $item['ITEM_ID'] . '&csrf_token=' . $_SESSION['csrf_token'] . '" onclick="return confirm(\'Are you sure you want to delete this?\')">Delete</a>
        </td>
    </tr>';
}
echo '</tbody>
</table>
</div>';
?>

<?php
include '../footer.php';
?>
