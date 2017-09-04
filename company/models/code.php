<?php

class Code {

    public $id;
    public $firstPart;
    public $lastDigits;
    public $isActive;

    public function __construct($id, $firstPart, $lastDigits, $isActive) {
        $this->id = $id;
        $this->firstPart = $firstPart;
        $this->lastDigits = $lastDigits;
        $this->isActive = $isActive;
    }

    public static function retrieveCodeByID($mv_codeID) {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_code(:codeID, '0', 0)"; //There are zeros because the retrieve is by CodeId.
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':codeID', $mv_codeID, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();

            if (empty($result)) {
                return NULL;
            }

            $lv_codeObj = new Code($mv_codeID, $result['first_part'], $result['last_digits'], $result['is_active']);
            return $lv_codeObj;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }
    
    public static function retrieveCodeByString($mv_codeFirstPart, $mv_codeLastDigits) {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_code(0, :firstPart, :lastDigits)"; //There is a zero because the retrieve is by code value. Ex: S16M 443.
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':firstPart', $mv_codeFirstPart, PDO::PARAM_STR);
            $stmt->bindParam(':lastDigits', $mv_codeLastDigits, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            if (empty($result)) {
                return NULL;
            }
            $lv_codeObj = new Code($result['id'], $mv_codeFirstPart, $mv_codeLastDigits, $result['is_active']);
            
            return $lv_codeObj;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function retrieveCodeByTestID($mv_testID) {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_test(:testID, 0)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':testID', $mv_testID, PDO::PARAM_INT);
            $stmt->execute();
            $testResult = $stmt->fetch();
            if (empty($testResult)) {
                return FALSE;
            }
            $lv_codeID = $testResult['code_id'];
            $stmt->closeCursor();

            $lv_codeObj = Code::retrieveCodeByID($lv_codeID);
            return $lv_codeObj;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function checkCode($mv_codeFirstPart, $mv_codeLastDigits) {
        $link = Db::getInstance();        
        try {
            
            $sql = "CALL proc_check_code(:firstPart, :lastDigits)";
            $stmt = $link->prepare($sql);            
            $stmt->bindParam(':firstPart', $mv_codeFirstPart, PDO::PARAM_STR);
            $stmt->bindParam(':lastDigits', $mv_codeLastDigits, PDO::PARAM_INT);            
            $stmt->execute();            
            $codeResult = $stmt->fetch();
             if (empty($codeResult)) {
                return FALSE;
            }
             $lv_codeID = $codeResult['id'];
             $stmt->closeCursor();
             
             return $lv_codeID;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

}
