<?php

namespace lsantsan\service\controller;

use lsantsan\model\Message;
use lsantsan\model\ServiceException;
use \ReflectionClass;

require_once(__DIR__ . '/../AbstractServiceController.php');
require_once(__DIR__ . '/../Util.php');
require_once(__DIR__ . '/resources/AccessToken.php');
require_once(__DIR__ . '/resources/Tests.php');
require_once(__DIR__ . '/resources/Teachers.php');

class TeachersServiceController extends AbstractServiceController
{

    private $method; //i.e POST or GET or PUT or DELETE.
    private $url; //Url without the resource. i.e /{teacherId}/tests/{testId}

    public function __construct($serviceDataArray, $dependencyArray)
    {
        parent::__construct($serviceDataArray, $dependencyArray);

        $this->method = isset($serviceDataArray['method']) ? strtolower($serviceDataArray['method']) : Util::createDependencyException('ServiceDataArray', basename(__FILE__), 'method');
        $this->url = isset($serviceDataArray['url']) ? $serviceDataArray['url'] : Util::createDependencyException('ServiceDataArray', basename(__FILE__), 'url');
    }

    public function runService()
    {

        switch (count($this->url)) { //Base URL: /teachers/
            case '0': //URL: /
                $response = $this->instantiateResourceObject('Teachers');
                return $response;
            case '1':
                switch ($this->url[0]) {
                    case 'accessToken': //URL: /accessToken
                        $response = $this->instantiateResourceObject('AccessToken');
                        return $response;
                    default : //URL: /{teacherId}
                        $response = $this->instantiateResourceObject('Teachers', $this->url[0]);
                        return $response;
                }
            case '2':
                switch ($this->url[1]) {
                    case 'tests': //URL: /{teacherId}/tests
                        $response = $this->instantiateResourceObject('Tests', $this->url[0]);
                        return $response;
                }
            case '3': //URL: /{teacherId}/tests/{testId}
                $response = $this->instantiateResourceObject('wrong3');
                return $response;
            default :
                $responseMessage = new Message("rest-111", "Resource Not Found", "Url $this->url is not available.");
                $httpCode = 404;
                throw new ServiceException($responseMessage, $httpCode);
        }
    }

    private function instantiateResourceObject($className, $requestParameters = null)
    {
        $args = array($this->serviceDataArray, $this->dependencyArray);
        $objectReflection = new ReflectionClass("lsantsan\\service\\$className");
        $resourceObject = $objectReflection->newInstanceArgs($args);
        if (method_exists($resourceObject, $this->method)) {
            if (is_null($requestParameters)) {
                $response = $resourceObject->{$this->method}();
                return $response;
            } else {
                $response = $resourceObject->{$this->method}($requestParameters);
                return $response;
            }

        } else {
            $responseMessage = new Message("rest-111", "Resource Not Found", "Method '$this->method' is not available.");
            $httpCode = 404;
            throw new ServiceException($responseMessage, $httpCode);
        }

    }

}
