<?php

namespace lsantsan\unitTest;

use lsantsan\service\controller\TeachersServiceController;
use lsantsan\model\Teacher;
use lsantsan\model\ServiceException;
use \PDOException;

require_once(__DIR__ . '/../BasicTestCase.php');

class Teachers_GET_ById_Test extends BasicTestCase
{

    const TEACHER_ID = 3342;
    protected $serviceDataArray = array(
        'method' => 'GET',
        'url' => [Teachers_GET_ById_Test::TEACHER_ID]);
    protected $requiredInputsArray = ['accessToken'];
    protected $basenameFile = 'Teachers.php';

    public function testTeacherGET_Success()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TeacherTbl', ['getTeacherById', 'isTeacherAdmin']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $consumerId = '456';

        //Output
        $teacherOutputArray = new Teacher(
            1,
            "username1",
            "first_name1",
            "last_name1",
            "is_admin1",
            "is_active1",
            "reset_password1",
            "creation_date1"
        );

        $arrayInput = array('accessToken' => $accessToken);
        $jsonOutput = $teacherOutputArray;

        $this->serviceDataArray['payload'] = $arrayInput;

        // ASSERTING STUB's METHODS
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

        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveToken')
            ->with(
                $this->stringContains($accessToken))
            ->willReturn(array('user_id' => $consumerId,
                'creation_date' => date('Y-m-d H:i:s')));

        $this->_teacherTblStub->expects($this->once())
            ->method('isTeacherAdmin')
            ->with(
                $this->stringContains($consumerId))
            ->willReturn(1);

        $this->_teacherTblStub->expects($this->once())
            ->method('getTeacherById')
            ->with(
                $this->stringContains(Teachers_GET_ById_Test::TEACHER_ID)
            )
            ->willReturn($teacherOutputArray);

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $serviceOutput = $teacherService->runService();
        $this->assertJsonStringEqualsJsonString(
            json_encode($jsonOutput)
            , json_encode($serviceOutput)
        );

    }

    public function testTeacherGET_EmptyInput_All()
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

        $this->_utilStub->expects($this->never())
            ->method('nullCheckForTables');

        // IMPLEMENTING STUB's METHODS
        $this->_teacherTblStub->expects($this->never())
            ->method('getTeacherById');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTeacherGET_InvalidToken()
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
        $this->_teacherTblStub->expects($this->never())
            ->method('getTeacherById');


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTeacherGET_ExpiredToken()
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
        $this->_teacherTblStub->expects($this->never())
            ->method('getTeacherById');


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTeacherGET_ConsumerNotAdmin()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);
        $this->createTableMock('TeacherTbl', ['isTeacherAdmin', 'getTeacherById']);

        //Input
        $accessToken = 'asdvaq234';
        $consumerId = '456';

        //Output
        $code = 'rest-104';
        $message = 'Action Denied';
        $details = "This consumer does not have enough privilege.";
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

        $this->_teacherTblStub->expects($this->never())
            ->method('getTeacherById');


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTeacherGET_CatchDatabaseException()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TeacherTbl', ['isTeacherAdmin', 'getTeacherById']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);

        //Input
        $accessToken = 'asdvaq234';

        //Output
        $code = 'proc-100';
        $message = 'Database Error';
        $pdoMessage = 'Message from PDOException';
        $details = "$pdoMessage [FILE: C:\\wamp64\\www\\php\\restful\\api\\v1\\tests\\unitTest\\teachers\\Teachers_GET_ById_Test.php] [LINE: 297]";
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
        $this->_teacherTblStub->expects($this->never())
            ->method('getTeacherById');


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTeacherGET_DatabaseDependencyNull()
    {
        $this->checkDependency('database');
    }

    public function testTeacherGET_UtilDependencyNull()
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
        $this->_teacherTblStub->expects($this->never())
            ->method('getTeacherById');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

}