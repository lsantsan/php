<?php

class Essay {

    public $id;
    public $codeObj;
    public $studentName; //List
    public $content;
    public $timeSpent;
    public $wordCount;
    public $teacherName;
    public $creationDate;

    public function __construct($id, $codeObj, $studentName, $content, $timeSpent, $wordCount, $teacherName, $creationDate) {
        $this->id = $id;
        $this->codeObj = $codeObj;
        $this->studentName = $studentName;
        $this->content = $content;
        $this->timeSpent = $timeSpent;
        $this->wordCount = $wordCount;
        $this->teacherName = $teacherName;
        $this->creationDate = $creationDate;
    }

    public static function retrieveStudentListByCodeID($mv_codeID) {
        $lv_nameList = array();
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_student_list(:codeID )";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':codeID', $mv_codeID, PDO::PARAM_INT);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $aRecord) {
                $lv_nameList[$aRecord['essay_id']] = $aRecord['student_name'];
            }
            return $lv_nameList;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }
    
     public static function retrieveNameIDListByCodeID($mv_codeID) {
        $lv_nameIDList = array();
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_nameid_list(:codeID )";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':codeID', $mv_codeID, PDO::PARAM_INT);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $aRecord) {
                $lv_nameIDList[$aRecord['name_id']] = $aRecord['student_name'];
            }
            return $lv_nameIDList;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function retrieveEssayByID($mv_essayIDList) {
        $link = Db::getInstance();
        $lv_objList = [];
        $lv_idArrayStr = ""; //For the database, the list has to be an string. Ex: '1,2,4'
        foreach ($mv_essayIDList as $id) {
            (empty($lv_idArrayStr)) ? $lv_idArrayStr = $id : $lv_idArrayStr = $lv_idArrayStr . ',' . $id;
        }
        try {
            $sql = "CALL proc_retrieve_essay(:essayIDArray )";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':essayIDArray', $lv_idArrayStr, PDO::PARAM_STR);
            $stmt->execute();
            foreach ($stmt->fetchAll() as $aRecord) {
                $lv_codeObj = new Code($aRecord['code_id'], $aRecord['first_part'], $aRecord['last_digits'], null);

                $lv_objList[] = new Essay($aRecord['essay_id'], $lv_codeObj, $aRecord['student_name']
                        , $aRecord['essay_content'], $aRecord['time_spent'], $aRecord['word_count'], null, $aRecord['creation_date']);
            }
            return $lv_objList;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function createEssay($mv_codeID, $mv_studentName, $mv_essayContent, $mv_timeSpent, $mv_wordCount) {
        $link = Db::getInstance();
        try {
            //CREATE ESSAY ON TBL_ESSAY.
            $sql = "CALL proc_create_essay(:codeID, :studentName, :essayContent, :timeSpent, :wordCount, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':codeID', $mv_codeID, PDO::PARAM_INT);
            $stmt->bindParam(':studentName', $mv_studentName, PDO::PARAM_STR);
            $stmt->bindParam(':essayContent', $mv_essayContent, PDO::PARAM_STR);
            $stmt->bindParam(':timeSpent', $mv_timeSpent, PDO::PARAM_STR);
            $stmt->bindParam(':wordCount', $mv_wordCount, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
            // execute the second query to get result.
            $result = $link->query("SELECT @result AS result")->fetch();
            
            //If the insert process had a problem, leave this method.
            if(empty($result)){
               return $result['result']; 
            }
            //CREATE RANDOM NUMBER ON TBL_NAME_ID FOR THE "HIDDEN NAME" FEATURE.
            $sql = "CALL proc_create_nameid(:codeID, :studentName, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':codeID', $mv_codeID, PDO::PARAM_INT);
            $stmt->bindParam(':studentName', $mv_studentName, PDO::PARAM_STR);           
            $stmt->execute();
            $stmt->closeCursor();
            //If both procs ran ok, return Essay ID.
            return $result['result'];
            
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function saveCookie($mv_studentName, $mv_testCode, $mv_cookieValue) {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_save_cookie_student(:studentName, :testCode, :cookieValue, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':studentName', $mv_studentName, PDO::PARAM_STR);
            $stmt->bindParam(':testCode', $mv_testCode, PDO::PARAM_STR);
            $stmt->bindParam(':cookieValue', $mv_cookieValue, PDO::PARAM_STR);
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

    public static function retrieveStudentNameByCookie($mv_cookieValue) {
        $link = Db::getInstance();
        try {
            $sql = "CALL proc_retrieve_cookie_student(:cookie_value)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':cookie_value', $mv_cookieValue, PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch();
            $lv_arrayResult = [$result['cookie_student_name'], $result['cookie_test_code']];
            return $lv_arrayResult;
        } catch (PDOException $ex) {
            return FALSE;
        }
    }

    public static function saveEssay($mv_saveID, $mv_studentName, $mv_prompt, $mv_essayContent, $mv_timePassed) {
        $link = Db::getInstance();
        $lv_id = (empty($mv_saveID) || $mv_saveID == 0)? 0 : $mv_saveID ;
        try {
            $sql = "CALL proc_save_essay(:id, :studentName, :prompt, :essayContent, :timePassed, @result)";
            $stmt = $link->prepare($sql);
            $stmt->bindParam(':id', $lv_id, PDO::PARAM_INT);
            $stmt->bindParam(':studentName', $mv_studentName, PDO::PARAM_STR);
            $stmt->bindParam(':prompt', $mv_prompt, PDO::PARAM_STR);
            $stmt->bindParam(':essayContent', $mv_essayContent, PDO::PARAM_STR);
            $stmt->bindParam(':timePassed', $mv_timePassed, PDO::PARAM_STR);
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
