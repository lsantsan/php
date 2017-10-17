<?php

namespace lsantsan\model;

use \PDO;

class TestTbl
{
    private $db = null;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function createTest($fv_testObj)
    {
        $sql = "CALL proc_create_test(:teacherId, :duration, :instructions, :prompt, :semesterId, :testTypeId, @testId)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':teacherId', $fv_testObj->teacherId, PDO::PARAM_INT);
        $stmt->bindParam(':duration', $fv_testObj->duration, PDO::PARAM_INT);
        $stmt->bindParam(':instructions', $fv_testObj->instructions, PDO::PARAM_STR);
        $stmt->bindParam(':prompt', $fv_testObj->prompt, PDO::PARAM_STR);
        $stmt->bindParam(':semesterId', $fv_testObj->semesterId, PDO::PARAM_INT);
        $stmt->bindParam(':testTypeId', $fv_testObj->testTypeId, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();
        // execute the second query to get result.
        $testId = $this->db->query("SELECT @testId AS result")->fetch();
        $fv_testObj->id = $testId['result'];
        return $fv_testObj;
    }

    public function getTestByTestId($fv_testId)
    {
        $sql = "CALL proc_retrieve_test(:testId, 0)"; // optional parameters: 0 for codeId
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':testId', $fv_testId, PDO::PARAM_INT);
        $stmt->execute();
        $dbResult = $stmt->fetch();
        if ($dbResult == null ||
            (isset($dbResult['result']) && $dbResult['result'] == 0)) {
            return null;
        }
        $testObj = new Test(
            $dbResult['teacher_id'],
            $dbResult['duration'],
            $dbResult['instructions'],
            $dbResult['prompt'],
            $dbResult['semester_id'],
            $dbResult['test_type_id'],
            $dbResult['id'],
            $dbResult['code_id'],
            $dbResult['is_active'],
            $dbResult['creation_date']);
        $stmt->closeCursor();
        return $testObj;
    }

    public function updateTest($fv_testObj)
    {
        $sql = "CALL proc_update_test(:testId, :teacherId, :duration, :instructions, :prompt, :semesterId, :testTypeId, @result)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':testId', $fv_testObj->id, PDO::PARAM_INT);
        $stmt->bindParam(':teacherId', $fv_testObj->teacherId, PDO::PARAM_INT);
        $stmt->bindParam(':duration', $fv_testObj->duration, PDO::PARAM_INT);
        $stmt->bindParam(':instructions', $fv_testObj->instructions, PDO::PARAM_STR);
        $stmt->bindParam(':prompt', $fv_testObj->prompt, PDO::PARAM_STR);
        $stmt->bindParam(':semesterId', $fv_testObj->semesterId, PDO::PARAM_STR);
        $stmt->bindParam(':testTypeId', $fv_testObj->testTypeId, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();
        // execute the second query to get result.
        $result = $this->db->query("SELECT @result AS result")->fetch();
        return $result['result'];
    }
}