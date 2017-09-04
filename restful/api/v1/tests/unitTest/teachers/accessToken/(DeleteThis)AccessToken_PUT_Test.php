
<?php
/*
use lsantsan\unitTest\BasicTestCase;
use lsantsan\service\controller\TeachersServiceController;
use lsantsan\model\ServiceException;
use lsantsan\model\Message;

require_once(__DIR__ . '/../../BasicTestCase.php');

//base url: /teachers
class AccessToken_PUT_Test extends BasicTestCase
{

    protected $serviceDataArray = array(
        'method' => 'PUT',
        'url' => ['accessToken']);
    protected $requiredInputsArray = ['userId', 'refreshToken'];
    protected $basenameFile = 'AccessToken.php';

    public function testAccessTokenPUT_Success()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables', 'createProcedureException']);
        $this->createTableMock('TokenTbl', ['retrieveAccessToken']);

        //Input
        $username = 'admin';
        $refreshToken = '10f6efd32391ffd995072298bf531b12';

        //Output
        $accessTokenStub = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $refreshTokenStub = $refreshToken;

        $jsonInput = $this->createInputJson($username, $refreshToken);
        $jsonOutput = $this->createOutputJson($accessTokenStub, $refreshTokenStub);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        // IMPLEMENTING STUB's METHODS
        $this->_utilStub->expects($this->exactly(1))
            ->method('nullCheckForTables')
            ->with(
                $this->anything()
                , $this->stringContains($this->basenameFile));
        $this->_utilStub->expects($this->exactly(1))
            ->method('checkInput')
            ->with(
                $this->identicalTo($this->requiredInputsArray)
                , $this->identicalTo($jsonInput));

        $this->_utilStub->expects($this->never())
            ->method('createProcedureException');

        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveAccessToken')
            ->with(
                $this->stringContains($username)
                , $this->stringContains($refreshToken))
            ->willReturn($accessTokenStub);


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $serviceOutput = $teacherService->runService();
        $this->assertJsonStringEqualsJsonString(
            json_encode($jsonOutput)
            , json_encode($serviceOutput)
        );
    }

    public function testAccessTokenPUT_InvalidUsername()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables', 'createProcedureException']);
        $this->createTableMock('TokenTbl', ['retrieveAccessToken']);

        //Required database objects
        $databaseTblsArray = array(
            'tokenTbl' => $this->_databaseStub->tokenTbl,
        );
        //Input
        $username = 'invalidUsername';
        $refreshToken = '10f6efd32391ffd995072298bf531b12';

        //Output
        $code = 'rest-100';
        $message = 'Invalid Credentials';
        $details = 'Invalid userId or refresh token.';
        $httpCode = 401;

        $jsonInput = $this->createInputJson($username, $refreshToken);
        $jsonOutput = parent::createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        // IMPLEMENTING STUB's METHODS
        $this->_utilStub->expects($this->exactly(1))
            ->method('nullCheckForTables')
            ->with(
                $this->anything()
                , $this->stringContains($this->basenameFile));

        $this->_utilStub->expects($this->exactly(1))
            ->method('checkInput')
            ->with(
                $this->identicalTo($this->requiredInputsArray)
                , $this->identicalTo($jsonInput));

        $this->_utilStub->expects($this->never())
            ->method('createProcedureException');

        $this->_tokenTblStub->expects($this->exactly(1))
            ->method('retrieveAccessToken')
            ->with(
                $this->stringContains($username)
                , $this->stringContains($refreshToken))
            ->willReturn(0);


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testAccessTokenPUT_InvalidAccessToken()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables', 'createProcedureException']);
        $this->createTableMock('TokenTbl', ['retrieveAccessToken']);

        //Required database objects
        $databaseTblsArray = array(
            'tokenTbl' => $this->_databaseStub->tokenTbl,
        );
        //Input
        $username = 'admin';
        $refreshToken = 'invalid';
        //Output
        $code = 'rest-100';
        $message = 'Invalid Credentials';
        $details = 'Invalid userId or refresh token.';
        $httpCode = 401;

        $jsonInput = $this->createInputJson($username, $refreshToken);
        $jsonOutput = parent::createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        // IMPLEMENTING STUB's METHODS
        $this->_utilStub->expects($this->exactly(1))
            ->method('nullCheckForTables')
            ->with(
                $this->anything()
                , $this->stringContains($this->basenameFile));
        $this->_utilStub->expects($this->exactly(1))
            ->method('checkInput')
            ->with(
                $this->identicalTo($this->requiredInputsArray)
                , $this->identicalTo($jsonInput));

        $this->_utilStub->expects($this->once())
            ->method('createProcedureException');

        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveAccessToken')
            ->with(
                $this->stringContains($username)
                , $this->stringContains($refreshToken))
            ->willReturn(NULL);


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testAccessTokenPUT_EmptyInput_All()
    {
        //Defining the mocked methods
        $this->createUtilMock(['nullCheckForTables', 'createProcedureException']);
        $this->createTableMock('TokenTbl', ['retrieveAccessToken']);

        //Required database objects
        $databaseTblsArray = array(
            'tokenTbl' => $this->_databaseStub->tokenTbl,
        );
        //Input
        $username = ''; //empty username
        $refreshToken = ''; //empty refreshToken
        //Output
        $emptyFieldName = ["userId cannot be empty.", "refreshToken cannot be empty."];
        $code = 'rest-101';
        $message = 'Empty Input';
        $details = $emptyFieldName;
        $httpCode = 400;

        $messageObj = new Message($code, $message, $emptyFieldName);

        $jsonInput = $this->createInputJson($username, $refreshToken);
        $jsonOutput = parent::createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        // IMPLEMENTING STUB's METHODS
        $this->_utilStub->expects($this->never())
            ->method('nullCheckForTables');


        $this->_tokenTblStub->expects($this->never())
            ->method('retrieveAccessToken');
        $this->_utilStub->expects($this->never())
            ->method('createProcedureException');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testAccessTokenPUT_CatchDatabaseException()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables', 'createProcedureException']);
        $this->createTableMock('TokenTbl', ['retrieveAccessToken']);

        //Required database objects
        $databaseTblsArray = array(
            'tokenTbl' => $this->_databaseStub->tokenTbl,
        );
        //Input
        $username = 'admin';
        $refreshToken = 'asfadfdfasdf';
        //Output
        $code = 'proc-100';
        $message = 'Database Error';
        $pdoMessage = 'Message from PDOException';
        $details = "$pdoMessage [FILE: C:\\wamp64\\www\\restful\\api\\v1\\tests\\unitTest\\teachers\\accessToken\\(DeleteThis)AccessToken_PUT_Test.php] [LINE: 278]";
        $httpCode = 500;

        $jsonInput = $this->createInputJson($username, $refreshToken);
        $jsonOutput = parent::createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        // IMPLEMENTING STUB's METHODS
        $this->_utilStub->expects($this->exactly(1))
            ->method('nullCheckForTables')
            ->with(
                $this->anything()
                , $this->stringContains($this->basenameFile));
        $this->_utilStub->expects($this->exactly(1))
            ->method('checkInput')
            ->with(
                $this->identicalTo($this->requiredInputsArray)
                , $this->identicalTo($jsonInput));

        $this->_utilStub->expects($this->never())
            ->method('createProcedureException');

        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveAccessToken')
            ->with(
                $this->stringContains($username)
                , $this->stringContains($refreshToken))
            ->will($this->throwException(new PDOException($pdoMessage, '000')));


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testAccessTokenPUT_DatabaseDependencyNull()
    {
        $this->checkDependency('database');
    }

    public function testAccessTokenPUT_UtilDependencyNull()
    {
        $this->checkDependency('util');
    }

    private function checkDependency($dependencyName)
    {
        //Defining the mocked methods
        $this->createUtilMock([]);
        $this->createTableMock('TokenTbl', []);

        //Input
        $username = 'admin';
        $refreshToken = '10f6efd32391ffd995072298bf531b12';

        //Output
        $arrayName = 'DependencyArray';
        $elementName = $dependencyName;
        $code = 'rest-999';
        $message = 'Internal Error';
        $details = "$this->basenameFile => Element: $elementName from $arrayName is null or empty.";
        $httpCode = 500;

        $jsonInput = $this->createInputJson($username, $refreshToken);
        $jsonOutput = parent::createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);
        $this->dependencyArray[$dependencyName] = NULL; //ASSIGNING NULL to Database Stub

        // IMPLEMENTING STUB's METHODS
        $this->_utilStub->expects($this->never())
            ->method('checkInput');
        $this->_utilStub->expects($this->never())
            ->method('nullCheckForTables');
        $this->_utilStub->expects($this->never())
            ->method('createProcedureException');
        $this->_tokenTblStub->expects($this->never())
            ->method('retrieveAccessToken');


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }


    private function createInputJson($username, $refreshToken)
    {
        return array(
            'userId' => $username,
            'refreshToken' => $refreshToken);
    }

    protected function createOutputJson($accessToken, $refreshToken)
    {
        return array(
            'accessToken' => $accessToken,
            'refreshToken' => $refreshToken);
    }
}
*/