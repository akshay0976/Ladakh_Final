<?php
require('../util/Connection.php');
require('../structures/Login.php');
require('../util/Security.php');
require ('../util/Encryption.php');
$nonceValue = 'nonce_value';
session_start();

// echo json_encode($_POST);
// echo json_encode($_SESSION);


if (!isset($_SESSION['captcha']) || !isset($_SESSION['csrf_token'])) {
    die("Sowething went wrong.");
}

if(empty($_POST) || empty($_SESSION) || empty($_POST['username']) || empty($_POST['password'])){
    die("Something went wrong");
}

if(empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Something went wrong. Request denied.");
}

if (empty($_POST['captchainput']) ||$_SESSION['captcha'] !==  $_POST['captchainput']){
	unset($_SESSION['captcha']);
  die("Please Check Captcha");
}

$person = new Login;
$person->setUsername($_POST["username"]);
$Encryption = new Encryption();
$person->setPassword($Encryption->decrypt($_POST["password"], $nonceValue));

$query = "SELECT * FROM login WHERE username='".$person->getUsername()."'";
$result = mysqli_query($con,$query);
$row = mysqli_fetch_assoc($result);

if (empty($row)) {
	die("Password or Username is incorrect");
}

if ($row["verified"] == 0) {
		echo "Error: Your account needs to be verified.";
		exit;
}

$dbHashedPassword = $row['password'];
if(password_verify($person->getPassword(), $dbHashedPassword)){
 if($row['role']=="admin"){
	    session_regenerate_id(true);
		$count = 1 + $row['count'];
		$uniqueId = uniqid();
		$authToken = md5($uniqueId);
		$currentLoginTime = date("Y-m-d H:i:s");
		$queryUpdate = "UPDATE login SET token='$authToken',lastlogin='$currentLoginTime',count='$count' WHERE username='".$person->getUsername()."'";
		mysqli_query($con,$queryUpdate);
		
		$_SESSION['user'] = $person->getUsername();
		$_SESSION['token'] = $authToken;
		
		mysqli_close($con);
		echo "<script>window.location.href = '../Home.php';</script>";
    }
} 
else{
    echo "Error : Password or Username is incorrect";
}

?>
