<?php
$con = mysqli_connect("localhost","admin","admin","swap_assignment_db"); //connect to database
if (!$con){
 die('Could not connect: ' . mysqli_connect_errno()); //return error is connect fail
}

if (!isset($_GET["report_id"])) {
    die("Error: Report ID is missing.");
}

// Catch the submitted value 
$edit_reportid = htmlspecialchars($_GET["report_id"]);

// Prepare the statement

$stmt= $con->prepare("SELECT * from report WHERE REPORT_ID=?");

// Binding can be done either BEFORE the execute (ie bind_param) or AFTER the execute (ie bind_result)
// Bind the parameters - nothing to bind since select *
$stmt->bind_param('i', $edit_reportid); 

// Execute the statement
$stmt->execute();

// Obtain the result set
$result = $stmt->get_result();

$row = $result->fetch_assoc();
?>

<html>
<head>
    <title>Edit Procurement Activites Report</title>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        background-color: #f4f4f4;
    }

    header {
        background: url('imgs/header-bg.png'); /* Path to your header background image */
        background-size: cover;
        color: white;
        text-align: center;
        padding: 80px 0;
        }

    .header-content {
        background-color: black; /* Black background for the text box */
        display: inline-block;
        padding: 1px 20px;
    }

    .container {
        width: 100%;
        max-width: 800px;
        margin: 50px auto;
        padding: 20px;
    }

    .row {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }

    label {
        width: 150px;
        margin-right: 10px;
    }

    .row input, .row select {
        flex: 1;
        padding: 10px;
        border: 1px solid black;
        border-radius: 4px;
        font-size: 14px;
    }

    .row input:focus, .row select:focus {
        border-color: #6200ea;
        outline: none;
    }

    button {
        padding: 12px 20px;
        border: 1px solid black;
        border-radius: 4px;
        background-color: #6200ea;
        color: white;
        font-size: 16px;
        cursor: pointer;
        margin-left: auto;
    }

    button:hover {
        background-color: #3700b3;
    }

    .buttons {
        text-align: right;
    }

    nav {
        background-color: #383737; /* Dark background for the navigation bar */
        overflow: hidden;
    }

    ul {
        list-style-type: none; /* Removes bullet points from the list */
        padding: 0;
        margin: 0;
        display: flex; /* Layout the list items in a row */
        justify-content: left; /* Center the navigation links horizontally */
    }

    li {
        float: left;
        }

    li a {
        display: block; /* Make the links fill the entire list item */
        color: white; /* White text color */
        text-align: left;
        padding: 20px 24px; /* Padding inside each link */
        text-decoration: none; /* Remove underline from links */
        transition: background-color 0.3s; /* Smooth transition for hover effect */
    }

    li a:hover {
        background-color: #ddd; /* Light background on hover */
        color: black; /* Change text color on hover */
    }

    footer {
        background-color: white; /* White background */
        text-align: center;
        padding: 35px;
        }
</style>
</head>
<header>
    <div class="header-content">
        <h4>AMC Internal Procurement Management System</h4>
    </div>
</header>
<nav>
    <ul>
    <li><a href="../dashboard.php">Dashboard</a></li>
        <li><a href="../vendors/vendor.php">Vendor</a></li>
        <li><a href="../inventory/inventory.php">Inventory Management</a></li>
        <li><a href="../procurement/procurement.php">Procurement Request</a></li>
        <li><a href="../report/report.php">Report</a></li>
        <li><a href="../logout.php">Log Out</a></li>
    </ul>
</nav>
<body>
<div class="container">
<h2>Edit Procurement Activities Report</h2>
    <form action="update_report.php?greport_ID=<?php echo $edit_reportid ?>" method="POST">
        <div class="row">
            <label for="upd_reportid">Report ID:</label>
            <span id="upd_reportid"><?php echo $row['REPORT_ID']; ?></span>
        </div>
        <div class="row">
            <label for="upd_order">Order ID:</label>
            <input type="number" id="upd_order" name="upd_order" value="<?php echo $row['ORDER_ID']; ?>" min="1" required>
        </div>
        <div class="row">
            <label for="upd_history">Purchase Order History:</label>
            <input type="datetime-local" id="upd_history" name="upd_history" value="<?php echo date('Y-m-d\TH:i', strtotime($row['ORDER_HISTORY'])); ?>" required>
        </div>
        <div class="row">
            <label for="upd_vendor">Vendor ID:</label>
            <input type="number" id="upd_vendor" name="upd_vendor" value="<?php echo $row['VENDOR_ID']; ?>" min="1" required>
        </div>
        <div class="row">
            <label for="upd_performance">Vendor Performance:</label>
            <input type="text" id="upd_performance" name="upd_performance" style="height: 80px" value="<?php echo $row['PERFORMANCE']; ?>" required>
        </div>
        <div class="row">
            <label for="upd_item">Item ID:</label>
            <input type="number" id="upd_item" name="upd_item" value="<?php echo $row['ITEM_ID']; ?>" min="1" required>
        </div>
        <div class="row">
            <label for="upd_stock">Inventory Levels:</label>
            <input type="number" id="upd_stock" name="upd_stock" value="<?php echo $row['STOCK']; ?>" min="0" required>
        </div>
        <div class="buttons">
            <button onclick="window.location.href='report.php';">Discard</button>
            <button type="submit">Update Report</button>
        </div>
    </form>
</div>
</body>
<footer>
    <h4>All rights reserved © 2025 Secure AMC System</h4>
</footer>
</html>
<?php
$con->close();
?>