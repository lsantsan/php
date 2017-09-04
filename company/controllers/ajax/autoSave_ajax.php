<?php

require_once('./../../../../db_config/connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . 'selnate/models/essay.php');

session_start();

if (!isset($_POST['auto_save_id']) || !isset($_POST['student_name']) || !isset($_POST['prompt']) || !isset($_POST['essay_content']) || !isset($_POST['time_passed'])) {
    //header("Location: ?controller=pages&action=error");
    //exit;
    echo "Post fields for Auto_Save do not exist";
}

$lv_autoSaveID = $_POST['auto_save_id'];
$lv_studentName = $_POST['student_name'];
$lv_prompt = $_POST['prompt'];
$lv_essayContent = $_POST['essay_content'];
$lv_timePassed = $_POST['time_passed'];

$lv_result = Essay::saveEssay($lv_autoSaveID, $lv_studentName, $lv_prompt, $lv_essayContent, $lv_timePassed);

echo $lv_result;
?>
