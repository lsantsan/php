<?php

require_once('controllers/util.php');
require_once('models/test.php');
require_once('models/code.php');
require_once('models/essay.php');

class TeachersController
{

    private $cookie_time_length = 60 * 30;
    private $cookie_name = 'selnate_cookie';

    public function __construct()
    {
        $this->home_logo_link = '.';
        $this->logo_link = '?controller=teachers&action=main';
        $this->logout_link = '?controller=teachers&action=logout';
        $this->my_account_link = '?controller=teachers&action=edit_account';
        $this->admin_or_teacher_link = '?controller=admin&action=main';
    }

    public function main()
    {
        $create_test_url = "?controller=teachers&action=new_test";
        $view_essays_url = "?controller=teachers&action=view_essays";
        $edit_test_url = "?controller=teachers&action=edit_test";
        $remove_test_url = "?controller=teachers&action=remove_test";


        if (!isset($_COOKIE[$this->cookie_name])) {
            header("Location: ?controller=teachers&action=login");
            exit;
        }

        $lv_teacherObj = $this->loadTeacherObj();
        if ($lv_teacherObj->resetPassword == 1) {
            header("Location: ?controller=teachers&action=edit_account");
        } else {
            $test_list = Test::retrieveAllTestByTeacher($lv_teacherObj->id);
            $testListGroupedBySemester = $this->groupBySemester($test_list);
        }


        require_once('views/teachers/main.php');
    }

    public function new_test()
    {
        $create_test_url = "?controller=teachers&action=create_test";

        //OVERLOADING NEW_TEST method.        
        foreach (func_get_args() as $screenUpdate) { //  1 parameter => screenUpdate[]
            if (array_key_exists('error', $screenUpdate)) {
                $message_type = "error_message";
                $message = $screenUpdate['error'];
            }
            if (array_key_exists('errors', $screenUpdate)) {
                $lv_empty_inputs = $screenUpdate['errors'];
                foreach ($lv_empty_inputs as $input) {
                    switch ($input) {
                        case 'duration':
                            $duration_error = "style='color:red;'";
                            break;
                        case 'instructions':
                            $instructions_error = "style='color:red;'";
                            break;
                        case 'prompt':
                            $prompt_error = "style='color:red;'";
                            break;
                        default:
                            $duration_error = $instructions_error = $prompt_error = "";
                    }
                }
            }
            if (array_key_exists('success', $screenUpdate)) {
                $message_type = "success_message";
                $message = $screenUpdate['success'];
                $lv_isDisabled = "disabled";
            }

            $show_message = "style='display:block;'";
        }
        // ************* LOAD PAGE ************************

        $this->isSessionOpened();
        require_once('views/teachers/create_test.php');
    }

    public function create_test()
    {
        $this->isSessionOpened();
        $lv_screenUpdate = array();
        $lv_empty_fields = [];
        $lv_duration = $_POST['duration'];
        $lv_semester = $_POST['semester'];
        $lv_type = $_POST['type'];
        $lv_instructions = $_POST['instructions'];
        $lv_prompt = $_POST['prompt'];
        $lv_teacherObj = unserialize($_SESSION['teacherObj']);

        if ($lv_duration == '') {
            $lv_empty_fields[] = 'duration';
        }
        if (empty($lv_instructions)) {
            $lv_empty_fields[] = 'instructions';
        }
        if (empty($lv_prompt)) {
            $lv_empty_fields[] = 'prompt';
        }
        if (!empty($lv_empty_fields)) {
            $lv_screenUpdate['errors'] = $lv_empty_fields;
            $lv_screenUpdate['error'] = 'Missing information.';
            $this->new_test($lv_screenUpdate);
        }

        if (empty($lv_empty_fields)) {
            //Building codeFirstPart
            $lv_current_year = substr(date("Y"), 2, 3); //Last two digits of the year.
            $lv_codeFirstPart = $lv_semester . $lv_current_year . $lv_type;
            $lv_insertResult = Test::createTest($lv_teacherObj->id, $lv_codeFirstPart, $lv_duration, $lv_instructions, $lv_prompt);
            if ($lv_insertResult == 0 || $lv_insertResult == -1) {
                echo "Insert failed";
                header("Location: ?controller=pages&action=error");
                exit;
            }
            $lv_codeObj = Code::retrieveCodeByTestID($lv_insertResult);
            if (is_null($lv_codeObj)) {
                echo "Retrieve code failed";
                header("Location: ?controller=pages&action=error");
                exit;
            }
            $lv_testCode = $lv_codeObj->firstPart . $lv_codeObj->lastDigits;

            $lv_screenUpdate['success'] = 'Success! Test code is ' . $lv_testCode;
            $this->new_test($lv_screenUpdate);
            exit;
        }
    }

    public function remove_test()
    {
        if (!is_numeric($_GET['id'])) {
            echo 'TestID is not numeric';
            header("Location: ?controller=pages&action=error");
            exit;
        }
        $this->isSessionOpened();
        $lv_teacherObj = unserialize($_SESSION['teacherObj']);
        $lv_testID = $_GET['id'];

        $lv_result = Test::removeTestByID($lv_teacherObj->id, $lv_testID);
        if ($lv_result != 1) {
            echo 'Remove Failed!';
            header("Location: ?controller=pages&action=error");
            exit;
        }
        header("Location: ?controller=teachers&action=main");
        exit;
    }

    public function view_essays()
    {
        $view_essays_url = "?controller=teachers&action=view_essays";
        $print_essay_url = "controllers/ajax/printEssay_ajax.php";

        $this->isSessionOpened();

        $lv_teacherObj = unserialize($_SESSION['teacherObj']);

        if (isset($_GET['eid'])) { //Essay id
            if (!is_numeric($_GET['eid'])) {
                echo 'EssayID is not numeric';
                header("Location: ?controller=pages&action=error");
                exit;
            }
            $lv_essayObj_list = Essay::retrieveEssayByID([$_GET['eid']]);
            $lv_essayObj = $lv_essayObj_list[0];
            $lv_testObj = Test::retrieveTestByCode($lv_essayObj->codeObj->id);
        }

        if (!is_numeric($_GET['id'])) { //Code id
            echo 'CodeID is not numeric';
            header("Location: ?controller=pages&action=error");
            exit;
        }

        $lv_codeID = $_GET['id'];
        $lv_studentList = Essay::retrieveStudentListByCodeID($lv_codeID);
        /* if (empty($lv_studentList)) {
          echo "Empty List!";
          } */

        if (isset($_GET['nameid'])) { //Just a trigger
            $lv_hideEssayView = 'display:none;';
            $lv_hideNameID = 'display:block;';
            $lv_nameIDList = Essay::retrieveNameIDListByCodeID($lv_codeID);
        }

        require_once('views/teachers/view_essays.php');
    }

    public function edit_test()
    {
        $update_test_url = "?controller=teachers&action=update_test";

        //OVERLOADING EDIT_TEST method.        
        foreach (func_get_args() as $screenUpdate) { //  1 parameter => screenUpdate[]
            if (array_key_exists('error', $screenUpdate)) {
                $message_type = "error_message";
                $message = $screenUpdate['error'];
            }
            if (array_key_exists('errors', $screenUpdate)) {
                $lv_empty_inputs = $screenUpdate['errors'];
                foreach ($lv_empty_inputs as $input) {
                    switch ($input) {
                        case 'duration':
                            $duration_error = "style='color:red;'";
                            break;
                        case 'instructions':
                            $instructions_error = "style='color:red;'";
                            break;
                        case 'prompt':
                            $prompt_error = "style='color:red;'";
                            break;
                        default:
                            $duration_error = $instructions_error = $prompt_error = "";
                    }
                }
            }
            if (array_key_exists('success', $screenUpdate)) {
                $message_type = "success_message";
                $message = $screenUpdate['success'];
                $lv_isDisabled = "disabled";
            }

            $show_message = "style='display:block;'";
        }

        // ************* LOAD PAGE ************************

        if (!is_numeric($_GET['id'])) {
            echo 'TestID is not numeric';
            header("Location: ?controller=pages&action=error");
            exit;
        }
        $this->isSessionOpened();
        $lv_testID = $_GET['id'];
        $lv_testObj = Test::retrieveTestByID($lv_testID);
        $_SESSION['testObj'] = serialize($lv_testObj);
        $lv_testSemester = substr($lv_testObj->codeObj->firstPart, 0, 1); //First digit of the code. Ex S16F => 'S'.
        $lv_testType = substr($lv_testObj->codeObj->firstPart, 3, 3); //Last digit of the code. Ex S16F => 'F'.

        require_once('views/teachers/edit_test.php');
    }

    public function edit_account()
    {
        $update_account_url = "?controller=teachers&action=update_account";

        //OVERLOADING EDIT_ACCOUNT method.        
        foreach (func_get_args() as $screenUpdate) { //  1 parameter => screenUpdate[]
            if (empty($screenUpdate)) {
                echo "ScreenUpdate is empty";
                header("Location: ?controller=pages&action=error");
                exit;
            }
            foreach ($screenUpdate as $key => $arrayValue) {
                switch ($key) {
                    /* case 'success_messages':
                      $lv_message_type = "success_message";
                      $lv_messages = $arrayValue;
                      $lv_disable_field = TRUE;
                      echo "";
                      break; */
                    case 'error_messages':
                        $lv_message_type = "error_message";
                        $lv_messages = $arrayValue;
                        break;
                    case 'fields':
                        foreach ($arrayValue as $field) {
                            switch ($field) {
                                case 'firstName':
                                    $firstName_error = "style='color:red;'";
                                    break;
                                case 'lastName':
                                    $lastName_error = "style='color:red;'";
                                    break;
                                case 'username':
                                    $username_error = "style='color:red;'";
                                    break;
                                case 'currentPassword':
                                    $currentPassword_error = "style='color:red;'";
                                    break;
                                case 'newPassword_1':
                                    $newPassword_1_error = "style='color:red;'";
                                    break;
                                case 'newPassword_2':
                                    $newPassword_2_error = "style='color:red;'";
                                    break;
                            }
                        }
                        break; //For FIELDS case.
                }
            }

            $show_message = "style='display:block;'";
        }

        // ************  LOAD PAGE  ************************************
        $this->isSessionOpened();
        $lv_teacherObj = unserialize($_SESSION['teacherObj']);
        //Read explanation about this Session variable at the end of the update_account method.
        if (isset($_SESSION['success_messages'])) {
            $lv_arrayValue = $_SESSION['success_messages'];
            $lv_message_type = "success_message";
            $lv_messages = $lv_arrayValue['success_messages'];
            $lv_isDisabled = "disabled";
            $show_message = "style='display:block;'";
            unset($_SESSION['success_messages']);
        }

        require_once('views/teachers/edit_account.php');
    }

    public function update_account()
    {
        //$edit_test_url = "?controller=teachers&action=edit_test";

        $this->isSessionOpened();
        $lv_screenUpdate = array();
        $lv_empty_fields = [];
        $lv_error_messages = [];
        $lv_firstName = $_POST['first_name'];
        $lv_lastName = $_POST['last_name'];
        $lv_username = $_POST['username'];
        $lv_currentPassword = $_POST['current_password'];
        $lv_newPassword_1 = $_POST['new_password_1'];
        $lv_newPassword_2 = $_POST['new_password_2'];
        $lv_teacherObj = unserialize($_SESSION['teacherObj']);

// ********************* DATA VALIDATION *****************************************
        if (empty($lv_firstName)) {
            $lv_empty_fields[] = 'firstName';
        }
        if (empty($lv_lastName)) {
            $lv_empty_fields[] = 'lastName';
        }
        if (empty($lv_username)) {
            $lv_empty_fields[] = 'username';
        }
        //Checks if any of the password fields has value. If so, it also checks if all of them have value.
        if (!empty($lv_currentPassword) || !empty($lv_newPassword_1) || !empty($lv_newPassword_2)) {
            if (empty($lv_currentPassword)) {
                $lv_empty_fields[] = 'currentPassword';
            }
            if (empty($lv_newPassword_1)) {
                $lv_empty_fields[] = 'newPassword_1';
            }
            if (empty($lv_newPassword_2)) {
                $lv_empty_fields[] = 'newPassword_2';
            }
        }
        //Checks if new password was type correctly.
        if ($lv_newPassword_1 != $lv_newPassword_2 && !empty($lv_newPassword_1 && !empty($lv_newPassword_2))) {
            $lv_error_messages[] = 'New password not confirmed.';
        }
        //Checks if there was any empty field. If so, sets up the error message.
        if (!empty($lv_empty_fields)) {
            $lv_screenUpdate['fields'] = $lv_empty_fields;
            $lv_error_messages[] = 'Information was missing.';
            $lv_screenUpdate['error_messages'] = $lv_error_messages;
        }
        //Checks if there was any error to be displayed on screen. If so, sends message back to interface.
        if (!empty($lv_screenUpdate)) {
            $this->edit_account($lv_screenUpdate);
        }

// ******************** UPDATE PROCESS ***********************************    
        if (empty($lv_screenUpdate)) {

            $lv_adminID = 0; //This proc call has a hardcoded '0' because adminID is not being used.
            $lv_updateResult = Teacher::updateAccount($lv_adminID, $lv_teacherObj->id, $lv_username, $lv_firstName, $lv_lastName);
            if ($lv_updateResult != 1) {
                echo $lv_updateResult . " Info Update Failed!";
            }
            //Updates password if required.
            if (!empty($lv_currentPassword)) {
                $currentHashPass = Util::encryptPassword($lv_currentPassword);
                $newHashPass = Util::encryptPassword($lv_newPassword_1);
                $lv_is_valid_user = Teacher::checkUser($lv_teacherObj->username, $currentHashPass);
                if ($lv_is_valid_user != 1) {
                    $lv_screenUpdate['fields'] = ['currentPassword'];
                    $lv_screenUpdate['error_messages'] = ['Invalid current password.'];
                    $this->edit_account($lv_screenUpdate);
                    return;
                } else {
                    $lv_updateResult = Teacher::updatePassword($lv_username, $currentHashPass, $newHashPass);
                    if ($lv_updateResult != 1) {
                        echo "Password Update Failed";
                        return;
                    }
                }
            }
            /* The updateCookie method returns the cookie value because it's not possible to update a cookie' value
             * and retrieve it right after. */
            $lv_cookieValue = $this->updateCookie($lv_username);
            $this->loadTeacherObjByCookieValue($lv_cookieValue);
            /* Because of the change in the cookie's value, the page has to be refreshed so the cookie's new value
             * can take place in the whole application. That is why there is 'header(Location...' and 'exit' at the bottom.
             * Since the page will be refreshed, the succeful message has to be passed to the edit_account through
             * the Session variable to update the screen. */
            $lv_screenUpdate['success_messages'] = ['Success! Update is done.'];
            $_SESSION['success_messages'] = $lv_screenUpdate;

            header("Location: ?controller=teachers&action=edit_account");
            exit;
        }
    }

    public function update_test()
    {
        $this->isSessionOpened();
        $lv_screenUpdate = array();
        $lv_empty_fields = [];
        $lv_duration = $_POST['duration'];
        $lv_semester = $_POST['semester'];
        $lv_type = $_POST['type'];
        $lv_instructions = $_POST['instructions'];
        $lv_prompt = $_POST['prompt'];
        $lv_testObj = unserialize($_SESSION['testObj']);

// ******************** DATA VALIDATION ***********************************
        if ($lv_duration == '') {
            $lv_empty_fields[] = 'duration';
        }
        if (empty($lv_instructions)) {
            $lv_empty_fields[] = 'instructions';
        }
        if (empty($lv_prompt)) {
            $lv_empty_fields[] = 'prompt';
        }
        if (!empty($lv_empty_fields)) {
            $lv_screenUpdate['errors'] = $lv_empty_fields;
            $lv_screenUpdate['error'] = 'Missing information.';
            $this->new_test($lv_screenUpdate);
        }

// ******************** UPDATE PROCESS ***********************************
        if (empty($lv_empty_fields)) {
            //Building codeFirstPart
            $lv_current_year = substr(date("Y"), 2, 3); //Last two digits of the year.
            $lv_codeFirstPart = $lv_semester . $lv_current_year . $lv_type;
            //echo $lv_testObj->id. " ". $lv_testObj->codeObj->id, $lv_codeFirstPart. " ". $lv_testObj->codeObj->lastDigits. " ". $lv_duration. " ". $lv_instructions. " ". $lv_prompt;
            $lv_updateResult = Test::updateTestByID($lv_testObj->id, $lv_testObj->codeObj->id, $lv_codeFirstPart, $lv_testObj->codeObj->lastDigits, $lv_duration, $lv_instructions, $lv_prompt);
            if ($lv_updateResult == 0 || $lv_updateResult == -1) {
                echo "Update failed";
            }
            $lv_codeObj = Code::retrieveCodeByTestID($lv_testObj->id);
            if (is_null($lv_codeObj)) {
                echo "Retrieve code failed";
            }
            $lv_testCode = $lv_codeObj->firstPart . $lv_codeObj->lastDigits;

            $lv_screenUpdate['success'] = 'Success! Test code is ' . $lv_testCode;
            $this->edit_test($lv_screenUpdate);
            exit;
        }
    }

    public function login()
    {
        //OVERLOADING LOGIN method.        
        foreach (func_get_args() as $error_messages) { //  1 parameter => error message
            $error_message = $error_messages;
            $show_error = "display:block";
        }

        $submit_login_url = "?controller=teachers&action=submit_login";
        require_once('views/teachers/login.php');
    }

    public function logout()
    {
        session_start();
        if (session_destroy()) {
            setcookie($this->cookie_name, '', time() - 5, '/');
            header("Location: ?controller=teachers&action=login");
            exit;
        }
    }

    public function submit_login()
    {
        if (!isset($_POST['teacher_username']) || !isset($_POST['teacher_password'])) {
            //header("Location: ?controller=pages&action=error");
            //exit;
            echo "problem 2";
        }

        $lv_username = $_POST['teacher_username'];
        $lv_password = $_POST['teacher_password'];
        //$lv_error_message = "";

        $lv_password = Util::encryptPassword($lv_password);
        $lv_is_valid_user = Teacher::checkUser($lv_username, $lv_password);

        if ($lv_is_valid_user) {
            $this->updateCookie($lv_username);
            header("Location: ?controller=teachers&action=main");
            exit;
        } else {
            $lv_error_messages = "Username/Password invalid!";
            $this->login($lv_error_messages);
        }
    }

    private function isSessionOpened()
    {
        $lv_isSessionOpened = isset($_COOKIE[$this->cookie_name]);
        if ($lv_isSessionOpened) {
            setcookie($this->cookie_name, $_COOKIE[$this->cookie_name], time() + $this->cookie_time_length, '/');
            (session_status() == PHP_SESSION_NONE) ? session_start() : "";
        } else {
            header("Location: ?controller=teachers&action=login");
            exit;
        }
    }

    private function updateCookie($mv_username)
    {
        $lv_cookie_value = Util::generateCookieValue();
        $lv_proc_result = Teacher::saveCookie($mv_username, $lv_cookie_value);
        if ($lv_proc_result) {
            setcookie($this->cookie_name, $lv_cookie_value, time() + $this->cookie_time_length, '/');
            return $lv_cookie_value; //This return is for the EditAccount feature.
        } else {
            //header("Location: ?controller=pages&action=error");
            //exit;
            echo "problem 3";
        }
    }

    private function loadTeacherObj()
    {
        $lv_cookie_value = $_COOKIE[$this->cookie_name];
        $lv_proc_result = Teacher::retrieveUsernameByCookie($lv_cookie_value);
        if ($lv_proc_result == '0') {
            //header("Location: ?controller=pages&action=error");
            //exit;
            echo 'No username retrieved by Cookie ';
        }
        $lv_teacherObj = Teacher::retrieveAccountByUsername($lv_proc_result);
        $lv_teacherObj->sessionID = Util::generateSessionID($lv_teacherObj->username);
        session_id($lv_teacherObj->sessionID);
        session_start();
        $_SESSION['teacherObj'] = serialize($lv_teacherObj); //Storing teacher object in a session variable.
        $_SESSION['isAdmin'] = $lv_teacherObj->isAdmin;
        $_SESSION['first_name'] = $lv_teacherObj->firstName;
        return $lv_teacherObj;
    }

    private function loadTeacherObjByCookieValue($mv_cookieValue)
    {
        $lv_cookie_value = $mv_cookieValue;
        $lv_proc_result = Teacher::retrieveUsernameByCookie($lv_cookie_value);
        if ($lv_proc_result == '0') {
            //header("Location: ?controller=pages&action=error");
            //exit;
            echo 'No username retrieved by Cookie ';
        }
        $lv_teacherObj = Teacher::retrieveAccountByUsername($lv_proc_result);
        $lv_teacherObj->sessionID = Util::generateSessionID($lv_teacherObj->username);
        session_id($lv_teacherObj->sessionID);
        //Since a new session id is created, a new session has to be started.
        if (session_status() != PHP_SESSION_NONE) {
            session_destroy();
            session_start();
        }
        $_SESSION['teacherObj'] = serialize($lv_teacherObj); //Storing teacher object in a session variable.
        $_SESSION['first_name'] = $lv_teacherObj->firstName;
        return $lv_teacherObj;
    }

    private function groupBySemester($testList)
    {
        $resultList = array();
        foreach ($testList as $test) {
            $semesterCode = $test->codeObj->firstPart;
            $semesterName = "";
            switch ($semesterCode[0]) {
                case 'W':
                    $semesterName = "Winter";
                    break;
                case 'S':
                    $semesterName = "Summer";
                    break;
                case 'F':
                    $semesterName = "Fall";
                    break;
            }
            $semesterName .= ' ' . '20' . $semesterCode[1] . $semesterCode[2]; // i.e. Winter 2017
            $resultList[$semesterName][] = $test;
        }
        return $resultList;
    }


}

?>