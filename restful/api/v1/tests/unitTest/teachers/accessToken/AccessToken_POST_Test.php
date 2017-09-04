<?php

namespace lsantsan\unitTest;
use lsantsan\service\controller\TeachersServiceController;
use lsantsan\model\ServiceException;
use \PDOException;


require_once(__DIR__ . '/../../BasicTestCase.php');

class AccessToken_POST_Test extends BasicTestCase
{

    protected $serviceDataArray = array(
        'method' => 'POST',
        'url' => ['accessToken']);
    protected $requiredInputsArray = ['username', 'password'];
    protected $basenameFile = 'AccessToken.php';

    public function testAccessTokenPOST_Success()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables', 'generateToken']);
        $this->createTableMock('TeacherTbl', ['checkTeacher', 'retrieveTeacherByUsername']);
        $this->createTableMock('TokenTbl', ['saveToken']);

        //Input
        $username = 'username1';
        $password = 'P@$$word';
        //Output
        $userId = 23;
        $accessTokenStub = '9c8838a2942e47dff29e5b5dd3f0d9d6';

        $jsonInput = $this->createInputJson($username, $password);
        $jsonOutput = $this->createOutputJson($accessTokenStub);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        // ASSERTING STUB's METHODS
        $this->_teacherTblStub->expects($this->once())
            ->method('checkTeacher')
            ->with(
                $this->stringContains($username)
                , $this->stringContains($password))
            ->willReturn(1);
        $this->_teacherTblStub->expects($this->once())
            ->method('retrieveTeacherByUsername')
            ->with(
                $this->stringContains($username))
            ->willReturn(array('id' => $userId));

        $this->_tokenTblStub->expects($this->once())
            ->method('saveToken')
            ->with(
                $this->stringContains($userId)
                , $this->stringContains($accessTokenStub))
            ->willReturn(1);
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

        $this->_utilStub->expects($this->exactly(1))
            ->method('generateToken')
            ->willReturnOnConsecutiveCalls(
                $accessTokenStub);

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $serviceOutput = $teacherService->runService();
        $this->assertJsonStringEqualsJsonString(
            json_encode($jsonOutput)
            , json_encode($serviceOutput)
        );
    }

    public function testAccessTokenPOST_invalidUsername()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables', 'generateToken']);
        $this->createTableMock('TeacherTbl', ['checkTeacher']);
        $this->createTableMock('TokenTbl', ['saveToken']);

        //Input
        $username = 'invalidUserId';
        $password = 'P@$$word';

        //Output
        $code = 'rest-100';
        $message = 'Invalid Credentials';
        $details = 'Invalid username or password.';
        $httpCode = 401;

        $jsonInput = $this->createInputJson($username, $password);
        $jsonOutput = parent::createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        // IMPLEMENTING STUB's METHODS
        $this->_teacherTblStub->expects($this->once())
            ->method('checkTeacher')
            ->with(
                $this->stringContains($username)
                , $this->stringContains($password))
            ->willReturn(0);
        $this->_tokenTblStub->expects($this->never())
            ->method('saveToken');
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

        $this->_utilStub->expects($this->never())
            ->method('generateToken');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testAccessTokenPOST_EmptyInput_All()
    {
        //Defining the mocked methods
        $this->createUtilMock(['nullCheckForTables', 'generateToken']);
        $this->createTableMock('TeacherTbl', ['checkTeacher']);
        $this->createTableMock('TokenTbl', ['saveToken']);

        //Input
        $username = ''; //empty username
        $password = ''; //empty password
        //Output
        $emptyFieldName = ["username cannot be empty.", "password cannot be empty."];
        $code = 'rest-101';
        $message = 'Empty Input';
        $details = $emptyFieldName;
        $httpCode = 400;

        $jsonInput = $this->createInputJson($username, $password);
        $jsonOutput = parent::createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        // IMPLEMENTING STUB's METHODS
        $this->_teacherTblStub->expects($this->never())
            ->method('checkTeacher');
        $this->_tokenTblStub->expects($this->never())
            ->method('saveToken');
        // UTIL STUB's mocked methods
        $this->_utilStub->expects($this->never())
            ->method('nullCheckForTables');
        $this->_utilStub->expects($this->never())
            ->method('generateToken');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testAccessTokenPOST_CatchDatabaseException()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables', 'generateToken']);
        $this->createTableMock('TeacherTbl', ['checkTeacher']);
        $this->createTableMock('TokenTbl', ['saveToken']);

        //Input
        $username = 'invalidUsername';
        $password = 'P@$$word';
        //Output
        $code = 'proc-100';
        $message = 'Database Error';
        $pdoMessage = 'Message from PDOException';
        $details = "$pdoMessage [FILE: C:\\wamp64\\www\\restful\\api\\v1\\tests\\unitTest\\teachers\\accessToken\\AccessToken_POST_Test.php] [LINE: 209]";
        $httpCode = 500;

        $jsonInput = $this->createInputJson($username, $password);
        $jsonOutput = parent::createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        // IMPLEMENTING STUB's METHODS
        $this->_teacherTblStub->expects($this->once())
            ->method('checkTeacher')
            ->with(
                $this->stringContains($username)
                , $this->stringContains($password))
            ->will($this->throwException(new PDOException($pdoMessage, '000')));

        $this->_tokenTblStub->expects($this->never())
            ->method('saveToken');

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

        $this->_utilStub->expects($this->never())
            ->method('generateToken');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testAccessTokenPOST_DatabaseDependencyNull()
    {
        $this->checkDependency('database');
    }

    public function testAccessTokenPOST_UtilDependencyNull()
    {
        $this->checkDependency('util');
    }

    private function checkDependency($dependencyName)
    {
        //Defining the mocked methods
        $this->createUtilMock([]);

        //Input
        $username = 'username1';
        $password = 'qeradf';

        //Output
        $arrayName = 'DependencyArray';
        $elementName = $dependencyName;
        $code = 'rest-999';
        $message = 'Internal Error';
        $details = "$this->basenameFile => Element: $elementName from $arrayName is null or empty.";
        $httpCode = 500;

        $jsonInput = $this->createInputJson($username, $password);
        $jsonOutput = parent::createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);
        $this->dependencyArray[$dependencyName] = NULL; //ASSIGNING NULL to Database Stub

        // IMPLEMENTING STUB's METHODS
        $this->_utilStub->expects($this->never())
            ->method('checkInput');
        $this->_utilStub->expects($this->never())
            ->method('nullCheckForTables');
        $this->_utilStub->expects($this->never())
            ->method('generateToken');
        $this->_tokenTblStub->expects($this->never())
            ->method('saveToken');
        $this->_teacherTblStub->expects($this->never())
            ->method('checkTeacher');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }


    private function createInputJson($username, $password)
    {
        return array(
            'username' => $username,
            'password' => $password);
    }

    protected function createOutputJson($accessToken)
    {
        return array(
            'accessToken' => $accessToken);
    }

}
