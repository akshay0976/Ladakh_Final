<?php

require('../util/Connection.php');
require('../structures/Mill.php');
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

$DCP = new DCP;
$DCP->setUniqueid($_POST['uid']);

$query = $DCP->delete($DCP);

if($_POST['uid']=="all"){
	$query = $DCP->deleteall($DCP);
}

$log_query = $DCP->logname($DCP);
$log_name= "all";
$log_result = mysqli_query($con,$log_query);
if ($log_result && $row = $log_result->fetch_assoc()) {
$log_name = $row['name'];
}
$filteredPost = $_POST;
unset($filteredPost['username'], $filteredPost['password']);
writeLog("User ->" ." Mill deleted -> ". $_SESSION['user'] . "| Requested
JSON -> " . json_encode($filteredPost) . " | " . $log_name);

mysqli_query($con,$query);
mysqli_close($con);
echo "<script>window.location.href = '../Mill.php';</script>";


?>
<?php require('Fullui.php');  ?>