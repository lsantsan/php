<?php

namespace lsantsan\model;

class ServiceException extends \Exception {

    private $responseMessage;
    private $httpCode;

    public function __construct($responseMessage, $httpCode) {
        $this->responseMessage = $responseMessage;
        $this->httpCode = $httpCode;

        parent::__construct(json_encode($responseMessage), $httpCode, NULL);
    }
    
    public function getResponseMessage(){
        return $this->responseMessage;
    }
    public function getHttpCode(){
        return $this->httpCode;
    }

}
