<?php

use PHPUnit\Framework\TestCase;
use lsantsan\service\Util;
use lsantsan\model\ServiceException;

require_once (__DIR__ . '/../../../services/Util.php');

class Util_Test extends TestCase {

    private static $HTTP_CODE_400 = 400;
    private static $HTTP_CODE_500 = 500;
    private static $CODE_101 = 'rest-101';
    private static $CODE_999 = 'rest-999';
    private static $MESSAGE_EMPTY_INPUT = 'Empty Input';
    private static $MESSAGE_INTERNAL_ERROR = 'Internal Error';

    protected function setUp() {
        // Create a stub for the Database class.
        $this->_databaseStub = $this->getMockBuilder('Database')
                ->disableOriginalConstructor()
                ->getMock();
    }

    public function testCheckInput_Success() {
        //given
        $requiredInputsArray = ['accessToken', 'username', 'firstName'];
        $inputArray = array(
            'accessToken' => '123',
            'username' => 'abc',
            'firstName' => 'def');

        //when
        $util = new Util();
        $testChecker = $util->checkInput($requiredInputsArray, $inputArray);

        //then
        self::assertTrue(true, $testChecker);
    }

    public function testCheckInput_InputNotSent() {
        //given
        $requiredInputsArray = ['accessToken', 'username', 'firstName', 'lastName'];
        $inputArray = array(
            'accessToken' => '123',
            'username' => 'abc');
        $details = [
            "firstName was not sent.",
            "lastName was not sent."
        ];
        $jsonOutput = Util_Test::createJsonOutput(Util_Test::$CODE_101, Util_Test::$MESSAGE_EMPTY_INPUT, $details);

        //when
        $util = new Util();

        //then
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Util_Test::$HTTP_CODE_400);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $util->checkInput($requiredInputsArray, $inputArray);
    }

    public function testCheckInput_InputEmpty() {
        //given
        $requiredInputsArray = ['accessToken', 'username', 'firstName', 'lastName'];
        $inputArray = array(
            'accessToken' => '123',
            'username' => 'abc',
            'firstName' => '',
            'lastName' => '');
        $details = [
            "firstName cannot be empty.",
            "lastName cannot be empty."
        ];
        $jsonOutput = Util_Test::createJsonOutput(Util_Test::$CODE_101, Util_Test::$MESSAGE_EMPTY_INPUT, $details);

        //when
        $util = new Util();

        //then
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Util_Test::$HTTP_CODE_400);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $util->checkInput($requiredInputsArray, $inputArray);
    }

    public function testNullCheckForTables2_Success() {
        //given       
        $_teacherTblStub = $this->getMockBuilder('TeacherTbl')
                ->disableOriginalConstructor()
                ->getMock();
        $_tokenTblStub = $this->getMockBuilder('TokenTbl')
                ->disableOriginalConstructor()
                ->getMock();
        $this->_databaseStub->teacherTbl = $_teacherTblStub;
        $this->_databaseStub->tokenTbl = $_tokenTblStub;
        $tableObjectMap = array(
            'teacherTbl' => $this->_databaseStub->teacherTbl,
            'tokenTbl' => $this->_databaseStub->tokenTbl,
        );

        //when
        $util = new Util();
        $testChecker = $util->nullCheckForTables($tableObjectMap, basename(__FILE__));

        //then
        self::assertTrue(true, $testChecker);
    }

    public function testNullCheckForTables2_NullTables() {
        //given             
        $tableObjectMap = array(
            'teacherTbl' => null,
            'tokenTbl' => null,
        );
        $variableName = 'database';
        $tableName1 = 'teacherTbl';
        $tableName2 = 'tokenTbl';
        $sourceFileName = basename(__FILE__);
        $details = [
            "$sourceFileName => Object $tableName1 from $variableName variable is null.",
            "$sourceFileName => Object $tableName2 from $variableName variable is null."
        ];
        $jsonOutput = Util_Test::createJsonOutput(Util_Test::$CODE_999, Util_Test::$MESSAGE_INTERNAL_ERROR, $details);

        //when
        $util = new Util();

        //then
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Util_Test::$HTTP_CODE_500);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $util->nullCheckForTables($tableObjectMap, $sourceFileName);
    }

    public function testCreateProcedureException_Success() {
        //given
        $procName = 'myProc';
        $tableName = 'myTable';
        $sourceFileName = basename(__FILE__);
        $detail = "$sourceFileName => Procedure $procName from $tableName returned null.";
        $jsonOutput = Util_Test::createJsonOutput(Util_Test::$CODE_999, Util_Test::$MESSAGE_INTERNAL_ERROR, $detail);

        //when
        $util = new Util();

        //then
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Util_Test::$HTTP_CODE_500);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $util->createProcedureException($procName, $tableName, $sourceFileName);
    }

    public function testCreateDependencyException_SuccessWithoutElementName() {
        //given
        $arrayName = 'DependencyArray';
        $sourceFileName = basename(__FILE__);
        $detail = "$sourceFileName => $arrayName is null or empty.";
        $jsonOutput = Util_Test::createJsonOutput(Util_Test::$CODE_999, Util_Test::$MESSAGE_INTERNAL_ERROR, $detail);

        //when      
        //then
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Util_Test::$HTTP_CODE_500);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        Util::createDependencyException($arrayName, $sourceFileName);
    }

    public function testCreateDependencyException_SuccessWithElementName() {
        //given
        $arrayName = 'DependencyArray';
        $sourceFileName = basename(__FILE__);
        $elementName = 'myElement';
        $detail = "$sourceFileName => Element: $elementName from $arrayName is null or empty.";
        $jsonOutput = Util_Test::createJsonOutput(Util_Test::$CODE_999, Util_Test::$MESSAGE_INTERNAL_ERROR, $detail);

        //when      
        //then
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode(Util_Test::$HTTP_CODE_500);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        Util::createDependencyException($arrayName, $sourceFileName, $elementName);
    }

    private static function createJsonOutput($code, $message, $details) {
        return array(
            'code' => $code,
            'message' => $message,
            'details' => $details);
    }

}
