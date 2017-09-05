<?php

namespace lsantsan\unitTest;
use lsantsan\service\controller\TeachersServiceController;
use lsantsan\model\ServiceException;
use \PDOException;

require_once(__DIR__ . '/../BasicTestCase.php');

//base url: /teachers
class Teachers_POST_Test extends BasicTestCase
{

    protected $serviceDataArray = array(
        'method' => 'POST',
        'url' => []);
    protected $requiredInputsArray = ['accessToken', 'username', 'firstName', 'lastName', 'password', 'isAdmin'];
    protected $basenameFile = 'Teachers.php';

    public function testTeachersPOST_Success()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TeacherTbl', ['isTeacherAdmin', 'createTeacherAccount']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $username = 'teacher1';
        $firstName = 'Teacher1';
        $lastName = 'LastName1';
        $password = 'qeradf123';
        $isAdmin = 'true';
        $consumerId = '456';

        //Output
        $newTeacherId = '323';
        $code = 'rest-200';
        $message = 'Teacher account created.';
        $details = array('teacherId' => $newTeacherId);

        $jsonInput = $this->createInputJson($accessToken, $username, $firstName, $lastName, $password, $isAdmin);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        // ASSERTING STUB's METHODS
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
            ->method('createTeacherAccount')
            ->with(
                $this->stringContains($consumerId)
                , $this->stringContains($username)
                , $this->stringContains($firstName)
                , $this->stringContains($lastName)
                , $this->stringContains($password)
                , $this->stringContains($isAdmin)
            )
            ->willReturn($newTeacherId);

        // UTIL STUB's mocked methods
        $this->_utilStub->expects($this->exactly(1))
            ->method('checkInput')
            ->with(
                $this->identicalTo($this->requiredInputsArray)
                , $this->identicalTo($jsonInput));

        $this->_utilStub->expects($this->exactly(1))
            ->method('nullCheckForTables')
            ->with(
                $this->anything()
                , $this->stringContains($this->basenameFile));

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $serviceOutput = $teacherService->runService();
        $this->assertJsonStringEqualsJsonString(
            json_encode($jsonOutput)
            , json_encode($serviceOutput)
        );
    }

    public function testTeachersPOST_EmptyInput_All()
    {
        //Defining the mocked methods
        $this->createUtilMock(['nullCheckForTables']);

        //Input
        $accessToken = '';
        $username = '';
        $firstName = '';
        $lastName = '';
        $password = '';
        $isAdmin = '';

        //Output
        $emptyFieldName = [
            "accessToken cannot be empty.",
            "username cannot be empty.",
            "firstName cannot be empty.",
            "lastName cannot be empty.",
            "password cannot be empty.",
            "isAdmin cannot be empty.",
        ];
        $code = 'rest-101';
        $message = 'Empty Input';
        $details = $emptyFieldName;
        $httpCode = 400;

        $jsonInput = $this->createInputJson($accessToken, $username, $firstName, $lastName, $password, $isAdmin);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        $this->_utilStub->expects($this->never())
            ->method('nullCheckForTables');

        // IMPLEMENTING STUB's METHODS
        $this->_teacherTblStub->expects($this->never())
            ->method('retrieveTeacherByUsername');
        $this->_teacherTblStub->expects($this->never())
            ->method('createTeacherAccount');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTeacherPOST_InvalidToken()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TokenTbl', ['retrieveToken', 'getTokenLifeTime']);

        //Input
        $accessToken = 'asdvaq234';
        $username = 'teacher1';
        $firstName = 'FirstName';
        $lastName = 'LastName';
        $password = 'adfqe1324';
        $isAdmin = 'true';

        //Output
        $code = 'rest-102';
        $message = 'Invalid Access Token';
        $details = "The service requires a valid access token.";
        $httpCode = 401;

        $jsonInput = $this->createInputJson($accessToken, $username, $firstName, $lastName, $password, $isAdmin);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);


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
            ->method('retrieveTeacherByUsername');
        $this->_teacherTblStub->expects($this->never())
            ->method('createTeacherAccount');


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTeacherPOST_ExpiredToken()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);
        $this->createTableMock('TeacherTbl', []);

        //Input
        $accessToken = 'asdvaq234';
        $username = 'teacher1';
        $firstName = 'FirstName';
        $lastName = 'LastName';
        $password = 'adfqe1324';
        $isAdmin = 'true';
        $consumerId = '456';

        //Output
        $code = 'rest-103';
        $message = 'Expired Access Token';
        $details = "This access token has expired.";
        $httpCode = 401;

        $jsonInput = $this->createInputJson($accessToken, $username, $firstName, $lastName, $password, $isAdmin);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

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
            ->method('retrieveTeacherByUsername');
        $this->_teacherTblStub->expects($this->never())
            ->method('createTeacherAccount');


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTeacherPOST_ConsumerNotAdmin()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);
        $this->createTableMock('TeacherTbl', ['isTeacherAdmin', 'retrieveTeacherByUsername', 'createTeacherAccount']);

        //Input
        $accessToken = 'asdvaq234';
        $username = 'teacher1';
        $firstName = 'FirstName';
        $lastName = 'LastName';
        $password = 'adfqe1324';
        $isAdmin = 'true';
        $consumerId = '456';

        //Output
        $code = 'rest-104';
        $message = 'Action Denied';
        $details = "This consumer does not have enough privilege.";
        $httpCode = 403;

        $jsonInput = $this->createInputJson($accessToken, $username, $firstName, $lastName, $password, $isAdmin);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

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
            ->method('retrieveTeacherByUsername');
        $this->_teacherTblStub->expects($this->never())
            ->method('createTeacherAccount');


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTeacherPOST_CatchDatabaseException()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TeacherTbl', ['isTeacherAdmin', 'retrieveTeacherByUsername', 'createTeacherAccount']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);

        //Input
        $accessToken = 'asdvaq234';
        $username = 'teacher1';
        $firstName = 'FirstName';
        $lastName = 'LastName';
        $password = 'adfqe1324';
        $isAdmin = 'true';

        //Output
        $code = 'proc-100';
        $message = 'Database Error';
        $pdoMessage = 'Message from PDOException';
        $details = "$pdoMessage [FILE: C:\\wamp64\\www\\php\\restful\\api\\v1\\tests\\unitTest\\teachers\\Teachers_POST_Test.php] [LINE: 338]";
        $httpCode = 500;

        $jsonInput = $this->createInputJson($accessToken, $username, $firstName, $lastName, $password, $isAdmin);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);


        // ASSERTING STUB's METHODS
        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveToken')
            ->with(
                $this->stringContains($accessToken))
            ->will($this->throwException(new PDOException($pdoMessage, '000')));

        $this->_teacherTblStub->expects($this->never())
            ->method('isTeacherAdmin');
        $this->_teacherTblStub->expects($this->never())
            ->method('retrieveTeacherByUsername');
        $this->_teacherTblStub->expects($this->never())
            ->method('createTeacherAccount');


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTeacherPOST_DatabaseDependencyNull()
    {
        $this->checkDependency('database');
    }

    public function testTeacherPOST_UtilDependencyNull()
    {
        $this->checkDependency('util');
    }


    private function checkDependency($dependencyName)
    {
        //Defining the mocked methods
        $this->createUtilMock([]);

        //Input
        $accessToken = 'asdvaq234';
        $username = 'teacher1';
        $firstName = 'FirstName';
        $lastName = 'LastName';
        $password = 'adfqe1324';
        $isAdmin = 'true';

        //Output
        $arrayName = 'DependencyArray';
        $elementName = $dependencyName;
        $code = 'rest-999';
        $message = 'Internal Error';
        $details = "$this->basenameFile => Element: $elementName from $arrayName is null or empty.";
        $httpCode = 500;

        $jsonInput = $this->createInputJson($accessToken, $username, $firstName, $lastName, $password, $isAdmin);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);
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
            ->method('createTeacherAccount');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    private function createInputJson($accessToken, $username, $firstName, $lastName, $password, $isAdmin)
    {
        return array(
            'accessToken' => $accessToken,
            'username' => $username,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'password' => $password,
            'isAdmin' => $isAdmin);
    }

}
