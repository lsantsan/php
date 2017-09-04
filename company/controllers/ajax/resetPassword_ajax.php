<?php

require_once('./../../../../db_config/connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . 'selnate/models/teacher.php');
require_once($_SERVER['DOCUMENT_ROOT'] . 'selnate/controllers/util.php');

session_start();

if (!isset($_POST['admin_id']) || !isset($_POST['teacher_id']) ) {
    //header("Location: ?controller=pages&action=error");
    //exit;
    echo "Post fields for Reset_Password do not exist";
}

$lv_adminID = $_POST['admin_id'];
$lv_teacherID = $_POST['teacher_id'];
$lv_defaultPassword = Util::encryptPassword(Util::getDefaultPassword());

$lv_result = Teacher::resetTeacherPassword($lv_adminID, $lv_teacherID, $lv_defaultPassword);
if($lv_result != 1){
    //echo "Reset Teacher Password Failed!";
    echo "0";
}
//echo "Password Reseted!";
echo "1";
?>
