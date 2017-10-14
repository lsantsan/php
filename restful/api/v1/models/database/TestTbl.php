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
        $stmt->bindParam(':testTypeId', $fv_testObj->semesterId, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();
        // execute the second query to get result.
        $testId = $this->db->query("SELECT @testId AS result")->fetch();
        $fv_testObj->id = $testId;
        return $fv_testObj;
    }

    public function getTestByTestId($fv_testId)
    {
        $sql = "CALL proc_retrieve_test(:testId, 0)"; // optional parameters: 0 for codeId
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':testId', $fv_testId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        $stmt->closeCursor();
        return $result;
    }

    public function updateTest($fv_testId, $fv_codeId, $fv_codeFirstPart, $fv_codeLastDigits, $fv_duration, $fv_instructions, $fv_prompt)
    {
        $sql = "CALL proc_update_test(:testId, :codeId, :codeFirstPart, :codeLastDigits, :duration, :instructions, :prompt, @result)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':testId', $fv_testId, PDO::PARAM_INT);
        $stmt->bindParam(':codeId', $fv_codeId, PDO::PARAM_INT);
        $stmt->bindParam(':codeFirstPart', $fv_codeFirstPart, PDO::PARAM_STR);
        $stmt->bindParam(':codeLastDigits', $fv_codeLastDigits, PDO::PARAM_INT);
        $stmt->bindParam(':duration', $fv_duration, PDO::PARAM_INT);
        $stmt->bindParam(':instructions', $fv_instructions, PDO::PARAM_STR);
        $stmt->bindParam(':prompt', $fv_prompt, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();
        // execute the second query to get result.
        $result = $this->db->query("SELECT @result AS result")->fetch();
        return $result['result'];
    }
}