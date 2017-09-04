<?php

namespace lsantsan\service;

use \lsantsan\model\Message;
use \lsantsan\model\ServiceException;
use \DateTime;
use \DateInterval;

abstract class AbstractService
{
    protected $serviceDataArray;
    protected $dependencyArray;
    protected $basenameFile;
    protected $utilObj;
    protected $databaseObj;
    protected $inputArray;

    public function __construct($serviceDataArray, $dependencyArray)
    {
        $this->serviceDataArray = $serviceDataArray;
        $this->dependencyArray = $dependencyArray;
    }

    public function setup($requiredInputsArray)
    {
        //Dependency Objects
        $this->utilObj = isset($this->dependencyArray['util']) ? $this->dependencyArray['util'] : Util::createDependencyException('DependencyArray', $this->basenameFile, 'util');
        $this->databaseObj = isset($this->dependencyArray['database']) ? $this->dependencyArray['database'] : Util::createDependencyException('DependencyArray', $this->basenameFile, 'database');
        //Getting request's payload
        $this->inputArray = ($this->serviceDataArray['method'] == 'GET') ?
            $this->serviceDataArray['payload'] :
            json_decode($this->serviceDataArray['payload'], true);
        //Checking input
        $this->utilObj->checkInput($requiredInputsArray, $this->inputArray);
        $this->checkTables();
    }


    public function hasAccess($accessToken)
    {
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
        return $userId;
    }

    public function isAdmin($userId)
    {
        $isAdmin = $this->databaseObj->teacherTbl->isTeacherAdmin($userId);
        if ($isAdmin != 1) {
            $responseMessage = new Message("rest-104", "Action Denied", "This consumer does not have enough privilege.");
            $httpCode = 403;
            throw new ServiceException($responseMessage, $httpCode);
        }
        return true;

    }

    private function checkTables()
    {
        $databaseTblsArray = array(
            'teacherTbl' => $this->databaseObj->teacherTbl,
            'tokenTbl' => $this->databaseObj->tokenTbl,
        );

        $this->utilObj->nullCheckForTables($databaseTblsArray, $this->basenameFile);

    }
}