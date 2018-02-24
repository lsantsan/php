<?php

namespace lsantsan\service;

use lsantsan\model\Message;
use lsantsan\model\ServiceException;
use \PDOException;


require_once(__DIR__ . '/../../Util.php');

class Teachers extends AbstractService
{
    protected $basenameFile = 'Teachers.php';

    public function post()
    {
        $requiredInputsArray = ['accessToken', 'username', 'firstName', 'lastName', 'password', 'isAdmin'];
        try {
            $this->setup($requiredInputsArray);

            //Handling inputs
            $lv_accessToken = $this->inputArray['accessToken'];
            $lv_username = $this->inputArray['username'];
            $lv_firstName = $this->inputArray['firstName'];
            $lv_lastName = $this->inputArray['lastName'];
            $lv_password = $this->inputArray['password'];
            $lv_isAdmin = $this->inputArray['isAdmin'];

            //Checking consumer's access
            $consumerId = $this->hasAccess($lv_accessToken);
            $this->shouldBeAdmin($consumerId);

            //Creating teacher account
            $newTeacherId = $this->databaseObj->teacherTbl->createTeacherAccount($consumerId, $lv_username, $lv_firstName, $lv_lastName, $lv_password, $lv_isAdmin);

            //Building JSON response
            $resultArray = array(
                'code' => 'rest-200',
                'message' => 'Teacher account created.',
                'details' => array('teacherId' => $newTeacherId)
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

    public function put($teacherId)
    {
        $requiredInputsArray = ['accessToken', 'username', 'firstName', 'lastName', 'isAdmin'];
        try {
            $this->setup($requiredInputsArray);

            //Handling inputs
            $lv_accessToken = $this->inputArray['accessToken'];
            $lv_teacherId = $teacherId;
            $lv_username = $this->inputArray['username'];
            $lv_firstName = $this->inputArray['firstName'];
            $lv_lastName = $this->inputArray['lastName'];
            $lv_isAdmin = $this->inputArray['isAdmin'];

            //Checking consumer's access
            $consumerId = $this->hasAccess($lv_accessToken);

            //Updating teacher account
            if ($consumerId != $lv_teacherId) {
                $this->isAdmin($consumerId);
            }
            $dbResult = $this->databaseObj->teacherTbl->updateTeacherAccount($consumerId, $lv_teacherId, $lv_username, $lv_firstName, $lv_lastName, $lv_isAdmin);
            switch ($dbResult) {
                case -1 :
                    $responseMessage = new Message("rest-104", "Action Denied", "This consumer does not have enough privilege.");
                    $httpCode = 403;
                    throw new ServiceException($responseMessage, $httpCode);
                    break;
                case -2 :
                    $responseMessage = new Message("rest-105", "Duplicated Record", "Duplicate username or first name and last name.");
                    $httpCode = 409;
                    throw new ServiceException($responseMessage, $httpCode);
                    break;
                default :
                    break;
            }

            //Building JSON response
            $resultArray = array(
                'code' => 'rest-200',
                'message' => 'Teacher account updated.',
                'details' => array('teacherId' => $lv_teacherId)
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

    public function get($teacherParam = null) //PHP doesn't allow two methods with the same name
    {
        if (is_null($teacherParam)) {
            return $this->getAll();
        } else {
            if (ctype_digit($teacherParam)) {
                return $this->getById($teacherParam);
            }else{
                return $this->getByUsername($teacherParam);
            }
        }

    }

    private function getAll()
    {
        $requiredInputsArray = ['accessToken'];
        try {
            $this->setup($requiredInputsArray);

            //Handling inputs
            $lv_accessToken = $this->inputArray['accessToken'];

            //Checking consumer's access
            $consumerId = $this->hasAccess($lv_accessToken);
            $this->shouldBeAdmin($consumerId);

            //Creating teacher accounts
            $teacherList = $this->databaseObj->teacherTbl->getAllTeachers($consumerId);

            return $teacherList;
        } catch (PDOException $ex) {
            $detail = "{$ex->getMessage()} [FILE: {$ex->getFile()}] [LINE: {$ex->getLine()}]";
            $responseMessage = new Message("proc-100", "Database Error", $detail);
            $httpCode = 500;
            throw new ServiceException($responseMessage, $httpCode);
        } catch (ServiceException $ex) {
            throw $ex;
        }
    }

    private function getById($teacherId)
{
    $requiredInputsArray = ['accessToken'];
    try {
        $this->setup($requiredInputsArray);

        //Handling inputs
        $lv_accessToken = $this->inputArray['accessToken'];

        //Checking consumer's access
        $consumerId = $this->hasAccess($lv_accessToken);
        $this->shouldBeAdmin($consumerId);

        //Getting teacher account
        $teacher = $this->databaseObj->teacherTbl->getTeacherById($teacherId);

        return $teacher;
    } catch (PDOException $ex) {
        $detail = "{$ex->getMessage()} [FILE: {$ex->getFile()}] [LINE: {$ex->getLine()}]";
        $responseMessage = new Message("proc-100", "Database Error", $detail);
        $httpCode = 500;
        throw new ServiceException($responseMessage, $httpCode);
    } catch (ServiceException $ex) {
        throw $ex;
    }
}

    private function getByUsername($teacherUsername)
    {
        $requiredInputsArray = ['accessToken'];
        try {
            $this->setup($requiredInputsArray);

            //Handling inputs
            $lv_accessToken = $this->inputArray['accessToken'];

            //Checking consumer's access
            $consumerId = $this->hasAccess($lv_accessToken);
            $this->shouldBeAdmin($consumerId);

            //Creating teacher account
            $teacher = $this->databaseObj->teacherTbl->getTeacherByUsername($teacherUsername);

            return $teacher;
        } catch (PDOException $ex) {
            $detail = "{$ex->getMessage()} [FILE: {$ex->getFile()}] [LINE: {$ex->getLine()}]";
            $responseMessage = new Message("proc-100", "Database Error", $detail);
            $httpCode = 500;
            throw new ServiceException($responseMessage, $httpCode);
        } catch (ServiceException $ex) {
            throw $ex;
        }
    }
}
