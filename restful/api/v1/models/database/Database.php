<?php

namespace lsantsan\model;

use \PDO;

require_once 'TeacherTbl.php';
require_once 'TokenTbl.php';
require_once (__DIR__ . '/../Message.php');

/* This class has a collection of all the tables in the database. Each table is a class that has
 * all the procedures.
 */

class Database {

    //LIST OF TABLE CLASSES   
    public $teacherTbl = null;
    public $tokenTbl = null;

    public function __construct() {
        $dbConnection = $this->connectDb();
        
        //INSTANTIATING TABLE CLASSES
        $this->teacherTbl = new TeacherTbl($dbConnection);
        $this->tokenTbl = new TokenTbl($dbConnection);
    }

    private function connectDb() {
        $dsn = 'mysql:host=localhost;dbname=selnate';
        $db_username = 'dbuser';
        $db_password = 'selnatecangetin';

        try {
            $dbConnection = new PDO($dsn, $db_username, $db_password);
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //When an error occurs on Mysql, the error will popup on PHP.
            return $dbConnection;
        } catch (PDOException $ex) {
            $responseMessage = new Message("db-100", "Database Connection Not Available", "");
            $httpCode = 503;
            throw new ServiceException($responseMessage, $httpCode);
        }
    }

}
