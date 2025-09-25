<?php

require('../util/Connection.php');
require('../structures/FPS.php');
require('../util/SessionFunction.php');
require('../structures/Login.php');
require('../util/Logger.php'); 
require('../util/Encryption.php');

if(!SessionCheck()){
	return;
}

require('Header.php');

function formatName($name) {
    $name = preg_replace('/[^a-zA-Z ]/', '', $name);
    $name = ucwords(strtolower($name));
    return trim($name);
}

function isValidCoordinate($value, $coordinateType) {
    if (!is_numeric($value)) {
        return false;
    }
    $coordinate = floatval($value);

    switch ($coordinateType) {
        case 'latitude':
            return ($coordinate >= -90 && $coordinate <= 90);
        case 'longitude':
            return ($coordinate >= -180 && $coordinate <= 180);
        default:
            return false;
    }
}

function isStringNumber($stringValue) {
    return is_numeric($stringValue);
}

// ---------- Use session authentication (safe) ----------
if (!isset($_SESSION['district_user']) || !isset($_SESSION['district_password'])) {
    echo "Session invalid. Please login again.";
    return;
}

$sessionUser = $_SESSION['district_user'];
$sessionPassword = $_SESSION['district_password'];

// If client sent a username, ensure it matches the logged-in user
if (isset($_POST['username']) && $_POST['username'] !== $sessionUser) {
    echo "User is logged in with different username and password";
    return;
}

// ---------- Re-verify session password against DB (prepared statement) ----------
$stmt = $con->prepare("SELECT password FROM login WHERE username = ?");
if ($stmt === false) {
    error_log("FPSEdit: prepare failed - " . $con->error);
    echo "Server error.";
    return;
}
$stmt->bind_param('s', $sessionUser);
if (!$stmt->execute()) {
    error_log("FPSEdit: execute failed - " . $stmt->error);
    echo "Server error.";
    $stmt->close();
    return;
}
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (empty($row) || !password_verify($sessionPassword, $row['password'])) {
    // generic message to avoid leaking which part failed
    echo "Error : Password or Username is incorrect";
    return;
}

// ---------- Input validations ----------
if (!isset($_POST["latitude"], $_POST["longitude"], $_POST["demand"], $_POST["demand_rice"], $_POST["demand_frice"])) {
    echo "Missing required fields.";
    return;
}

if (!isValidCoordinate($_POST["latitude"], 'latitude') || !isValidCoordinate($_POST["longitude"], 'longitude')) {
    echo "Error : Check Latitude and Longitude Value";
    return;
}

if (!isStringNumber($_POST["demand"])) {
    echo "Error : Check DemandWheat Value";
    return;
}

if (!isStringNumber($_POST["demand_rice"])) {
    echo "Error : Check DemandRice Value";
    return;
}
if (!isStringNumber($_POST["demand_frice"])) {
    echo "Error : Check DemandFRice Value";
    return;
}

// ---------- Assign values (sanitize where needed) ----------
$district = isset($_POST["district"]) ? $_POST["district"] : '';
$latitude = $_POST["latitude"];
$longitude = $_POST["longitude"];
$name = isset($_POST["name"]) ? $_POST["name"] : '';
$id = isset($_POST["id"]) ? $_POST["id"] : '';
$type = isset($_POST["type"]) ? $_POST["type"] : '';
$demand = $_POST["demand"];
$demand_rice = $_POST["demand_rice"];
$demand_frice = $_POST["demand_frice"];
$uniqueid = isset($_POST["uniqueid"]) ? $_POST["uniqueid"] : '';
$active = isset($_POST["active"]) ? $_POST["active"] : '1';

// Optionally format name/district if you want (kept as original behavior available)
// $district = formatName($district);
// $name = formatName($name);

$FPS = new FPS;
$FPS->setUniqueid($uniqueid);
$FPS->setDistrict($district);
$FPS->setLatitude($latitude);
$FPS->setLongitude($longitude);
$FPS->setName($name);
$FPS->setId($id);
$FPS->setType($type);
$FPS->setDemand($demand);
$FPS->setDemandRice($demand_rice);
$FPS->setDemandFRice($demand_frice);
$FPS->setActive($active);

$query = $FPS->update($FPS);

// Execute update query and handle errors
if ($query) {
    if (!mysqli_query($con, $query)) {
        error_log("FPSEdit: update failed - " . mysqli_error($con));
        echo "Error : Unable to update FPS.";
        mysqli_close($con);
        return;
    }
} else {
    echo "Error : Invalid update query.";
    return;
}

// Logging (remove sensitive fields)
$filteredPost = $_POST;
unset($filteredPost['username'], $filteredPost['password']);
writeLog("District User -> FPS Edit -> " . $_SESSION['district_user'] . " | Requested JSON -> " . json_encode($filteredPost));

mysqli_close($con);

echo "<script>window.location.href = '../FPS.php';</script>";

?>
<?php require('Fullui.php');  ?>
