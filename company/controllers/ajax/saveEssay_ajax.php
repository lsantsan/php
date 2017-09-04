<?php

session_start();

$lv_essayContent = $_POST['essay_content'];
$lv_timePassed = $_POST["time_passed"];
if($_POST['auto_save_id']){
    $lv_saveID = (int)$_POST['auto_save_id'];
    $_SESSION['auto_save_id'] = $lv_saveID;
}

$_SESSION['essay_content'] = $lv_essayContent;
$_SESSION['time_passed'] = $lv_timePassed;
?>
