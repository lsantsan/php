<?php

namespace lsantsan\model;

class Message{

    // attributes are public so they can become json
    public $code;
    public $message;
    public $details;
    
     public function __construct($code, $message, $details) {
        $this->code = $code;
        $this->message = $message;
        $this->details = $details;
    }
    
    const REST_100 = "rest-100";
    const REST_101 = "rest-101";
    const REST_102 = "rest-102";
    const REST_103 = "rest-103";
    const REST_104 = "rest-104";
    const REST_105 = "rest-105";
    const REST_106 = "rest-106";
    const REST_107 = "rest-107";
    const REST_108 = "rest-108";
    const REST_109 = "rest-109";
    const REST_110 = "rest-110";
    const REST_999 = "rest-999";

    
    
}