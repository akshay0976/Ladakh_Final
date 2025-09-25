<?php
require('../util/Connection.php');
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


// Fetch user by username
$query = "SELECT * FROM login WHERE username='".$person->getUsername()."'";
$result = mysqli_query($con,$query);
$numrows = mysqli_num_rows($result);

if($numrows == 0){
	echo "Error : Username is incorrect";
	return;
}

$row = mysqli_fetch_assoc($result);
// Check password (hashed)
if (!password_verify($person->getPassword(), $row['password'])) {
	echo "Error : Password is incorrect";
	return;
}

$uid = $_POST["uid"];

$log_query = "select username from login WHERE uid='$uid'";
$log_result = mysqli_query($con,$log_query);
if ($log_result && $row = $log_result->fetch_assoc()) {
$log_name = $row['username'];
}

$query = "UPDATE login set verified='1' WHERE uid='$uid'";
mysqli_query($con,$query);
mysqli_close($con);

$filteredPost = $_POST;
unset($filteredPost['username'], $filteredPost['password']);
writeLog("User ->" ." Verify User ->". $_SESSION['user'] . "| Requested JSON -> " .
json_encode($filteredPost). " | " . $log_name);

echo "<script>window.location.href = '../Userdata.php';</script>";

?>
<?php require('Fullui.php');  ?>