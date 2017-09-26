<?php

namespace lsantsan\model;

use \PDO;

require_once(__DIR__ . '/../Teacher.php');
class TeacherTbl
{

    private $db = null;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function checkTeacher($fv_username, $fv_password)
    {
        $sql = "CALL proc_check_user(:usrname, :pswd, @result)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':usrname', $fv_username, PDO::PARAM_STR);
        $stmt->bindParam(':pswd', $fv_password, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();
        // execute the second query to get result.
        $result = $this->db->query("SELECT @result AS result")->fetch();
        return $result['result'];
    }

    public function retrieveTeacherByUsername($fv_username)
    {
        $sql = "CALL proc_retrieve_account_by_username(:username)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':username', $fv_username, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();
        return $result;
    }

    public function retrieveTeacherById($fv_teacherId)
    {
        $sql = "CALL proc_retrieve_account_by_id(:userId)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':userId', $fv_teacherId, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();
        return $result;
    }

    public function isTeacherAdmin($fv_userId)
    {
        $sql = "SELECT func_check_admin_status(:user_id) AS result";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $fv_userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();
        return $result['result']; //Returns 0 or 1.
    }

    public function createTeacherAccount($fv_adminId, $fv_username, $fv_firstName, $fv_lastName, $fv_password, $fv_isAdmin)
    {
        $lv_isAdmin = ($fv_isAdmin) ? 1 : 0;
        $sql = "CALL proc_create_teacher_account(:adminId, :username, :firstName, :lastName,  :password, :isAdmin, @result)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':adminId', $fv_adminId, PDO::PARAM_INT);
        $stmt->bindParam(':username', $fv_username, PDO::PARAM_STR);
        $stmt->bindParam(':firstName', $fv_firstName, PDO::PARAM_STR);
        $stmt->bindParam(':lastName', $fv_lastName, PDO::PARAM_STR);
        $stmt->bindParam(':password', $fv_password, PDO::PARAM_STR);
        $stmt->bindParam(':isAdmin', $lv_isAdmin, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();
        // execute the second query to get result.
        $result = $this->db->query("SELECT @result AS result")->fetch();
        return $result['result'];
    }

    public function updateTeacherAccount($fv_adminId, $fv_teacherId, $fv_username, $fv_firstName, $fv_lastName, $fv_isAdmin)
    {
        $sql = "CALL proc_update_account(:adminId, :teacherId, :username, :firstName, :lastName, :isAdmin, @result)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':adminId', $fv_adminId, PDO::PARAM_INT);
        $stmt->bindParam(':teacherId', $fv_teacherId, PDO::PARAM_INT);
        $stmt->bindParam(':username', $fv_username, PDO::PARAM_STR);
        $stmt->bindParam(':firstName', $fv_firstName, PDO::PARAM_STR);
        $stmt->bindParam(':lastName', $fv_lastName, PDO::PARAM_STR);
        $stmt->bindParam(':isAdmin', $fv_isAdmin, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();
        // execute the second query to get result.
        $result = $this->db->query("SELECT @result AS result")->fetch();
        return $result['result'];
    }

    public function updateTeacherPassword($fv_teacherId, $fv_currentPassword, $fv_newPassword)
    {
        $sql = "CALL proc_update_password(:userId, :currentPassword, :newPassword, @result)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':userId', $fv_teacherId, PDO::PARAM_INT);
        $stmt->bindParam(':currentPassword', $fv_currentPassword, PDO::PARAM_INT);
        $stmt->bindParam(':newPassword', $fv_newPassword, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();
        // execute the second query to get result.
        $result = $this->db->query("SELECT @result AS result")->fetch();
        return $result['result'];
    }

    public function getAllTeachers($fv_adminId)
    {
        $sql = "CALL proc_retrieve_all_teacher_account(:adminId)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':adminId', $fv_adminId, PDO::PARAM_INT);
        $stmt->execute();
        foreach ($stmt->fetchAll() as $teacher) {
            if ($teacher['is_active'] == '1') {
                $list[] = new Teacher(
                    $teacher['id'],
                    $teacher['username'],
                    $teacher['first_name'],
                    $teacher['last_name'],
                    $teacher['is_admin'],
                    $teacher['is_active'],
                    $teacher['reset_password'],
                    $teacher['creation_date']
                );
            }
        }
        $stmt->closeCursor();
        return $list;
    }

    public function getTeacherById($fv_teacher_id)
    {
        $sql = "CALL proc_retrieve_account_by_id(:teacherId)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':teacherId', $fv_teacher_id, PDO::PARAM_INT);
        $stmt->execute();
        $dbTeacher = $stmt->fetch();
        $teacher = new Teacher(
            $dbTeacher['id'],
            $dbTeacher['username'],
            $dbTeacher['first_name'],
            $dbTeacher['last_name'],
            $dbTeacher['is_admin'],
            $dbTeacher['is_active'],
            $dbTeacher['reset_password'],
            $dbTeacher['creation_date']);
        $stmt->closeCursor();
        return $teacher;
    }

    public function getTeacherByUsername($fv_teacher_username)
    {
        $sql = "CALL proc_retrieve_account_by_username(:teacherUsername)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':teacherUsername', $fv_teacher_username, PDO::PARAM_INT);
        $stmt->execute();
        $dbTeacher = $stmt->fetch();
        $teacher = new Teacher(
            $dbTeacher['id'],
            $dbTeacher['username'],
            $dbTeacher['first_name'],
            $dbTeacher['last_name'],
            $dbTeacher['is_admin'],
            $dbTeacher['is_active'],
            $dbTeacher['reset_password'],
            $dbTeacher['creation_date']);
        $stmt->closeCursor();
        return $teacher;
    }

}
