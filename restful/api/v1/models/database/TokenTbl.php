<?php

namespace lsantsan\model;

use \PDO;

class TokenTbl {

    private $TOKEN_LIFE_TIME = "PT60M"; //60 minutes
    private $db = null;

    public function __construct($database) {
        $this->db = $database;
    }

    public function saveToken($fv_teacher_id, $fv_access_token) {
        $sql = "CALL proc_save_token(:user_id, :access_token, @result)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':user_id', $fv_teacher_id, PDO::PARAM_STR);
        $stmt->bindParam(':access_token', $fv_access_token, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();
        // execute the second query to get result.
        $result = $this->db->query("SELECT @result AS result")->fetch();
        return $result['result'];
    }

    public function retrieveToken($fv_accessToken) {
        $sql = "CALL proc_retrieve_token(:access_token)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':access_token', $fv_accessToken, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(); 
        $stmt->closeCursor();      
        return $result; //It contains the result set. i.e userId, access_token, refresh_token, creation_date.
    }   
    
    public function getTokenLifeTime(){
        return $this->TOKEN_LIFE_TIME;
    }
   
}
