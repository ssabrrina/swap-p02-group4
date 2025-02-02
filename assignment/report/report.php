<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Report</title>
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

        h2 {
            position: absolute;
            right: 1115px; /* Moves h2 to the right */
            top: 290px; 
        }

        a {
            text-decoration: none;
        }

        table {
            width: 100%;
            max-width: 1400px;
            margin: 20px auto;
            border-collapse: collapse;
        }

        table th, table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        button {
            display: block;
            margin-left: auto;
            margin-right: 70px;
            padding: 12px 20px;
            font-size: 16px;
            background-color: #6200ea;
            color: white;
            border: 1px solid black;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #3700b3;
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
<body>
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
        <li><a href="read.php">Report</a></li>
        <li><a href="../logout.php">Log Out</a></li>
    </ul>
</nav>
<br>
<h2>Procurement Activities Report</h2>
<button onclick="window.location.href='create_report.php';">Generate Report</button>
<div class="tables">
<?php
$con = mysqli_connect("localhost","root","","swap_assignment_db"); //connect to database
if (!$con){
 die('Could not connect: ' . mysqli_connect_errno()); //return error is connect fail
}

// Prepare the statement
$stmt= $con->prepare("SELECT * from report");

// Binding can be done either BEFORE the execute (ie bind_param) or AFTER the execute (ie bind_result)
// Bind the parameters - nothing to bind since select *
// $stmt->bind_param('s', $seller); 

// Execute the statement
$stmt->execute();

// Alternatively, you can bind the result to fixed variables.
// Example : $stmt->bind_result($id, $item_name,$stock, $unitprice, $costprice, $shortdesc, $merchant, $detail, $image);

// Obtain the result set
$result = $stmt->get_result();

echo '<table width="100%"';
    echo '<tr><th>Report ID</th><th>Order ID</th><th>Purchase Order History</th><th>Vendor ID</th><th>Vendor Performance</th><th>Item ID</th><th>Inventory Levels</th><th colspan="2">Options</th>';

// Extract the data row by row
while($row = $result->fetch_assoc()) {

    echo '<tr>';
    echo '<td>'.$row['REPORT_ID'].'</td>';
    echo '<td>'.$row['ORDER_ID'].'</td>';
    echo '<td>'.$row['ORDER_HISTORY'].'</td>';
    echo '<td>'.$row['VENDOR_ID'].'</td>';
    echo '<td>'.$row['PERFORMANCE'].'</td>';
    echo '<td>'.$row['ITEM_ID'].'</td>';
    echo '<td>'.$row['STOCK'].'</td>';
    echo '<td> <a href="update_reportform.php?report_id='.$row['REPORT_ID'].'">Edit</a> </td>';
    echo '<td> <a href="delete_report.php?report_id='.$row['REPORT_ID'].'">Delete</a> </td>';
    echo '</tr>';
}
echo '</table>';
?>
</div>
</body>
<footer>
    <h4>All rights reserved © 2025 Secure AMC System</h4>
</footer>
</html>