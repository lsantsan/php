<?php

require_once('./../../../../db_config/connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . 'selnate/models/essay.php');
require_once($_SERVER['DOCUMENT_ROOT'] . 'selnate/models/test.php');
require_once($_SERVER['DOCUMENT_ROOT'] . 'selnate/models/pdf.php');

if (!isset($_POST['student_checkbox']) && !isset($_POST['id_name_list_checkbox'])) {
    echo "Nothing to be printed";
    exit;
}
if (!isset($_POST['code_id']) || !is_numeric($_POST['code_id'])) {
    echo "Invalid code id";
    exit;
}
$lv_codeID = $_POST['code_id'];
$lv_teacherFullName = $_POST['teacher_fullname'];
$lv_testObj = Test::retrieveTestByCode($lv_codeID);

$pdf = new PDF();
$title = 'Print Page';
$pdf->SetTitle($title);

//PRINTS ID/NAME LIST
if (isset($_POST['id_name_list_checkbox']) && ($_POST['id_name_list_checkbox'] == 1)) {    
    $lv_idNameList = Essay::retrieveNameIDListByCodeID($lv_codeID);
    $lv_creationDate = date('m / d / Y', time());

    $pdf->PrintIDNameList($lv_idNameList, $lv_teacherFullName, $lv_testObj->instructions, $lv_testObj->prompt, $lv_creationDate);
}

//PRINTS ESSAY(S)
if (isset($_POST['student_checkbox'])) {

    $lv_essayIDList = $_POST['student_checkbox'];
    $lv_essayObjList = Essay::retrieveEssayByID($lv_essayIDList);
    //PRINTS ESSAY(S) WITHOUT STUDENT'S NAME.
    if (isset($_POST['hide_names_checkbox']) && ($_POST['hide_names_checkbox'] == 1)) {
        $lv_idNameList = Essay::retrieveNameIDListByCodeID($lv_codeID);
        foreach ($lv_essayObjList as $lv_essayObj) {
            $lv_nameID = array_search($lv_essayObj->studentName, $lv_idNameList);
            printEssay($lv_nameID, $lv_teacherFullName, $pdf, $lv_essayObj, $lv_testObj);
        }
    } else { //PRINTS WITH NAME.
        foreach ($lv_essayObjList as $lv_essayObj) {
            printEssay($lv_essayObj->studentName, $lv_teacherFullName, $pdf, $lv_essayObj, $lv_testObj);
        }
    }
}

$pdf->Output();



/* * *************** UTIL FUNCTIONS ********************** */

function printEssay($mv_studentName, $mv_teacherFullName, $mv_pdfObj, $mv_essayObj, $mv_testObj) {
    $lv_formatedDate = formatDate($mv_essayObj->creationDate);

    $mv_pdfObj->PrintEssay($mv_studentName, $mv_essayObj->timeSpent, $mv_teacherFullName
            , $mv_essayObj->wordCount, $mv_testObj->instructions, $mv_testObj->prompt
            , $mv_essayObj->content, $lv_formatedDate);
}

function formatDate($mv_dateDBFormat) {
    $lv_explodedDate = explode('-', $mv_dateDBFormat);
    $lv_cleanedDay = explode(' ', $lv_explodedDate[2]);
    $lv_formatedDate = $lv_explodedDate[1] . " / " . $lv_cleanedDay[0] . " / " . $lv_explodedDate[0];
    return $lv_formatedDate;
}

?>
