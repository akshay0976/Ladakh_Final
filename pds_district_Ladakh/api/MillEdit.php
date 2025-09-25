<?php
require('../util/Connection.php');
require('../structures/Mill.php');
require('../util/SessionFunction.php');
require('../structures/Login.php');
require('../util/Logger.php');
require('../util/Encryption.php');

session_start();

if (!SessionCheck()) {
    return;
}

require('Header.php');

// ---------- Helper functions ----------
function formatName($name) {
    $name = preg_replace('/[^a-zA-Z ]/', '', $name);
    $name = ucwords(strtolower($name));
    return trim($name);
}

function isValidCoordinate($value, $coordinateType) {
    if (!is_numeric($value)) return false;
    $coordinate = floatval($value);

    switch ($coordinateType) {
        case 'latitude': return ($coordinate >= -90 && $coordinate <= 90);
        case 'longitude': return ($coordinate >= -180 && $coordinate <= 180);
        default: return false;
    }
}

function isStringNumber($stringValue) {
    return is_numeric($stringValue);
}

// ---------- Use session user ----------
if (!isset($_SESSION['district_user']) || !isset($_SESSION['district_password'])) {
    echo "Session invalid. Please login again.";
    return;
}

$person = new Login();
$person->setUsername($_SESSION['district_user']);
$person->setPassword($_SESSION['district_password']); // Use session password

// Optional: check POST username matches session
if (isset($_POST['username']) && $_POST['username'] !== $_SESSION['district_user']) {
    echo "Username mismatch. Action denied.";
    return;
}

// ---------- Input validation ----------
if (!isValidCoordinate($_POST["latitude"], 'latitude') || !isValidCoordinate($_POST["longitude"], 'longitude')) {
    echo "Error: Check Latitude and Longitude Value";
    return;
}

if (!isStringNumber($_POST["demand"])) {
    echo "Error: Check Rice Procurement Value";
    return;
}

if (!isStringNumber($_POST["demand_rice"])) {
    echo "Error: Check Wheat Procurement Value";
    return;
}

// ---------- Format and assign variables ----------
$district = formatName($_POST["district"]);
$latitude = $_POST["latitude"];
$longitude = $_POST["longitude"];
$name = formatName($_POST["name"]);
$id = $_POST["id"];
$type = $_POST["type"];
$demand = $_POST["demand"];
$demand_rice = $_POST["demand_rice"];
$uniqueid = $_POST["uniqueid"];
$active = $_POST["active"];

// ---------- Prepare DCP object ----------
$DCP = new DCP();
$DCP->setUniqueid($uniqueid);
$DCP->setDistrict($district);
$DCP->setLatitude($latitude);
$DCP->setLongitude($longitude);
$DCP->setName($name);
$DCP->setId($id);
$DCP->setType($type);
$DCP->setDemand($demand);
$DCP->setDemandRice($demand_rice);
$DCP->setActive($active);

// ---------- Execute update ----------
$query = $DCP->update($DCP);
if ($query) {
    mysqli_query($con, $query);
} else {
    echo "Error: Update query is invalid.";
    return;
}

// ---------- Logging ----------
$filteredPost = $_POST;
unset($filteredPost['username'], $filteredPost['password']); // Remove sensitive info
writeLog(
    "District User -> Mill Edit -> " . $_SESSION['district_user'] .
    " | Requested JSON -> " . json_encode($filteredPost)
);

// ---------- Close connection and redirect ----------
mysqli_close($con);
echo "<script>window.location.href = '../Mill.php';</script>";

require('Fullui.php');
?>
