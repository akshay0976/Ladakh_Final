<?php

require('../util/Connection.php');
require('../structures/Warehouse.php');
require('../util/SessionFunction.php');
require('../util/Logger.php');

require('../structures/Login.php');

if(!SessionCheck()){
	return;
}

$district = $_SESSION["district_district"];

require('Header.php');

$person = new Login;
$person->setUsername($_POST["username"]);
$person->setPassword($_POST["password"]);

if($_SESSION['district_user']!=$person->getUsername()){
	echo "User is logged in with different username and password";
	return;
}

$query = "SELECT * FROM login WHERE username='".$person->getUsername()."' AND password='".$person->getPassword()."'";
$result = mysqli_query($con,$query);
$numrows = mysqli_num_rows($result);

if($numrows == 0){
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

function logname(Warehouse $warehouse){
	return "SELECT name FROM warehouse WHERE
	uniqueid='".$warehouse->getUniqueid()."'";
	}

$filteredPost = $_POST;
unset($filteredPost['username'], $filteredPost['password']);
writeLog("District User ->" ." Warehouse deleted -> ". $_SESSION['district_user'] .
	"| Requested JSON -> " . json_encode($filteredPost) . " | " . $log_name);
	
echo "<script>window.location.href = '../Warehouse.php';</script>";

?>
<?php require('Fullui.php');  ?>