<?php

require('../util/Connection.php');
require('../structures/Warehouse.php');
require('../util/SessionFunction.php');
require('../structures/Login.php');
require('../util/Logger.php');
require('../util/Encryption.php');

if (!SessionCheck()) {
    return;
}

require('Header.php');

function formatName($name) {
    $name = preg_replace('/[^a-zA-Z ]/', '', $name);
    $name = ucwords(strtolower($name));
    return trim($name);
}

function isValidCoordinate($value, $coordinateType) {
    if (!is_numeric($value)) return false;
    $coordinate = floatval($value);
    switch ($coordinateType) {
        case 'latitude':  return ($coordinate >= -90 && $coordinate <= 90);
        case 'longitude': return ($coordinate >= -180 && $coordinate <= 180);
        default: return false;
    }
}

function isStringNumber($stringValue) {
    return is_numeric($stringValue);
}

// ✅ Just use session user instead of POST password check
$username = $_SESSION['district_user'] ?? null;
if (!$username) {
    echo "Session expired. Please log in again.";
    exit;
}

// Optional: if form still sends username, make sure it matches session
if (isset($_POST['username']) && $_POST['username'] !== $username) {
    echo "User mismatch. Please log in again.";
    exit;
}

// ---- Input validations ----
if (!isValidCoordinate($_POST["latitude"], 'latitude') || !isValidCoordinate($_POST["longitude"], 'longitude')) {
    echo "Error : Check Latitude and Longitude Value";
    exit;
}

if (!isStringNumber($_POST["storage"])) {
    echo "Error : Check Storage Value";
    exit;
}

$district     = formatName($_POST["district"]);
$latitude     = $_POST["latitude"];
$longitude    = $_POST["longitude"];
$name         = formatName($_POST["name"]);
$id           = $_POST["id"];
$type         = $_POST["type"];
$storage      = $_POST["storage"];
$warehousetype= $_POST["warehousetype"];
$uniqueid     = $_POST["uniqueid"];
$active       = $_POST["active"];

$Warehouse = new Warehouse;
$Warehouse->setUniqueid($uniqueid);
$Warehouse->setDistrict($district);
$Warehouse->setLatitude($latitude);
$Warehouse->setLongitude($longitude);
$Warehouse->setName($name);
$Warehouse->setId($id);
$Warehouse->setType($type);
$Warehouse->setStorage($storage);
$Warehouse->setWarehousetype($warehousetype);
$Warehouse->setActive($active);

$query = $Warehouse->update($Warehouse);
mysqli_query($con, $query);

// Log (without sensitive info)
$filteredPost = $_POST;
unset($filteredPost['username'], $filteredPost['password']);
writeLog("District User -> Warehouse Edit -> $username | Requested JSON -> " . json_encode($filteredPost));

mysqli_close($con);

echo "<script>window.location.href = '../Warehouse.php';</script>";

?>
<?php require('Fullui.php'); ?>
