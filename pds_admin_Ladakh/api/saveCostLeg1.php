<?php
require('../util/Connection.php');
require('../util/SessionFunction.php');
require('../util/Logger.php');

if (!SessionCheck()) {
    return;
}

require('Header.php');

// Function to sanitize inputs (extra safety)
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

foreach ($_POST as $key => $value) {
    if (substr($key, 0, 5) === 'cost_' && !empty($value)) {
        $id = sanitizeInput(substr($key, 5));
        $value_temp = sanitizeInput($value);

        // Fetch log details
        $log_query = "SELECT * FROM optimised_table_leg1 WHERE id='$id'";
        $log_result = mysqli_query($con, $log_query);
        if ($log_result && $row = $log_result->fetch_assoc()) {
            $user_id = $row['year'];
            $user_id1 = $row['month'];
        }

        // Validate value as numeric
        $value = filter_var($value_temp, FILTER_VALIDATE_FLOAT);
        if ($value === false) {
            $value = filter_var($value_temp, FILTER_VALIDATE_INT);
        }

        // Reject if not numeric
        if ($value === false) {
            echo "Error : Invalid value: $value_temp<br>";
            return;
        } else {
            // Prepared statement to prevent SQL injection
            $stmt = $con->prepare("UPDATE optimised_table_leg1 SET cost = ? WHERE id = ?");
            $stmt->bind_param("di", $value, $id);
            
            $filteredPost = $_POST;
            unset($filteredPost['username'], $filteredPost['password']);

            writeLog("User -> Cost for leg1 Added -> " . $_SESSION['user'] . 
                " | Requested JSON -> " . json_encode($filteredPost) . 
                " | " . $user_id . " | " . $user_id1);

            if ($stmt->execute()) {
                // success
            } else {
                echo "Error : updating record: " . $con->error;
                return;
            }
        }
    }
}

echo "<script>window.location.href = '../PerformaLeg1.php';</script>";

require('Fullui.php');
?>
