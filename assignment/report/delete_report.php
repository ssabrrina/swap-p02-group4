<html>
<body>  
<?php
include '../security.php';

$con = mysqli_connect("localhost","root","","swap_assignment_db"); //connect to database
if (!$con){
	die('Could not connect: ' . mysqli_connect_errno()); //return error is connect fail
}

// Prepare the statement 
$stmt= $con->prepare("DELETE FROM report WHERE REPORT_ID=?");


$del_reportid = htmlspecialchars($_GET["report_id"]);

// $seller = 'ADMIN USER1';

$stmt->bind_param('i', $del_reportid); //bind the parameters
if ($stmt->execute()){
    echo "<script>alert('Report deleted successfully!'); window.location.href='report.php';</script></script>";
}else{
    die("Error: " . $stmt->error);
}
$con->close();
?>
</body>
</html>