<?php

require('../util/Connection.php');
require('../structures/Warehouse.php');
require('../util/SessionFunction.php');
require('../structures/Login.php');
require('../util/Logger.php'); 
if(!SessionCheck()){
	return;
}

require('Header.php');

$person = new Login;
$person->setUsername($_POST["username"]);
$person->setPassword($_POST["password"]);

if($_SESSION['user']!=$person->getUsername()){
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

$district = $_POST["district"];
$status = $_POST["status"];
$fpstype = $_POST["fpstype"];

if($fpstype=='Model FPS'){
	if($status=='active'){
		$query = "UPDATE fps SET active='1' WHERE district='$district' AND type='Model FPS'";
		writeLog("User ->" ." Model FPS Active -> ". $_SESSION['user'] . "| " . $district);
	}
	else{
		$query = "UPDATE fps SET active='0' WHERE district='$district' AND type='Model FPS'";
		writeLog("User ->" ." Model FPS InActive -> ". $_SESSION['user'] . "| " . $district);

	}
}
else{
	if($status=='active'){
		$query = "UPDATE fps SET active='1' WHERE district='$district' AND type='Normal FPS'";
		writeLog("User ->" ." Normal FPS Active -> ". $_SESSION['user'] . "| " . $district);
	}
	else{
		$query = "UPDATE fps SET active='0' WHERE district='$district' AND type='Normal FPS'";
		writeLog("User ->" ." Normal FPS InActive -> ". $_SESSION['user'] . "| " . $district);
	}
}
mysqli_query($con, $query);
echo "<script>window.location.href = '../FPS.php';</script>";


?>
<?php require('Fullui.php');  ?>