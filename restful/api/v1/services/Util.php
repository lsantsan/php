<?php

namespace lsantsan\service;
use lsantsan\model\Message;
use lsantsan\model\ServiceException;

require_once (__DIR__ . '/../models/Message.php');
require_once (__DIR__ . '/../models/ServiceException.php');

class Util {

    public function generateToken() {
        $token = bin2hex(openssl_random_pseudo_bytes(16));
        return $token;
    }

    public function checkInput($requiredInputArray, $inputArray) {        
        $emptyFieldNames = array();
        foreach ($requiredInputArray as $requiredInput) {
            //The IF uses isset()'s speed and array_key_exists()'s reliability
            if (!isset($inputArray[$requiredInput]) || !array_key_exists($requiredInput, $inputArray)) {
                array_push($emptyFieldNames, "$requiredInput was not sent.");
            } elseif (empty($inputArray[$requiredInput]) || ctype_space($inputArray[$requiredInput])) {
                array_push($emptyFieldNames, "$requiredInput cannot be empty.");
            }
        }
        if (!empty($emptyFieldNames)) {
            $responseMessage = new Message("rest-101", "Empty Input", $emptyFieldNames);
            $httpCode = 400;
            throw new ServiceException($responseMessage, $httpCode);
        }
        return true;
    }
    
    public function nullCheckForTables($tableObjectMap, $sourceFileName) {
        $nullObjectsArray = array();
        foreach ($tableObjectMap as $key => $value) {
            if (!isset($value)) {
                $details = "$sourceFileName => Object $key from database variable is null.";
                array_push($nullObjectsArray, $details);
            }
        }
        if (!empty($nullObjectsArray)) {
            $responseMessage = new Message("rest-999", "Internal Error", $nullObjectsArray);
            $httpCode = 500;
            throw new ServiceException($responseMessage, $httpCode);
        }
        return true;
    }

    public function createProcedureException($procName, $tableName, $sourceFileName) {
        $details = "$sourceFileName => Procedure $procName from $tableName returned null.";
        $responseMessage = new Message("rest-999", "Internal Error", $details);
        $httpCode = 500;
        throw new ServiceException($responseMessage, $httpCode);
    }    

    public static function createDependencyException($arrayName, $sourceFileName, $elementName = NULL) {
        if (isset($elementName)) {
            $details = "$sourceFileName => Element: $elementName from $arrayName is null or empty.";
        } else {
            $details = "$sourceFileName => $arrayName is null or empty.";
        }
        $responseMessage = new Message("rest-999", "Internal Error", $details);
        $httpCode = 500;
        throw new ServiceException($responseMessage, $httpCode);
    }

    protected function hasAccess($accessToken)
    {
        //Checking consumer's token
        $dbResult = $this->databaseObj->tokenTbl->retrieveToken($accessToken);
        $userId = $dbResult['user_id'];
        if (is_null($userId)) {
            $responseMessage = new Message("rest-102", "Invalid Access Token", "The service requires a valid access token.");
            $httpCode = 401;
            throw new ServiceException($responseMessage, $httpCode);
        }
        //Checking token's expiration
        date_default_timezone_set('America/Denver');
        $creationDateTime = new DateTime($dbResult['creation_date']);
        $currentDateTime = new DateTime();
        $interval = new DateInterval($this->databaseObj->tokenTbl->getTokenLifeTime());
        $limitDate = $creationDateTime->add($interval);

        if ($currentDateTime->format('Y-m-d H:i:s') > $limitDate->format('Y-m-d H:i:s')) {
            $responseMessage = new Message("rest-103", "Expired Access Token", "This access token has expired.");
            $httpCode = 401;
            throw new ServiceException($responseMessage, $httpCode);
        }
        //Checking consumer's permission
        $isAdmin = $this->databaseObj->teacherTbl->isTeacherAdmin($userId);
        if ($isAdmin != 1) {
            $responseMessage = new Message("rest-104", "Action Denied", "This consumer does not have enough privilege.");
            $httpCode = 403;
            throw new ServiceException($responseMessage, $httpCode);
        }
        return $userId;
    }

}
