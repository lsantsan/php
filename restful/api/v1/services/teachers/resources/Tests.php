<?php

namespace lsantsan\service;

use lsantsan\model\Message;
use lsantsan\model\ServiceException;
use \PDOException;

require_once(__DIR__ . '/../../Util.php');

class Tests extends AbstractService
{

    protected $basenameFile = 'Tests.php';

    public function post($teacherId)
    {
        $requiredInputsArray = ['accessToken', 'duration', 'instructions', 'prompt', 'semester', 'type'];
        try {
            $this->setup($requiredInputsArray);

            //Handling inputs
            $lv_accessToken = $this->inputArray['accessToken'];
            $lv_teacherId = $teacherId;
            $lv_duration = $this->inputArray['duration'];
            $lv_instructions = $this->inputArray['instructions'];
            $lv_prompt = $this->inputArray['prompt'];
            $lv_semester = $this->inputArray['semester']; //i.e. F,S,W => Fall, Spring, Winter
            $lv_type = $this->inputArray['type']; //i.e. E,J,T => Exit, Journal, Timed

            //Checking consumer's access
            $consumerId = $this->hasAccess($lv_accessToken);

            if ($consumerId != $teacherId) {
                $responseMessage = new Message("rest-104", "Action Denied", "Consumer and Teacher ids do not match.");
                $httpCode = 403;
                throw new ServiceException($responseMessage, $httpCode);
            }

            //Building codeFirstPart
            $lv_current_year = substr(date("Y"), 2, 3); //Last two digits of the year.
            $lv_codeFirstPart = $lv_semester . $lv_current_year . $lv_type;

            $testId = $this->databaseObj->testTbl->createTest($lv_teacherId, $lv_codeFirstPart, $lv_duration, $lv_instructions, $lv_prompt);

            if ($testId == 0) {
                $responseMessage = new Message("rest-999", "Internal Error", "Something went wrong in the database while creating new test.");
                $httpCode = 500;
                throw new ServiceException($responseMessage, $httpCode);
            }

            $code = $this->databaseObj->codeTbl->getCodeByTestId($testId);
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

}
