<?php

namespace lsantsan\unitTest;

use lsantsan\service\controller\TeachersServiceController;
use lsantsan\model\ServiceException;
use lsantsan\model\Code;
use lsantsan\model\Test;
use \PDOException;

require_once(__DIR__ . '/../../BasicTestCase.php');
require_once(__DIR__ . '/../../../../models/Code.php');
require_once(__DIR__ . '/../../../../models/Test.php');

class Tests_GET_Test extends BasicTestCase
{

    const TEACHER_ID = 233;
    const TEST_ID = 12;
    protected $serviceDataArray = array(
        'method' => 'GET',
        'url' => [Tests_GET_Test::TEACHER_ID, 'tests', Tests_GET_Test::TEST_ID]); //URL: /{teacherId}/tests/{testId}
    protected $requiredInputsArray = ['accessToken'];
    protected $basenameFile = 'Tests.php';

    public function testTestsGET_Success()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TeacherTbl', ['isTeacherAdmin']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);
        $this->createTableMock('TestTbl', ['getTestByTestId']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $consumerId = Tests_GET_Test::TEACHER_ID;

        //Output
        $teacherId = Tests_GET_Test::TEACHER_ID;
        $duration = 30;
        $instructions = 'These are the instructions.';
        $prompt = 'This is the prompt.';
        $semesterId = 1;
        $testTypeId = 2;
        $testId = Tests_GET_Test::TEST_ID;
        $codeId = 9;
        $isActive = 1;
        $creationDate = '2016-06-11 00:00:00';
        $testOutputObj = new Test($teacherId, $duration, $instructions, $prompt, $semesterId, $testTypeId, $testId, $codeId, $isActive, $creationDate);

        $arrayInput = array('accessToken' => $accessToken);
        $jsonOutput = $testOutputObj;

        $this->serviceDataArray['payload'] = $arrayInput;

        // ASSERTING STUB's METHODS
        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveToken')
            ->with(
                $this->stringContains($accessToken))
            ->willReturn(array('user_id' => $consumerId,
                'creation_date' => date('Y-m-d H:i:s')));

        // UTIL STUB's mocked methods
        $this->_utilStub->expects($this->exactly(1))
            ->method('checkInput')
            ->with(
                $this->identicalTo($this->requiredInputsArray)
                , $this->identicalTo($arrayInput));

        $this->_utilStub->expects($this->exactly(1))
            ->method('nullCheckForTables')
            ->with(
                $this->anything()
                , $this->stringContains($this->basenameFile));

        $this->_teacherTblStub->expects($this->once())
            ->method('isTeacherAdmin')
            ->with(
                $this->stringContains($consumerId))
            ->willReturn(1);

        $this->_testTblStub->expects($this->once())
            ->method('getTestByTestId')
            ->with(
                $this->stringContains($testId))
            ->willReturn($testOutputObj);

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $serviceOutput = $teacherService->runService();
        $this->assertJsonStringEqualsJsonString(
            json_encode($jsonOutput)
            , json_encode($serviceOutput)
        );
    }

    public function testTestGET_TestNotFound()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TeacherTbl', ['isTeacherAdmin']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);
        $this->createTableMock('TestTbl', ['getTestByTestId']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $consumerId = Tests_GET_Test::TEACHER_ID;
        $testId = Tests_GET_Test::TEST_ID;

        //Output
        $code = 'rest-110';
        $message = 'Record Not Found';
        $details = "Test not found.";
        $httpCode = 404;

        $arrayInput = array('accessToken' => $accessToken);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = $arrayInput;

        // ASSERTING STUB's METHODS
        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveToken')
            ->with(
                $this->stringContains($accessToken))
            ->willReturn(array('user_id' => $consumerId,
                'creation_date' => date('Y-m-d H:i:s')));

        // UTIL STUB's mocked methods
        $this->_utilStub->expects($this->exactly(1))
            ->method('checkInput')
            ->with(
                $this->identicalTo($this->requiredInputsArray)
                , $this->identicalTo($arrayInput));

        $this->_utilStub->expects($this->exactly(1))
            ->method('nullCheckForTables')
            ->with(
                $this->anything()
                , $this->stringContains($this->basenameFile));

        $this->_teacherTblStub->expects($this->once())
            ->method('isTeacherAdmin')
            ->with(
                $this->stringContains($consumerId))
            ->willReturn(1);

        $this->_testTblStub->expects($this->once())
            ->method('getTestByTestId')
            ->with(
                $this->stringContains($testId))
            ->willReturn(NULL);

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();

    }

    public function testTestsGET_EmptyInput_All()
    {
        //Defining the mocked methods
        $this->createUtilMock(['nullCheckForTables']);

        //Input
        $accessToken = '';

        //Output
        $emptyFieldName = [
            "accessToken cannot be empty."
        ];
        $code = 'rest-101';
        $message = 'Empty Input';
        $details = $emptyFieldName;
        $httpCode = 400;

        $arrayInput = array('accessToken' => $accessToken);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = $arrayInput;

        // IMPLEMENTING STUB's METHODS
        $this->_tokenTblStub->expects($this->never())
            ->method('retrieveToken');

        $this->_utilStub->expects($this->never())
            ->method('nullCheckForTables');


        $this->_teacherTblStub->expects($this->never())
            ->method('isTeacherAdmin');

        $this->_testTblStub->expects($this->never())
            ->method('getTestByTestId');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTestsGET_InvalidToken()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TokenTbl', ['retrieveToken', 'getTokenLifeTime']);

        //Input
        $accessToken = 'asdvaq234';

        //Output
        $code = 'rest-102';
        $message = 'Invalid Access Token';
        $details = "The service requires a valid access token.";
        $httpCode = 401;

        $arrayInput = array('accessToken' => $accessToken);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = $arrayInput;


        // ASSERTING STUB's METHODS
        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveToken')
            ->with(
                $this->stringContains($accessToken))
            ->willReturn(null);

        // IMPLEMENTING STUB's METHODS
        $this->_tokenTblStub->expects($this->never())
            ->method('getTokenLifeTime');

        $this->_teacherTblStub->expects($this->never())
            ->method('isTeacherAdmin');
        $this->_testTblStub->expects($this->never())
            ->method('getTestByTestId');


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTestsGET_ExpiredToken()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);
        $this->createTableMock('TeacherTbl', []);

        //Input
        $accessToken = 'asdvaq234';
        $consumerId = '456';

        //Output
        $code = 'rest-103';
        $message = 'Expired Access Token';
        $details = "This access token has expired.";
        $httpCode = 401;

        $arrayInput = array('accessToken' => $accessToken);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = $arrayInput;

        // ASSERTING STUB's METHODS
        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveToken')
            ->with(
                $this->stringContains($accessToken))
            ->willReturn(array('user_id' => $consumerId,
                'creation_date' => date('Y-m-d H:i:s', strtotime("-1 days"))));

        // IMPLEMENTING STUB's METHODS
        $this->_teacherTblStub->expects($this->never())
            ->method('isTeacherAdmin');
        $this->_testTblStub->expects($this->never())
            ->method('getTestByTestId');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTestsGET_ConsumerCannotAccessTest()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);
        $this->createTableMock('TeacherTbl', ['isTeacherAdmin']);

        //Input
        $accessToken = 'asdvaq234';
        $consumerId = '456';

        //Output
        $code = 'rest-104';
        $message = 'Action Denied';
        $details = "Consumer cannot access this test.";
        $httpCode = 403;

        $arrayInput = array('accessToken' => $accessToken);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = $arrayInput;

        // ASSERTING STUB's METHODS
        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveToken')
            ->with(
                $this->stringContains($accessToken))
            ->willReturn(array('user_id' => $consumerId,
                'creation_date' => date('Y-m-d H:i:s')));

        // IMPLEMENTING STUB's METHODS
        $this->_teacherTblStub->expects($this->once())
            ->method('isTeacherAdmin')
            ->with(
                $this->stringContains($consumerId))
            ->willReturn(0);

        $this->_testTblStub->expects($this->never())
            ->method('getTestByTestId');


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTestsGET_CatchDatabaseException()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TeacherTbl', ['isTeacherAdmin']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);

        //Input
        $accessToken = 'asdvaq234';

        //Output
        $code = 'proc-100';
        $message = 'Database Error';
        $pdoMessage = 'Message from PDOException';
        $details = "$pdoMessage [FILE: C:\\wamp64\\www\\php\\restful\\api\\v1\\tests\\unitTest\\teachers\\tests\\Tests_GET_ById_Test.php] [LINE: 309]";
        $httpCode = 500;

        $arrayInput = array('accessToken' => $accessToken);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = $arrayInput;


        // ASSERTING STUB's METHODS
        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveToken')
            ->with(
                $this->stringContains($accessToken))
            ->will($this->throwException(new PDOException($pdoMessage, '000')));

        $this->_teacherTblStub->expects($this->never())
            ->method('isTeacherAdmin');
        $this->_testTblStub->expects($this->never())
            ->method('getTestByTestId');


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTestsGET_DatabaseDependencyNull()
    {
        $this->checkDependency('database');
    }

    public function testTestsGET_UtilDependencyNull()
    {
        $this->checkDependency('util');
    }

    private function checkDependency($dependencyName)
    {
        //Defining the mocked methods
        $this->createUtilMock([]);

        //Input
        $accessToken = 'asdvaq234';

        //Output
        $arrayName = 'DependencyArray';
        $elementName = $dependencyName;
        $code = 'rest-999';
        $message = 'Internal Error';
        $details = "$this->basenameFile => Element: $elementName from $arrayName is null or empty.";
        $httpCode = 500;

        $arrayInput = array('accessToken' => $accessToken);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = $arrayInput;
        $this->dependencyArray[$dependencyName] = NULL; //ASSIGNING NULL to Database Stub

        // IMPLEMENTING STUB's METHODS
        $this->_utilStub->expects($this->never())
            ->method('nullCheckForTables');
        $this->_utilStub->expects($this->never())
            ->method('checkInput');
        $this->_tokenTblStub->expects($this->never())
            ->method('retrieveToken');
        $this->_teacherTblStub->expects($this->never())
            ->method('isTeacherAdmin');
        $this->_testTblStub->expects($this->never())
            ->method('getTestByTestId');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }
}