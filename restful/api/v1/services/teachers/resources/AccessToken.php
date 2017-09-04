<?php

namespace lsantsan\service;
use lsantsan\model\Message;
use lsantsan\model\ServiceException;
use \PDOException;

require_once(__DIR__ . '/../../AbstractService.php');

class AccessToken extends AbstractService{
    protected $basenameFile = 'AccessToken.php';

    public function post() {
        $requiredInputsArray = ['username', 'password'];
        try {
            $this->setup($requiredInputsArray);

            //Handling inputs
            $lv_username = $this->inputArray['username'];
            $lv_password = $this->inputArray['password'];

            //Checking teacher's credentials
            $dbResult = $this->databaseObj->teacherTbl->checkTeacher($lv_username, $lv_password);
            (!isset($dbResult)) ? $this->utilObj->createProcedureException('checkTeacher', 'TeacherTbl', basename(__FILE__)) : '';
            if ($dbResult != 1) {
                $responseMessage = new Message("rest-100", "Invalid Credentials", "Invalid username or password.");
                $httpCode = 401;
                throw new ServiceException($responseMessage, $httpCode);
            }

            //Getting userInfo
            $userInfo = $this->databaseObj->teacherTbl->retrieveTeacherByUsername($lv_username);
            if (!isset($userInfo) || is_null($userInfo)) {
                $responseMessage = new Message("rest-110", "Record Not Found", "Teacher not found.");
                $httpCode = 404;
                throw new ServiceException($responseMessage, $httpCode);
            }

            //Generating tokens and Saving them on database
            $userId = $userInfo['id'];
            $lv_accessToken = $this->utilObj->generateToken();
            $dbResult2 = $this->databaseObj->tokenTbl->saveToken($userId, $lv_accessToken);
            (!isset($dbResult2)) ? $this->utilObj->createProcedureException('saveToken', 'TokenTbl', basename(__FILE__)) : '';

            //Building JSON response
            $resultArray = array(
                'accessToken' => $lv_accessToken
            );
            return $resultArray;
        } catch (PDOException $ex) {
            $detail = "{$ex->getMessage()} [FILE: {$ex->getFile()}] [LINE: {$ex->getLine()}]";
            $responseMessage = new Message("proc-100", "Database Error", $detail);
            $httpCode = 500;
            throw new ServiceException($responseMessage, $httpCode);
        } catch (ServiceException $ex) {
            throw $ex;
        }
    }

    /*public function put() {
        $requiredInputsArray = ['userId', 'refreshToken'];
        try {
            $this->setup($requiredInputsArray);

            //Handling inputs
            $lv_userId = $this->inputArray['userId'];
            $lv_refreshToken = $this->inputArray['refreshToken'];

            //Retrieving Access Token
            $dbResult_accessToken = $this->databaseObj->tokenTbl->retrieveAccessToken($lv_userId, $lv_refreshToken);
            (is_null($dbResult_accessToken)) ? $this->utilObj->createProcedureException('retrieveAccessToken', 'TokenTbl', basename(__FILE__)) : '';
            echo $dbResult_accessToken;
            if ($dbResult_accessToken == 0) {
                $responseMessage = new Message("rest-100", "Invalid Credentials", "Invalid userId or refresh token.");
                $httpCode = 401;
                throw new ServiceException($responseMessage, $httpCode);
            }

            //Building JSON response
            $resultArray = array(
                'accessToken' => $dbResult_accessToken,
                'refreshToken' => $lv_refreshToken,
            );
            return $resultArray;
        } catch (PDOException $ex) {
            $detail = "{$ex->getMessage()} [FILE: {$ex->getFile()}] [LINE: {$ex->getLine()}]";
            $responseMessage = new Message("proc-100", "Database Error", $detail);
            $httpCode = 500;
            throw new ServiceException($responseMessage, $httpCode);
        } catch (ServiceException $ex) {
            throw $ex;
        }
    }*/

}
