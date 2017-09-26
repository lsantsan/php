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

    public function createTest($fv_teacherId, $fv_codeFistPart, $fv_duration, $fv_instructions, $fv_prompt)
    {
        $sql = "CALL proc_create_test(:teacherID, :codeFirstPart, :duration, :instructions, :prompt, @testId)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':teacherID', $fv_teacherId, PDO::PARAM_INT);
        $stmt->bindParam(':codeFirstPart', $fv_codeFistPart, PDO::PARAM_STR);
        $stmt->bindParam(':duration', $fv_duration, PDO::PARAM_INT);
        $stmt->bindParam(':instructions', $fv_instructions, PDO::PARAM_STR);
        $stmt->bindParam(':prompt', $fv_prompt, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();
        // execute the second query to get result.
        $testId = $this->db->query("SELECT @testId AS result")->fetch();
        return $testId['result'];
    }
}