<?php

// require("C:\wamp64\db_config\dbconnection.php");

class Teacher {

    public $id;
    public $username;
    public $firstName;
    public $lastName;
    public $isAdmin;
    public $isActive;
    public $sessionID;
    public $resetPassword;

    public function __construct($id, $username, $firstName, $lastName, $isAdmin, $isActive, $resetPassword) {
        $this->id = $id;
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->isAdmin = $isAdmin;
        $this->isActive = $isActive;
        $this->resetPassword = $resetPassword;
    }

    public static function checkUser($mv_username, $mv_password) {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_check_user(:usrname, :pswd, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':usrname', $mv_username, PDO::PARAM_STR);
            $stmt->bindParam(':pswd', $mv_password, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();
            // execute the second query to get result.
            $result = $link->query("SELECT @result AS result")->fetch();
            //echo $result['result'];
            return $result['result'];
        } catch (PDOException $ex) {
            echo "   " . $ex->getMessage();
            return FALSE;
        }
    }

    public static function saveCookie($mv_teacher_username, $mv_cookie_value) {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_save_cookie(:usrname, :cookie_value, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':usrname', $mv_teacher_username, PDO::PARAM_STR);
            $stmt->bindParam(':cookie_value', $mv_cookie_value, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();
            // execute the second query to get result.
            $result = $link->query("SELECT @result AS result")->fetch();
            //echo $result['result'];
            return $result['result'];
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function retrieveUsernameByCookie($mv_cookie_value) {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_cookie(:cookie_value)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':cookie_value', $mv_cookie_value, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['cookie_username'];
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function retrieveAccountByUsername($mv_username) {
        $lv_accountID = '0';
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_account(:accountID,:username )";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':accountID', $lv_accountID, PDO::PARAM_STR);
            $stmt->bindParam(':username', $mv_username, PDO::PARAM_STR);
            $stmt->execute();
            $teacher = $stmt->fetch();
            $obj = new Teacher($teacher['id'], $teacher['username'], $teacher['first_name']
                    , $teacher['last_name'], $teacher['is_admin'], $teacher['is_active'], $teacher['reset_password']);
            return $obj;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function retrieveAccountByID($mv_teacherID) {
        $lv_username = '0';
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_account(:accountID,:username )";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':accountID', $mv_teacherID, PDO::PARAM_STR);
            $stmt->bindParam(':username', $lv_username, PDO::PARAM_STR);
            $stmt->execute();
            $teacher = $stmt->fetch();
            $obj = new Teacher($teacher['id'], $teacher['username'], $teacher['first_name']
                    , $teacher['last_name'], $teacher['is_admin'], $teacher['is_active'], $teacher['reset_password']);
            return $obj;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function retrieveAllTestByTeacher($mv_teacher_id) {
        $list = [];
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_all_test(:teacher_id )";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':teacher_id', $mv_teacher_id, PDO::PARAM_INT);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $test) {
                $list[] = new Test($test['id'], $test['author'], $test['content']);
            }
            $obj = new Teacher($teacher['id'], $teacher['username'], $teacher['first_name']
                    , $teacher['last_name'], $teacher['is_admin'], $teacher['is_active'], $teacher['reset_password']);
            return $obj;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function retrieveAllTeacherByAdminID($mv_admin_id) {
        $list = [];
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_all_teacher_account(:admin_id )";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':admin_id', $mv_admin_id, PDO::PARAM_INT);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $teacher) {
                if($teacher['is_active'] == '1') {
                    $list[] = new Teacher($teacher['id'], $teacher['username'], $teacher['first_name'], 
                            $teacher['last_name'], $teacher['is_admin'], $teacher['is_active'], $teacher['reset_password']);
                }
            }
            return $list;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function updateAccount($mv_adminID, $mv_teacherID, $mv_username, $mv_firstName, $mv_lastName) {
        $link = Db::getInstance();
        try {
            
            $sql = "CALL proc_update_account(:adminID, :teacherID, :username, :firstName, :lastName, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':adminID', $mv_adminID, PDO::PARAM_INT);
            $stmt->bindParam(':teacherID', $mv_teacherID, PDO::PARAM_INT);
            $stmt->bindParam(':username', $mv_username, PDO::PARAM_STR);
            $stmt->bindParam(':firstName', $mv_firstName, PDO::PARAM_STR);
            $stmt->bindParam(':lastName', $mv_lastName, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();
            // execute the second query to get result.
            $result = $link->query("SELECT @result AS result")->fetch();
            //echo $result['result'];
            return $result['result'];
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function updatePassword($mv_username, $mv_current_password, $mv_new_password) {
        $link = Db::getInstance();
        try {
            //This proc has a hardcoded '0' because adminID is not being used.
            $sql = "CALL proc_update_password(:username, :currentPassword, :newPassword, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':username', $mv_username, PDO::PARAM_STR);
            $stmt->bindParam(':currentPassword', $mv_current_password, PDO::PARAM_STR);
            $stmt->bindParam(':newPassword', $mv_new_password, PDO::PARAM_STR);
            $stmt->execute();
            $stmt->closeCursor();
            // execute the second query to get result.
            $result = $link->query("SELECT @result AS result")->fetch();
            //echo $result['result'];
            return $result['result'];
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function createTeacherAccount($mv_adminID, $mv_username, $mv_firstName, $mv_lastName, $mv_password, $mv_isAdmin) {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_create_teacher_account(:adminID, :username, :firstName, :lastName, :password, :isAdmin, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':adminID', $mv_adminID, PDO::PARAM_INT);
            $stmt->bindParam(':username', $mv_username, PDO::PARAM_STR);
            $stmt->bindParam(':firstName', $mv_firstName, PDO::PARAM_STR);
            $stmt->bindParam(':lastName', $mv_lastName, PDO::PARAM_STR);
            $stmt->bindParam(':password', $mv_password, PDO::PARAM_STR);
            $stmt->bindParam(':isAdmin', $mv_isAdmin, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
            // execute the second query to get result.
            $result = $link->query("SELECT @result AS result")->fetch();
            //echo $result['result'];
            return $result['result'];
        } catch (PDOException $ex) {
            return FALSE;
        }
    }
    
    public static function removeTeacherAccountByID($mv_adminID, $mv_teacherID) {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_remove_teacher_account(:adminID, :teacherID, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':adminID', $mv_adminID, PDO::PARAM_INT);
            $stmt->bindParam(':teacherID', $mv_teacherID, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
            // execute the second query to get result.
            $result = $link->query("SELECT @result AS result")->fetch();
            
            return $result['result'];
        } catch (PDOException $ex) {
            return FALSE;
        }
    }
    
    public static function resetTeacherPassword($mv_adminID, $mv_teacherID, $mv_defaultPassword) {
        $link = Db::getInstance();
        try {
            //This proc has a hardcoded '0' because adminID is not being used.
            $sql = "CALL proc_reset_teacher_password(:adminID, :teacherID, :defaultPassword, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':adminID', $mv_adminID, PDO::PARAM_INT);
            $stmt->bindParam(':teacherID', $mv_teacherID, PDO::PARAM_INT);            
            $stmt->bindParam(':defaultPassword', $mv_defaultPassword, PDO::PARAM_STR);   
            $stmt->execute();
            $stmt->closeCursor();
            // execute the second query to get result.
            $result = $link->query("SELECT @result AS result")->fetch();
            //echo $result['result'];
            return $result['result'];
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

}

// checkUser("admin", "$3ZXPWTdAcRh2");


