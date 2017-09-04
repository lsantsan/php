<?php

session_start();

if (isset($_GET["time"])) {
    $_SESSION['time_passed'] = $_GET['time'];
}
?>