<?php

namespace lsantsan\service;

use lsantsan\model\Message;
use lsantsan\model\Test;
use lsantsan\model\ServiceException;
use \PDOException;

require_once(__DIR__ . '/../../Util.php');
require_once(__DIR__ . '/../../../models/Test.php');

class Tests extends AbstractService
{

    protected $basenameFile = 'Tests.php';

    public function post($teacherId)
    {
        $requiredInputsArray = ['accessToken', 'duration', 'instructions', 'prompt', 'semesterId', 'testTypeId'];
        try {
            $this->setup($requiredInputsArray);

            //Handling inputs
            $lv_accessToken = $this->inputArray['accessToken'];
            $lv_duration = $this->inputArray['duration'];
            $lv_instructions = $this->inputArray['instructions'];
            $lv_prompt = $this->inputArray['prompt'];
            $lv_semesterId = $this->inputArray['semesterId'];
            $lv_testTypeId = $this->inputArray['testTypeId'];

            $testObj = new Test($teacherId, $lv_duration, $lv_instructions, $lv_prompt, $lv_semesterId, $lv_testTypeId);
            //Checking consumer's access
            $consumerId = $this->hasAccess($lv_accessToken);

            if ($consumerId != $teacherId) {
                $responseMessage = new Message("rest-104", "Action Denied", "Consumer and Teacher ids do not match.");
                $httpCode = 403;
                throw new ServiceException($responseMessage, $httpCode);
            }

            $testObj = $this->databaseObj->testTbl->createTest($testObj);

            if ($testObj->id == 0) {
                $responseMessage = new Message("rest-999", "Internal Error", "Something went wrong in the database while creating new test.");
                $httpCode = 500;
                throw new ServiceException($responseMessage, $httpCode);
            }

            $code = $this->databaseObj->codeTbl->getCodeByTestId($testObj->id);
            $lv_testCode = $code->firstPart . $code->lastDigits;

            //Building JSON response
            $resultArray = array(
                'code' => 'rest-200',
                'message' => 'Test created',
                'details' => array('testCode' => $lv_testCode)
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

    public function put($teacherId, $testId)
    {
        $requiredInputsArray = ['accessToken', 'duration', 'instructions', 'prompt', 'semesterId', 'testTypeId'];
        try {
            $this->setup($requiredInputsArray);

            //Handling inputs
            $lv_accessToken = $this->inputArray['accessToken'];
            $lv_duration = $this->inputArray['duration'];
            $lv_instructions = $this->inputArray['instructions'];
            $lv_prompt = $this->inputArray['prompt'];
            $lv_semesterId = $this->inputArray['semesterId'];
            $lv_testTypeId = $this->inputArray['testTypeId'];
            $newTestObj = new Test($teacherId, $lv_duration, $lv_instructions, $lv_prompt, $lv_semesterId, $lv_testTypeId, $testId);

            //Checking consumer's access
            $consumerId = $this->hasAccess($lv_accessToken);
            if ($consumerId != $teacherId) {
                $responseMessage = new Message("rest-104", "Action Denied", "Consumer and Teacher ids do not match.");
                $httpCode = 403;
                throw new ServiceException($responseMessage, $httpCode);
            }

            //Checking if test exists
            $testObj = $this->databaseObj->testTbl->getTestByTestId($testId);
            if ($testObj == null) {
                $responseMessage = new Message("rest-110", "Record Not Found", "Test not found.");
                $httpCode = 404;
                throw new ServiceException($responseMessage, $httpCode);
            }

            //Checking if consumer can modify test
            $testOwner = $testObj->teacherId;
            $isAdmin = $this->isAdmin($teacherId);
            if (!$isAdmin && $testOwner != $teacherId) {
                $responseMessage = new Message("rest-104", "Action Denied", "Consumer cannot modify this test.");
                $httpCode = 403;
                throw new ServiceException($responseMessage, $httpCode);
            }

            //Update test
            $updateResult = $this->databaseObj->testTbl->updateTest($newTestObj);
            if ($updateResult == 0 || $updateResult == -1) {
                $responseMessage = new Message("rest-999", "Internal Error", "A problem happened while updating test.");
                $httpCode = 500;
                throw new ServiceException($responseMessage, $httpCode);
            }

            //Get test code
            $code = $this->databaseObj->codeTbl->getCodeByTestId($newTestObj->id);
            if ($code == null) {
                $responseMessage = new Message("rest-999", "Internal Error", "A problem happened while getting code.");
                $httpCode = 500;
                throw new ServiceException($responseMessage, $httpCode);
            }
            $lv_testCode = $code->firstPart . $code->lastDigits;

            //Building JSON response
            $resultArray = array(
                'code' => 'rest-200',
                'message' => 'Test updated',
                'details' => array('testCode' => $lv_testCode)
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

    public function get($teacherId, $testId = null) //PHP doesn't allow two methods with the same name
    {
        if (is_null($testId)) {
            return $this->getAll($teacherId);
        } else {
            return $this->getById($teacherId, $testId);

        }
    }

    private function getAll($teacherId)
    {
        $requiredInputsArray = ['accessToken'];

        try {
            $this->setup($requiredInputsArray);

            //Handling inputs
            $lv_accessToken = $this->inputArray['accessToken'];

            //Checking consumer's access
            $consumerId = $this->hasAccess($lv_accessToken);

            //Checking if consumer can access tests
            $isConsumerAdmin = $this->isAdmin($consumerId);
            if (!$isConsumerAdmin && $consumerId != $teacherId) {
                $responseMessage = new Message("rest-104", "Action Denied", "Consumer cannot access tests.");
                $httpCode = 403;
                throw new ServiceException($responseMessage, $httpCode);
            }

            //Getting test
            $testArray = $this->databaseObj->testTbl->getAllTests($teacherId);
            if ($testArray == null) {
                $responseMessage = new Message("rest-110", "Record Not Found", "No tests found.");
                $httpCode = 404;
                throw new ServiceException($responseMessage, $httpCode);
            }

            return $testArray;

        } catch (PDOException $ex) {
            $detail = "{$ex->getMessage()} [FILE: {$ex->getFile()}] [LINE: {$ex->getLine()}]";
            $responseMessage = new Message("proc-100", "Database Error", $detail);
            $httpCode = 500;
            throw new ServiceException($responseMessage, $httpCode);
        } catch (ServiceException $ex) {
            throw $ex;
        }
    }

    private function getById($teacherId, $testId)
    {
        $requiredInputsArray = ['accessToken'];

        try {
            $this->setup($requiredInputsArray);

            //Handling inputs
            $lv_accessToken = $this->inputArray['accessToken'];

            //Checking consumer's access
            $consumerId = $this->hasAccess($lv_accessToken);

            //Checking if consumer can access test
            $isConsumerAdmin = $this->isAdmin($consumerId);
            if (!$isConsumerAdmin && $consumerId != $teacherId) {
                $responseMessage = new Message("rest-104", "Action Denied", "Consumer cannot access this test.");
                $httpCode = 403;
                throw new ServiceException($responseMessage, $httpCode);
            }

            //Getting test
            $testObj = $this->databaseObj->testTbl->getTestByTestId($testId);
            if ($testObj == null) {
                $responseMessage = new Message("rest-110", "Record Not Found", "Test not found.");
                $httpCode = 404;
                throw new ServiceException($responseMessage, $httpCode);
            }

            return $testObj;

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
