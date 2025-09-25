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

// ---------- Validate session ----------
if (!isset($_SESSION['district_user']) || !isset($_SESSION['district_password']) || !isset($_SESSION['district_district'])) {
    echo "Session invalid. Please login again.";
    return;
}

// ---------- Get UID from POST ----------
$uid = isset($_POST['uid']) ? $_POST['uid'] : '';
if (empty($uid)) {
    echo "Missing UID for deletion.";
    return;
}

// ---------- Initialize DCP object ----------
$DCP = new DCP();
$DCP->setUniqueid($uid);

// ---------- Determine district ----------
$district = $_SESSION['district_district']; // for deleteAll

// ---------- Choose delete function ----------
if ($uid === "all") {
    $query = $DCP->deletealldistrict($DCP, $district);
} else {
    $query = $DCP->delete($DCP);
}

// ---------- Execute deletion ----------
if ($query) {
    mysqli_query($con, $query);
} else {
    echo "Deletion query is invalid.";
    return;
}

// ---------- Get log name ----------
$log_query = $DCP->logname($DCP);
$log_name = "all";
$log_result = mysqli_query($con, $log_query);
if ($log_result && $log_row = mysqli_fetch_assoc($log_result)) {
    $log_name = $log_row['name'];
}

// ---------- Logging ----------
$filteredPost = $_POST;
unset($filteredPost['username'], $filteredPost['password']); // remove sensitive info
writeLog("District User -> Mill deleted -> " . $_SESSION['district_user'] . 
         " | Requested JSON -> " . json_encode($filteredPost) . " | " . $log_name);

// ---------- Close connection and redirect ----------
mysqli_close($con);
echo "<script>window.location.href = '../Mill.php';</script>";

require('Fullui.php');
?>
