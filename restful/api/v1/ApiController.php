<?php

use lsantsan\model\Database;
use lsantsan\model\ServiceException;
use lsantsan\service\Util;
use lsantsan\service\controller\TeachersServiceController;

require_once (__DIR__ . '/AbstractApi.php');
require_once (__DIR__ . '/models/ServiceException.php');
require_once (__DIR__ . '/models/database/Database.php');
require_once (__DIR__ . '/services/teachers/TeachersServiceControllerCtrl.php');
require_once (__DIR__ . '/services/Util.php');

class ApiController extends API {

    protected $serviceDataArray;
    protected $dependencyArray;

    public function __construct($request, $origin = null) {
        parent::__construct($request);       
        $this->serviceDataArray = array(
            'method' => $this->method,
            'payload' => $this->payload,
            'url' => $this->url,
        );
        try {
            $this->dependencyArray = array(
                'database' => new Database(),
                'util' => new Util(),
            );
        } catch (ServiceException $ex) {
            echo $this->_response($ex->getResponseMessage(), $ex->getHttpCode());
            exit;
        }
    }

    /**
     * Example of an Resource
     */
    protected function teachers() {
        try {
            $teachersService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
            $response = $teachersService->runService();
        } catch (ServiceException $ex) {
            echo $this->_response($ex->getResponseMessage(), $ex->getHttpCode());
            exit;
        }

        return $response;
    }

}
