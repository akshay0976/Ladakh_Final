<?php
require('Connection.php');

$ip_address = "";

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip_address = $_SERVER['REMOTE_ADDR'];
}

session_start();

// Set session timeout duration (e.g., 30 min = 1800 sec; for testing use 10 sec)
$timeout_duration = 10; // change to 10 for testing

// Check if user session exists
if(isset($_SESSION['user'])){
    // Check for session timeout
    if(isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration){
        session_unset();
        session_destroy();
        header("Location: AdminLogin.html");
        exit();
    }

    // Update last activity timestamp
    $_SESSION['LAST_ACTIVITY'] = time();

    $user = $_SESSION['user'];
    $token = $_SESSION['token'];

    $query = "SELECT * FROM login WHERE username='$user' AND token='$token'";
    $result = mysqli_query($con,$query);
    $numrows = mysqli_num_rows($result);

    if($numrows == 0){
        session_unset();
        session_destroy();
        header("Location: AdminLogin.html");
        exit();
    }

    $currentLoginTime = date("Y-m-d H:i:s");
    $queryUpdate = "UPDATE login SET lastlogin='$currentLoginTime' WHERE username='$user'";
    mysqli_query($con,$queryUpdate);

} else {
    header("Location: AdminLogin.html");
    exit();
}
?>
