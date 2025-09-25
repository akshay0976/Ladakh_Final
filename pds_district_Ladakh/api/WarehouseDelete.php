<?php

require('../util/Connection.php');
require('../structures/Warehouse.php');
require('../util/SessionFunction.php');
require('../util/Logger.php');
require('../structures/Login.php');

if(!SessionCheck()){
	return;
}


require('Header.php');

$person = new Login;
$person->setUsername($_POST["username"] ?? '');
$person->setPassword($_POST["password"] ?? '');

if (!isset($_SESSION['district_user']) || $_SESSION['district_user'] !== $person->getUsername()){
	echo "User is logged in with different username and password";
	return;
}

// Fetch user by username and verify password hash
$stmt = $con->prepare("SELECT * FROM login WHERE username = ? LIMIT 1");
if (!$stmt) {
	error_log('[api/WarehouseDelete.php] prepare failed: ' . $con->error);
	echo "Error : Something went wrong";
	return;
}
$username = $person->getUsername();
$stmt->bind_param('s', $username);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
	echo "Error : Password or Username is incorrect";
	$stmt->close();
	return;
}
$row = $res->fetch_assoc();
$stmt->close();

$dbHashedPassword = $row['password'] ?? '';
if (!password_verify($person->getPassword(), $dbHashedPassword)) {
	echo "Error : Password or Username is incorrect";
	return;
}

$Warehouse = new Warehouse;
$Warehouse->setUniqueid($_POST['uid']);

$query = $Warehouse->delete($Warehouse);

if($_POST['uid']=="all"){
	$query = $Warehouse->deletealldistrict($Warehouse, $district);
}

$log_query = $Warehouse->logname($Warehouse);
$log_name= "all";
$log_result = mysqli_query($con,$log_query);
if ($log_result && $row = $log_result->fetch_assoc()) {
	$log_name = $row['name'];
}

mysqli_query($con,$query);
mysqli_close($con);
echo "<script>window.location.href = '../Warehouse.php';</script>";

$filteredPost = $_POST;
unset($filteredPost['username'], $filteredPost['password']);
writeLog("District User ->" ." Warehouse deleted -> ". $_SESSION['district_user'] . "| Requested JSON -> " . json_encode($filteredPost) . " | " . $log_name);

?>
<?php require('Fullui.php');  ?>