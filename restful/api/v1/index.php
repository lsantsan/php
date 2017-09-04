<?php

use lsantsan\model\Message;

//It wrappes php errors into an exception. Then, the exception is transformed into json and sent as a response.
function exception_error_handler($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        // This error code is not included in error_reporting
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}

set_error_handler("exception_error_handler");

require_once 'ApiController.php';

// Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
    $API = new ApiController($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
    echo $API->processAPI();
} catch (Exception $ex) {    
    header("HTTP/1.1 500 Internal Server Error");
    $detail = "{$ex->getMessage()} [FILE: {$ex->getFile()}] [LINE: {$ex->getLine()}]";    
    echo json_encode(new Message("rest-999", "Internal Error", $detail));
    exit;
}