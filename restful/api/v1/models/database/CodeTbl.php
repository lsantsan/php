<?php

namespace lsantsan\model;

use \PDO;

require_once(__DIR__ . '/../Code.php');
class CodeTbl
{
    private $db = null;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function getCodeByTestId($fv_testId)
    {
        $sql = "CALL proc_retrieve_code_by_test_id(:testId)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':testId', $fv_testId, PDO::PARAM_INT);
        $stmt->execute();
        $dbResult = $stmt->fetch();
        $code = new Code(
            $dbResult['first_part'],
            $dbResult['last_digits'],
            $dbResult['is_active']);
        $stmt->closeCursor();
        return $code;
    }
}