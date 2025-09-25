<?php
require('../util/Connection.php');
require('../structures/Warehouse.php');
require('../util/SessionFunction.php');
require('../structures/Login.php');
require('../util/Logger.php');

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
$person->setPassword($_SESSION['district_password']); // session password

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

if (!isStringNumber($_POST["storage"])) {
    echo "Error: Check Storage Value";
    return;
}

// ---------- Format and assign variables ----------
$district = formatName($_POST["district"]);
$latitude = $_POST["latitude"];
$longitude = $_POST["longitude"];
$name = formatName($_POST["name"]);
$id = $_POST["id"];
$type = $_POST["type"];
$storage = $_POST["storage"];
$warehousetype = $_POST["warehousetype"];
$uniqueid = uniqid("WH_", true);

// ---------- Prepare Warehouse object ----------
$Warehouse = new Warehouse();
$Warehouse->setUniqueid(substr($uniqueid, 0, 15));
$Warehouse->setDistrict($district);
$Warehouse->setLatitude($latitude);
$Warehouse->setLongitude($longitude);
$Warehouse->setName($name);
$Warehouse->setId($id);
$Warehouse->setType($type);
$Warehouse->setStorage($storage);
$Warehouse->setWarehousetype($warehousetype);
$Warehouse->setActive("1");

// ---------- Check if warehouse already exists ----------
$query_insert_check = $Warehouse->checkInsert($Warehouse);
$query_insert_result = mysqli_query($con, $query_insert_check);
$numrows_insert = mysqli_num_rows($query_insert_result);

if ($numrows_insert == 0) {
    $query = $Warehouse->insert($Warehouse);
    mysqli_query($con, $query);
    
    // ---------- Logging ----------
    $filteredPost = $_POST;
    unset($filteredPost['username'], $filteredPost['password']); // remove sensitive info
    writeLog("District User -> Warehouse added -> " . $_SESSION['district_user'] .
        " | Requested JSON -> " . json_encode($filteredPost));

    mysqli_close($con);
    echo "<script>window.location.href = '../Warehouse.php';</script>";
} else {
    echo "Error: Warehouse ID already exists";
}

require('Fullui.php');
?>
