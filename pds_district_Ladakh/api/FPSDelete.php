<?php

require('../util/Connection.php');
require('../structures/FPS.php');
require('../util/SessionFunction.php');
require('../structures/Login.php');
require('../util/Logger.php'); 

if (!SessionCheck()) {
    return;
}

$district = $_SESSION["district_district"];

require('Header.php');

$person = new Login;
$person->setUsername($_POST["username"]);
$person->setPassword($_POST["password"]);

if ($_SESSION['district_user'] != $person->getUsername()) {
    echo "User is logged in with different username and password";
    return;
}

// ✅ Use prepared statement for login lookup
$stmt = $con->prepare("SELECT password FROM login WHERE username = ?");
$stmt->bind_param("s", $person->getUsername());
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

// ✅ Verify credentials
if (!$row || !password_verify($person->getPassword(), $row['password'])) {
    echo "Error : Password or Username is incorrect";
    return;
}

// ✅ Delete FPS
$FPS = new FPS;
$FPS->setUniqueid($_POST['uid']);

if ($_POST['uid'] === "all") {
    $query = $FPS->deletealldistrict($FPS, $district);
} else {
    $query = $FPS->delete($FPS);
}

// ✅ Get log name safely
$log_query = $FPS->logname($FPS);
$log_name = "all";
$log_result = mysqli_query($con, $log_query);
if ($log_result && $row = $log_result->fetch_assoc()) {
    $log_name = $row['name'];
}

// ✅ Execute delete
mysqli_query($con, $query);
mysqli_close($con);

// ✅ Write secure logs (without credentials)
$filteredPost = $_POST;
unset($filteredPost['username'], $filteredPost['password']);
writeLog(
    "district_user -> FPS deleted -> " . $_SESSION['district_user'] .
    " | Requested JSON -> " . json_encode($filteredPost) . " | " . $log_name
);

echo "<script>window.location.href = '../FPS.php';</script>";

?>
<?php require('Fullui.php');  ?>
