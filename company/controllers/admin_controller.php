<?php

require_once('controllers/util.php');
require_once('controllers/teachers_controller.php');
require_once('models/test.php');
require_once('models/code.php');
require_once('models/essay.php');
require_once('models/teacher.php');

class AdminController {

    private $cookie_time_length = 60 * 60;
    private $cookie_name = 'selnate_cookie';
    private $default_password; //Initialized on constructor.

    public function __construct() {
        $this->logo_link = '?controller=admin&action=main';
        $this->my_account_link = '?controller=teachers&action=edit_account';
        $this->logout_link = '?controller=teachers&action=logout';
        $this->admin_or_teacher_link = '?controller=teachers&action=main';
        $this->default_password = Util::getDefaultPassword();
    }

    public function main() {
        $create_account_url = "?controller=admin&action=new_account";
        $view_teacher_url = "";
        $edit_teacher_url = "?controller=admin&action=edit_teacher_account";
        $remove_teacher_url = "?controller=admin&action=remove_account";

        $this->isSessionOpened();
        $lv_teacherObj = unserialize($_SESSION['teacherObj']);

        $lv_teacher_list = Teacher::retrieveAllTeacherByAdminID($lv_teacherObj->id);

        require_once('views/admin/main.php');
    }

    public function new_account() {
        $create_account_url = "?controller=admin&action=create_account";

//OVERLOADING NEW_ACCOUNT method.        
        foreach (func_get_args() as $screenUpdate) { //  1 parameter => screenUpdate[]
            if (array_key_exists('error', $screenUpdate)) {
                $message_type = "error_message";
                $message = $screenUpdate['error'];
            }
            if (array_key_exists('errors', $screenUpdate)) {
                $lv_empty_inputs = $screenUpdate['errors'];
                foreach ($lv_empty_inputs as $input) {
                    switch ($input) {
                        case 'username':
                            $username_error = "style='color:red;'";
                            break;
                        case 'firstName':
                            $firstName_error = "style='color:red;'";
                            break;
                        case 'lastName':
                            $lastName_error = "style='color:red;'";
                            break;
                        default:
                            $username_error = $firstName_error = $lastName_error = "";
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


        $this->isSessionOpened();
        require_once('views/admin/new_account.php');
    }

    public function create_account() {
        $this->isSessionOpened();
        $lv_screenUpdate = array();
        $lv_empty_fields = [];

        $lv_firstName = $_POST['first_name'];
        $lv_lastName = $_POST['last_name'];
        $lv_username = $_POST['username'];
        $lv_isAdmin = $_POST['is_admin'];
        $lv_password = Util::encryptPassword($this->default_password);
        $lv_teacherObj = unserialize($_SESSION['teacherObj']);

        if ($lv_username == '') {
            $lv_empty_fields[] = 'username';
        }
        if ($lv_firstName == '') {
            $lv_empty_fields[] = 'firstName';
        }
        if ($lv_lastName == '') {
            $lv_empty_fields[] = 'lastName';
        }
        if (!empty($lv_empty_fields)) {
            $lv_screenUpdate['errors'] = $lv_empty_fields;
            $lv_screenUpdate['error'] = 'Missing information.';
            $this->new_account($lv_screenUpdate);
        }

        if (empty($lv_empty_fields)) {
            $lv_firstName = ucwords($lv_firstName); //Making uppercase first letter of each word.
            $lv_lastName = ucwords($lv_lastName); //Making uppercase first letter of each word.
            $lv_username = strtolower($lv_username); //Making username all lower case.
            $lv_insertResult = Teacher::createTeacherAccount($lv_teacherObj->id, $lv_username, $lv_firstName, $lv_lastName, $lv_password, $lv_isAdmin);
            switch ($lv_insertResult) {
                case 0:
                    $lv_screenUpdate['error'] = 'Sorry, internal error.';
                    break;
                case -1:
                    $lv_screenUpdate['error'] = 'Sorry, you do not have admin privileges.';
                    break;
                case -2:
                    $lv_screenUpdate['error'] = 'Sorry, this account already exits.';
                    break;
                default:
                    $lv_screenUpdate['success'] = 'Success! Default password is: ' . $this->default_password;
            }
            $this->new_account($lv_screenUpdate);
            exit;
        }
    }

    public function remove_account() {
        if (!is_numeric($_GET['id'])) {
            echo 'TeacherID is not numeric';
        }
        $this->isSessionOpened();
        $lv_teacherObj = unserialize($_SESSION['teacherObj']);
        $lv_removedTeacherID = $_GET['id'];

        $lv_result = Teacher::removeTeacherAccountByID($lv_teacherObj->id, $lv_removedTeacherID);
        if ($lv_result != 1) {
            echo 'Remove Failed!';
        }
        header("Location: ?controller=admin&action=main");
        exit;
    }

    public function view_essays() {
        $view_essays_url = "?controller=teachers&action=view_essays";
        $this->isSessionOpened();

        if (isset($_GET['eid'])) { //Essay id
            if (!is_numeric($_GET['eid'])) {
                echo 'EssayID is not numeric';
            }
            $lv_teacherObj = unserialize($_SESSION['teacherObj']);
            $lv_essayObj_list = Essay::retrieveEssayByID([$_GET['eid']]);
            $lv_essayObj = $lv_essayObj_list[0];
            $lv_testObj = Test::retrieveTestByCode($lv_essayObj->codeObj->id);
        }

        if (!is_numeric($_GET['id'])) { //Code id
            echo 'CodeID is not numeric';
        }

        $lv_codeID = $_GET['id'];
        $lv_studentList = Essay::retrieveStudentListByCodeID($lv_codeID);
        if (empty($lv_studentList)) {
            echo "Empty List!";
        }

        require_once('views/teachers/view_essays.php');
    }

    public function edit_teacher_account() {
        $this->isSessionOpened();
        $update_teacher_account_url = "?controller=admin&action=update_teacher_account";
        $lv_adminObj = unserialize($_SESSION['teacherObj']);


//OVERLOADING EDIT_ACCOUNT method.        
        foreach (func_get_args() as $screenUpdate) { //  1 parameter => screenUpdate[]
            if (empty($screenUpdate)) {
                echo "ScreenUpdate is empty";
            }
            foreach ($screenUpdate as $key => $arrayValue) {
                switch ($key) {
                    case 'success_messages':
                        $lv_message_type = "success_message";
                        $lv_messages = $arrayValue;
                        $lv_isDisabled = "disabled";
                        echo "";
                        break;
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
                            }
                        }
                        break; //For FIELDS case.
                    case 'id':
                        $lv_teacherID = $arrayValue;
                        break;
                }
            }

            $show_message = "style='display:block;'";
        }

        /*         * *************** LOAD PAGE ***************************   */
//The teacher_id can be defined by GET or by $screenUpdate 
        if (isset($_GET['id'])) {
            if (is_numeric($_GET['id'])) {
                $lv_teacherID = $_GET['id'];
            } else {
                echo 'TeacherID is not numeric';
                exit;
            }
        }

        $lv_teacherObj = Teacher::retrieveAccountByID($lv_teacherID);
        if (empty($lv_teacherObj) || $lv_teacherObj == NULL) {
            echo "TeacherObj is null";
            exit;
        }
        require_once('views/admin/edit_teacher_account.php');
    }

    public function update_teacher_account() {
        $this->isSessionOpened();
        $lv_screenUpdate = array();
        $lv_empty_fields = [];
        $lv_error_messages = [];
        $lv_firstName = $_POST['first_name'];
        $lv_lastName = $_POST['last_name'];
        $lv_username = $_POST['username'];
        $lv_teacherID = $_POST['teacher_id'];
        $lv_adminObj = unserialize($_SESSION['teacherObj']);

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
//Checks if there was any empty field. If so, sets up the error message.
        if (!empty($lv_empty_fields)) {
            $lv_screenUpdate['fields'] = $lv_empty_fields;
            $lv_error_messages[] = 'Information was missing.';
            $lv_screenUpdate['error_messages'] = $lv_error_messages;
        }

// ******************** UPDATE PROCESS ***********************************    
        if (empty($lv_screenUpdate)) {
            $lv_updateResult = Teacher::updateAccount($lv_adminObj->id, $lv_teacherID, $lv_username, $lv_firstName, $lv_lastName);
            if ($lv_updateResult != 1) {
                echo $lv_updateResult . " Info Update Failed!";
            }
            $lv_screenUpdate['success_messages'] = ['Success! Update is done.'];
        }

//Passes the teacher_id so the page can be loaded again.
        $lv_screenUpdate['id'] = $lv_teacherID;
//Checks if there was any error to be displayed on screen. If so, sends message back to interface.
        if (!empty($lv_screenUpdate)) {
            $this->edit_teacher_account($lv_screenUpdate);
        }
    }

    public function login() {
//OVERLOADING LOGIN method.        
        foreach (func_get_args() as $error_messages) { //  1 parameter => error message
            $error_message = $error_messages;
            $show_error = "display:block";
        }

        $submit_login_url = "?controller=teachers&action=submit_login";
        require_once('views/teachers/login.php');
    }

    public function logout() {
        session_start();
        if (session_destroy()) {
            setcookie($this->cookie_name, '', time() - 5, '/');
            header("Location: ?controller=teachers&action=login");
            exit;
        }
    }

    private function isSessionOpened() {
        $lv_isSessionOpened = isset($_COOKIE[$this->cookie_name]);
        if ($lv_isSessionOpened) {
            setcookie($this->cookie_name, $_COOKIE[$this->cookie_name], time() + $this->cookie_time_length, '/');
            (session_status() == PHP_SESSION_NONE) ? session_start() : "";
        } else {
            header("Location: ?controller=teachers&action=login");
            exit;
        }
    }

}

?>