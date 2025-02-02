<html>
<body>  
<?php

//connect to database
$con = mysqli_connect("localhost","admin","admin","swap_assignment_db"); 
if (!$con){
	die('Could not connect: ' . mysqli_connect_errno()); //return error is connect fail
}

//A. Prepare SQL Statement
$stmt= $con->prepare("INSERT INTO `report` (`ORDER_ID`,`ORDER_HISTORY`, `VENDOR_ID`, `PERFORMANCE`, `ITEM_ID`, `STOCK`) VALUES (?,?,?,?,?,?)");

$order = htmlspecialchars($_POST["order"]);
$history = date('Y-m-d H:i:s', strtotime($_POST["history"]));
$vendor = htmlspecialchars($_POST["vendor"]);
$performance = htmlspecialchars($_POST["performance"]);
$item = htmlspecialchars($_POST["item"]);
$stock = htmlspecialchars($_POST["stock"]);

// B. Binding the parameter values to the prepared statement 
$stmt->bind_param('isisii', $order, $history, $vendor, $performance, $item, $stock); //bind the parameters
if ($stmt->execute()){  //execute query
    print "INSERT Query executed.";
    header("location:report.php");
}else{
    echo "Error executing INSERT query.";
}
$con->close();
?>
</body>
</html>
