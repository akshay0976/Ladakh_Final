<?php
session_start();

// Store CAPTCHA in session
if (isset($_POST['captcha'])) {
    $_SESSION['captcha'] = $_POST['captcha'];
    echo "CAPTCHA stored successfully";
} else {
    echo "No CAPTCHA provided";
}
?>