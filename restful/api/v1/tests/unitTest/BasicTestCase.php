<?php

namespace lsantsan\unitTest;

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../models/database/TeacherTbl.php');
require_once(__DIR__ . '/../../models/database/TokenTbl.php');
require_once(__DIR__ . '/../../models/database/Database.php');
require_once(__DIR__ . '/../../services/AbstractServiceController.php');
require_once(__DIR__ . '/../../services/teachers/TeachersServiceControllerCtrl.php');
require_once(__DIR__ . '/../../services/Util.php');
require_once(__DIR__ . '/../../models/ServiceException.php');

abstract class BasicTestCase extends TestCase
{
    protected $serviceDataArray;
    protected $requiredInputsArray;
    protected $basenameFile;
    protected $dependencyArray;
    protected $_utilStub;
    protected $_databaseStub;
    protected $_teacherTblStub;
    protected $_tokenTblStub;
    protected $tableNameList = ['TokenTbl', 'TeacherTbl'];

    protected function setUp()
    {
        $this->dependencyArray = [];

        // Create a stub for the Database class.
        $this->_databaseStub = $this->getMockBuilder('lsantsan\model\Database')
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($this->tableNameList as $tableName) {
            $this->createTableMock($tableName);
        }

    }

    protected function createUtilMock($methodList)
    {
        // Create a stub for the Util class.
        $this->_utilStub = $this->getMockBuilder('lsantsan\service\Util')
            ->disableOriginalConstructor()
            ->setMethods($methodList)
            ->getMock();

        $this->dependencyArray['util'] = $this->_utilStub;
    }

    protected function createTableMock($tableClassName, $methodList = null)
    {
        $tableName = lcfirst($tableClassName);
        $objName = "_$tableName" . "Stub";
        //Create a stub for the table.
        if (is_null($methodList)) {
            $this->$objName = $this->getMockBuilder("lsantsan\\model\\$tableClassName")
                ->disableOriginalConstructor()
                ->getMock();
        } else {
            $this->$objName = $this->getMockBuilder("lsantsan\\model\\$tableClassName")
                ->disableOriginalConstructor()
                ->setMethods($methodList)
                ->getMock();
        }

        //Connecting Table' stub to Database stub
        $this->_databaseStub->$tableName = $this->$objName;
        $this->dependencyArray['database'] = $this->_databaseStub;
    }

    protected function createOutputJson($code, $message, $details)
    {
        return array(
            'code' => $code,
            'message' => $message,
            'details' => $details);
    }

}
