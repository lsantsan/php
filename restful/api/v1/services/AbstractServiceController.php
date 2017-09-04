<?php

namespace lsantsan\service\controller;
use lsantsan\service\Util;

require_once(__DIR__ . '/Util.php');
require_once(__DIR__ . '/../models/Message.php');
require_once(__DIR__ . '/../models/ServiceException.php');

abstract class AbstractServiceController
{

    protected $dependencyArray; //Contains all the objects. i.e database, util, etc.
    protected $serviceDataArray; //Contains method, payload, url.

    public function __construct($serviceDataArray, $dependencyArray)
    {
        if (!isset($serviceDataArray) || empty($serviceDataArray)) {
            Util::createDependencyException('ServiceDataArray', basename(__FILE__));
        }
        if (!isset($dependencyArray) || empty($dependencyArray)) {
            Util::createDependencyException('DependencyArray', basename(__FILE__));
        }
        $this->dependencyArray = $dependencyArray; //Dependency objects will be check on each service's operation.
        $this->serviceDataArray = $serviceDataArray; //Service objects will be check on each service's operation.
    }

    public abstract function runService();

}
