<html>
<body>  
<?php
$con = mysqli_connect("localhost","root","","swap_assignment_db"); //connect to database
if (!$con){
 die('Could not connect: ' . mysqli_connect_errno()); //return error is connect fail
}

if (!isset($_POST["upd_order"], $_POST["upd_history"], $_POST["upd_vendor"], $_POST["upd_performance"], $_POST["upd_item"], $_POST["upd_stock"], $_GET["greport_ID"])) {
    die("Error: Missing input values.");
}

$query= $con->prepare("UPDATE report SET ORDER_ID=?, ORDER_HISTORY=?, VENDOR_ID=?, PERFORMANCE=?, ITEM_ID=?, STOCK=? WHERE REPORT_ID=?");

$upd_order = htmlspecialchars($_POST["upd_order"]);
$upd_history = date('Y-m-d H:i:s', strtotime($_POST["upd_history"]));
$upd_vendor = htmlspecialchars($_POST["upd_vendor"]);
$upd_performance = htmlspecialchars($_POST["upd_performance"]);
$upd_item = htmlspecialchars($_POST["upd_item"]);
$upd_stock = htmlspecialchars($_POST["upd_stock"]);
$upd_reportid = htmlspecialchars($_GET["greport_ID"]);

//bind the parameters
$query->bind_param('isisiii', $upd_order, $upd_history, $upd_vendor, $upd_performance, $upd_item, $upd_stock, $upd_reportid); 

if ($query->execute()){
    echo "Update Query executed.";
    header("location:report.php");
    exit();
}else{
    echo "Error executing UPDATE query." . $query->error;
}

$query->close();
$con->close();
?>
</body>
</html>