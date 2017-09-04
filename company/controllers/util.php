<?php

class Util {

    public function __construct() {
        
    }

    public static function encryptPassword($mv_password) {
        $salt = "$31n@t3";
        $hash = crypt($mv_password, $salt);
        return $hash;
    }
    
    public static function generateCookieValue(){
        $lv_random_value = sha1(mt_rand());
        return $lv_random_value;
    }
    
    public static function generateSessionID($username){
        return sha1($username);
    }
    
    public static function validateStudentName($mv_string){
        $lv_string = trim($mv_string);
        $lv_string = ucwords(strtolower($lv_string)); // Uppercase the first character of each word.
        $illegal = "#$%&*()+=-[]';,./\{}|:<>!?~";
        if (strpbrk($lv_string, $illegal)) {
            return FALSE; // Returns FALSE if name has illegal character;
        }
        return $lv_string;
    }
    
    public static function validateTestCode($mv_code){
       $lv_code = trim($mv_code); 
       $lv_code = strtoupper($lv_code);
       return $lv_code;
    }
    
    //Default Password used to Create User and Rest Password.
    public static function getDefaultPassword(){                
        $lv_defaultPassword = 'reset123';
        return $lv_defaultPassword; 
    }

}
