<?php

require_once($_SERVER['DOCUMENT_ROOT'] . 'selnate/models/code.php');

class Test
{

    public $id;
    public $teacherID;
    public $duration;
    public $instructions;
    public $prompt;
    public $codeObj;

    public function __construct($id, $teacherID, $duration, $instructions, $prompt, $codeObj)
    {
        $this->id = $id;
        $this->teacherID = $teacherID;
        $this->duration = $duration;
        $this->instructions = $instructions;
        $this->prompt = $prompt;
        $this->codeObj = $codeObj;
    }

    public static function retrieveTestByID($mv_testID)
    {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_test(:testID, 0)"; // '0' because only testID parameter is used.
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':testID', $mv_testID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $lv_codeObj = new Code($result['code_id'], $result['first_part'], $result['last_digits'], null);

            $lv_testObj = new Test($mv_testID, $result['teacher_id'], $result['duration']
                , $result['instructions'], $result['prompt'], $lv_codeObj);

            return $lv_testObj;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function retrieveTestByCode($mv_codeID)
    {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_test(0, :codeID)"; // '0' because only codeID parameter is used.
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':codeID', $mv_codeID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            $lv_testObj = new Test($result['id'], $result['teacher_id'], $result['duration']
                , $result['instructions'], $result['prompt'], NULL);

            return $lv_testObj;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function retrieveAllTestByTeacher($mv_teacher_id)
    {
        $lv_objList = [];
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_all_test(:teacher_id )";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':teacher_id', $mv_teacher_id, PDO::PARAM_INT);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $aRecord) {
                $lv_codeObj = new Code($aRecord['code_id'], $aRecord['first_part'], $aRecord['last_digits'], null);

                $lv_objList[] = new Test($aRecord['test_id'], $aRecord['teacher_id'], $aRecord['duration']
                    , $aRecord['instructions'], $aRecord['prompt'], $lv_codeObj);
            }
            return $lv_objList;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function createTest($mv_teacherID, $mv_codeFirstPart, $mv_duration, $mv_instructions, $mv_prompt)
    {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_create_test(:teacherID, :codeFirstPart, :duration, :instructions, :prompt, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':teacherID', $mv_teacherID, PDO::PARAM_INT);
            $stmt->bindParam(':codeFirstPart', $mv_codeFirstPart, PDO::PARAM_STR);
            $stmt->bindParam(':duration', $mv_duration, PDO::PARAM_INT);
            $stmt->bindParam(':instructions', $mv_instructions, PDO::PARAM_STR);
            $stmt->bindParam(':prompt', $mv_prompt, PDO::PARAM_STR);
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

    public static function removeTestByID($mv_teacherID, $mv_testID)
    {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_remove_test(0, :teacherID, :testID, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':teacherID', $mv_teacherID, PDO::PARAM_INT);
            $stmt->bindParam(':testID', $mv_testID, PDO::PARAM_INT);
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

    public static function updateTestByID($mv_testID, $mv_codeID, $mv_codeFirstPart, $mv_codeLastDigits, $mv_duration, $mv_instructions, $mv_prompt)
    {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_update_test(:testID, :codeID, :codeFirstPart, :codeLastDigits, :duration, :instructions, :prompt, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':testID', $mv_testID, PDO::PARAM_INT);
            $stmt->bindParam(':codeID', $mv_codeID, PDO::PARAM_INT);
            $stmt->bindParam(':codeFirstPart', $mv_codeFirstPart, PDO::PARAM_STR);
            $stmt->bindParam(':codeLastDigits', $mv_codeLastDigits, PDO::PARAM_INT);
            $stmt->bindParam(':duration', $mv_duration, PDO::PARAM_INT);
            $stmt->bindParam(':instructions', $mv_instructions, PDO::PARAM_STR);
            $stmt->bindParam(':prompt', $mv_prompt, PDO::PARAM_STR);
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
