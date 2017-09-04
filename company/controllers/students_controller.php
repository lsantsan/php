<?php

require_once('controllers/util.php');
require_once('models/test.php');
require_once('models/code.php');
require_once('models/essay.php');
require_once('models/teacher.php');

class StudentsController {

    private $cookie_time_length = 60 * 30;
    private $cookie_name = 'selnate_student_cookie';

    public function __construct() {
        $this->home_logo_link = '.';
        $this->logo_link = '?controller=students&action=main';
        $this->logout_link = '?controller=students&action=logout';
    }

    public function main() {

        if (!isset($_COOKIE[$this->cookie_name])) {
            header("Location: ?controller=students&action=login");
            exit;
        }

        $this->startSession();
        $lv_objectArray = $this->loadObjects(); //Objects used to populate Main page.

        $lv_testObj = $lv_objectArray[0];
        $lv_essayObj = $lv_objectArray[1];

        if (isset($_SESSION['time_passed'])) {
            $lv_duration = $_SESSION['time_passed'];
        } else {
            $lv_duration = $lv_testObj->duration;
        }

        if (isset($_SESSION['essay_content'])) {
            $lv_essayContent = $_SESSION['essay_content'];
        } else {
            $lv_essayContent = "";
        }

        if (isset($_SESSION['auto_save_id'])) {
            $lv_autoSaveID = $_SESSION['auto_save_id'];
        } else {
            $lv_autoSaveID = "";
        }
        
        if (isset($_SESSION['isSubmitted'])){
            header("Location: ?controller=students&action=logout");
            exit;        
        }
        require_once('views/students/main.php');
    }
   
    public function submit_essay() {
        if (!isset($_POST['code_id']) || !isset($_POST['student_name']) || !isset($_POST['essay_content']) || !isset($_POST['time_spent']) || !isset($_POST['word_count'])) {
            //header("Location: ?controller=pages&action=error");
            //exit;
            echo "Post fields for Essay do not exist";
        }

        $this->startSession();
        
        $lv_codeID = $_POST['code_id'];
        $lv_studentName = $_POST['student_name'];
        $lv_essayContent = $_POST['essay_content'];
        $lv_timeSpent = $_POST['time_spent'];
        $lv_wordCount = $_POST['word_count'];

        $lv_insertResult = Essay::createEssay($lv_codeID, $lv_studentName, $lv_essayContent, $lv_timeSpent, $lv_wordCount);
        if ($lv_insertResult == 0) {
            echo "Create Essay Problem!";
        }

        $_SESSION['isSubmitted'] = 1;
        
        exit;
    }
  
     public function login() {
        $lv_start_url = '?controller=students&action=start_test';

        //OVERLOADING LOGIN method.        
        foreach (func_get_args() as $error_messages) { //  1 parameter => error message
            $error_message = $error_messages;
            $show_error = "display:block";
        }

        require_once('views/students/login.php');
    }
    
    public function logout() {
        session_start();
        if (session_destroy()) {
            setcookie($this->cookie_name, '', time() - 5, '/');
            header("Location: ?controller=students&action=login");
            exit;
        }
    }
    
    public function start_test() {
        if (!isset($_POST['student_name']) || !isset($_POST['test_code'])) {
            //header("Location: ?controller=pages&action=error");
            //exit;
            echo "Post fields do not exist";
        }
        $lv_studentName = $_POST['student_name'];
        $lv_studentName = Util::validateStudentName($lv_studentName);
        if ($lv_studentName == FALSE) {
            $lv_error_messages = "Name had invalid characters.";
            $this->login($lv_error_messages);
        }

        $lv_testCode = $_POST['test_code'];
        $lv_testCode = Util::validateTestCode($lv_testCode);

        $pos = 4;
        $lv_firstPart = substr($lv_testCode, 0, $pos);
        $lv_lastDigits = substr($lv_testCode, $pos);

        $lv_codeID = Code::checkCode($lv_firstPart, $lv_lastDigits);
        if ($lv_codeID == 0) {
            $lv_error_messages = "Invalid Test Code!";
            $this->login($lv_error_messages);
        } else {
            $this->updateCookie($lv_studentName, $lv_testCode);
            header("Location: ?controller=students&action=main");
            exit;
        }
    }

    private function updateCookie($mv_studentName, $mv_testCode) {
        $lv_cookieValue = Util::generateCookieValue();
        $lv_proc_result = Essay::saveCookie($mv_studentName, $mv_testCode, $lv_cookieValue);
        if ($lv_proc_result) {
            setcookie($this->cookie_name, $lv_cookieValue, time() + $this->cookie_time_length, '/');
            //return $lv_cookieValue;
        } else {
            //header("Location: ?controller=pages&action=error");
            //exit;
            echo "problem 3";
        }
    }

    private function startSession() {
        $lv_cookieValue = $_COOKIE[$this->cookie_name];
        $lv_arrayResult = Essay::retrieveStudentNameByCookie($lv_cookieValue);
        if (empty($lv_arrayResult)) {
            echo "Student Cookie returned no data.";
            return;
        }
        $lv_studentName = $lv_arrayResult[0];
        $lv_testCode = $lv_arrayResult[1];
        $lv_exploded = explode(" ", $lv_studentName);
        $lv_firstName = $lv_exploded[0];
        $lv_sessionID = Util::generateSessionID($lv_studentName);

        session_id($lv_sessionID);
        session_start();
        $_SESSION['studentName'] = $lv_studentName;
        $_SESSION['first_name'] = $lv_firstName;
        $_SESSION['testCode'] = $lv_testCode;
    }

    private function loadObjects() {
        $lv_studentName = $_SESSION['studentName'];
        $lv_testCode = $_SESSION['testCode'];

        $pos = 4;
        $lv_firstPart = substr($lv_testCode, 0, $pos);
        $lv_lastDigits = substr($lv_testCode, $pos);

        $lv_codeObj = Code::retrieveCodeByString($lv_firstPart, $lv_lastDigits);
        if ($lv_codeObj->id == 0) {
            echo "Proc return no codeID.";
        }

        $lv_testObj = Test::retrieveTestByCode($lv_codeObj->id);
        if ($lv_testObj == NULL) {
            echo "Proc returned no test object.";
        }
        $lv_testObj->codeObj = $lv_codeObj;

        $lv_teacherObj = Teacher::retrieveAccountByID($lv_testObj->teacherID);
        if ($lv_teacherObj == NULL) {
            echo "Proc returned no teacher object.";
        }

        $lv_teacherName = $lv_teacherObj->firstName . " " . $lv_teacherObj->lastName;
        $lv_essayObj = new Essay(NULL, $lv_codeObj, $lv_studentName, NULL, NULL, NULL, $lv_teacherName, NULL);

        $lv_objectArray = [$lv_testObj, $lv_essayObj]; //Objects used to populate Main page.
        return $lv_objectArray;
    }

}

?>