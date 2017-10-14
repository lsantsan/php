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


class Tests_POST_Test extends BasicTestCase
{

    const TEACHER_ID = 4422;
    protected $serviceDataArray = array(
        'method' => 'POST',
        'url' => [Tests_POST_Test::TEACHER_ID, 'tests']); //URL: /{teacherId}/tests
    protected $requiredInputsArray = ['accessToken', 'duration', 'instructions', 'prompt', 'semesterId', 'testTypeId'];
    protected $basenameFile = 'Tests.php';

    public function testTestsPOST_Success()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TestTbl', ['createTest']);
        $this->createTableMock('CodeTbl', ['getCodeByTestId']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $duration = 30;
        $instructions = 'These are the instructions.';
        $prompt = 'This is the prompt.';
        $semesterId = 1;
        $testTypeId = 2;
        $testId = 12;
        $codeId = 9;
        $currentYear = substr(date("Y"), 2, 3); //Last two digits of the year.
        $codeFirstPart = 'F' . $currentYear . 'J';
        $consumerId = Tests_POST_Test::TEACHER_ID;
        $testObjReturn = new Test($consumerId, $duration, $instructions, $prompt, $semesterId, $testTypeId, $testId);

        //Output
        $lastDigits = '451';
        $testCode = $codeFirstPart . $lastDigits;
        $code = 'rest-200';
        $message = 'Test created';
        $details = array('testCode' => $testCode);

        $jsonInput = $this->createInputJson($accessToken, $duration, $instructions, $prompt, $semesterId, $testTypeId);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        // ASSERTING STUB's METHODS
        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveToken')
            ->with(
                $this->stringContains($accessToken))
            ->willReturn(array('user_id' => $consumerId,
                'creation_date' => date('Y-m-d H:i:s')));

        $this->_testTblStub->expects($this->once())
            ->method('createTest')
            ->with(
                $this->callback(function ($_testObj) {
                    if ( // This function can't access the test's input variables
                        ($_testObj->teacherId == 4422) &&
                        ($_testObj->duration == 30) &&
                        ($_testObj->instructions == 'These are the instructions.') &&
                        ($_testObj->prompt == 'This is the prompt.') &&
                        ($_testObj->semesterId == 1) &&
                        ($_testObj->testTypeId == 2)
                    ) {
                        return true;
                    }
                    return false;
                })
            )
            ->willReturn($testObjReturn);

        $this->_codeTblStub->expects($this->once())
            ->method('getCodeByTestId')
            ->with(
                $this->stringContains($testId)
            )
            ->willReturn(new Code($codeId, $codeFirstPart, $lastDigits, 1));

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

    public function testTestsPOST_ConsumerNotTeacher()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);


        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $duration = '30';
        $instructions = 'These are the instructions.';
        $prompt = 'This is the prompt.';
        $semesterId = 1;
        $testTypeId = 2;
        $consumerId = 11;

        //Output
        $code = 'rest-104';
        $message = 'Action Denied';
        $details = "Consumer and Teacher ids do not match.";
        $httpCode = 403;

        $jsonInput = $this->createInputJson($accessToken, $duration, $instructions, $prompt, $semesterId, $testTypeId);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        // ASSERTING STUB's METHODS
        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveToken')
            ->with(
                $this->stringContains($accessToken))
            ->willReturn(array('user_id' => $consumerId,
                'creation_date' => date('Y-m-d H:i:s')));

        $this->_testTblStub->expects($this->never())
            ->method('createTest');

        $this->_codeTblStub->expects($this->never())
            ->method('getCodeByTestId');

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
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTestsPOST_ProcedureError()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TestTbl', ['createTest']);
        $this->createTableMock('CodeTbl', ['getCodeByTestId']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $duration = 30;
        $instructions = 'These are the instructions.';
        $prompt = 'This is the prompt.';
        $semesterId = 1;
        $testTypeId = 2;
        $testId = 0;
        $consumerId = Tests_POST_Test::TEACHER_ID;
        $testObjReturn = new Test($consumerId, $duration, $instructions, $prompt, $semesterId, $testTypeId, $testId);


        //Output
        $code = 'rest-999';
        $message = 'Internal Error';
        $details = "Something went wrong in the database while creating new test.";
        $httpCode = 500;

        $jsonInput = $this->createInputJson($accessToken, $duration, $instructions, $prompt, $semesterId, $testTypeId);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        // ASSERTING STUB's METHODS
        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveToken')
            ->with(
                $this->stringContains($accessToken))
            ->willReturn(array('user_id' => $consumerId,
                'creation_date' => date('Y-m-d H:i:s')));

        $this->_testTblStub->expects($this->once())
            ->method('createTest')
            ->with(
                $this->callback(function ($_testObj) {
                    if ( // This function can't access the test's input variables
                        ($_testObj->teacherId == 4422) &&
                        ($_testObj->duration == 30) &&
                        ($_testObj->instructions == 'These are the instructions.') &&
                        ($_testObj->prompt == 'This is the prompt.') &&
                        ($_testObj->semesterId == 1) &&
                        ($_testObj->testTypeId == 2)
                    ) {
                        return true;
                    }
                    return false;
                })
            )
            ->willReturn($testObjReturn);

        $this->_codeTblStub->expects($this->never())
            ->method('getCodeByTestId');

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
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTeachersPOST_EmptyInput_All()
    {
        //Defining the mocked methods
        $this->createUtilMock(['nullCheckForTables']);

        //Input
        $accessToken = '';
        $duration = '';
        $instructions = '';
        $prompt = '';
        $semesterId = '';
        $testTypeId = '';

        //Output
        $emptyFieldName = [
            "accessToken cannot be empty.",
            "duration cannot be empty.",
            "instructions cannot be empty.",
            "prompt cannot be empty.",
            "semesterId cannot be empty.",
            "testTypeId cannot be empty.",
        ];
        $code = 'rest-101';
        $message = 'Empty Input';
        $details = $emptyFieldName;
        $httpCode = 400;

        $jsonInput = $this->createInputJson($accessToken, $duration, $instructions, $prompt, $semesterId, $testTypeId);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);

        $this->_utilStub->expects($this->never())
            ->method('nullCheckForTables');

        // IMPLEMENTING STUB's METHODS
        $this->_testTblStub->expects($this->never())
            ->method('createTest');

        $this->_codeTblStub->expects($this->never())
            ->method('getCodeByTestId');

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
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $duration = '30';
        $instructions = 'These are the instructions.';
        $prompt = 'This is the prompt.';
        $semesterId = 1;
        $testTypeId = 2;

        //Output
        $code = 'rest-102';
        $message = 'Invalid Access Token';
        $details = "The service requires a valid access token.";
        $httpCode = 401;

        $jsonInput = $this->createInputJson($accessToken, $duration, $instructions, $prompt, $semesterId, $testTypeId);
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

        $this->_testTblStub->expects($this->never())
            ->method('createTest');

        $this->_codeTblStub->expects($this->never())
            ->method('getCodeByTestId');


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

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $duration = '30';
        $instructions = 'These are the instructions.';
        $prompt = 'This is the prompt.';
        $semesterId = 'F';
        $testTypeId = 'E';
        $consumerId = Tests_POST_Test::TEACHER_ID;

        //Output
        $code = 'rest-103';
        $message = 'Expired Access Token';
        $details = "This access token has expired.";
        $httpCode = 401;

        $jsonInput = $this->createInputJson($accessToken, $duration, $instructions, $prompt, $semesterId, $testTypeId);
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
        $this->_testTblStub->expects($this->never())
            ->method('createTest');

        $this->_codeTblStub->expects($this->never())
            ->method('getCodeByTestId');


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
        $this->createTableMock('TokenTbl', ['retrieveToken']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $duration = '30';
        $instructions = 'These are the instructions.';
        $prompt = 'This is the prompt.';
        $semesterId = 1;
        $testTypeId = 2;

        //Output
        $code = 'proc-100';
        $message = 'Database Error';
        $pdoMessage = 'Message from PDOException';
        $details = "$pdoMessage [FILE: C:\\wamp64\\www\\php\\restful\\api\\v1\\tests\\unitTest\\teachers\\tests\\Tests_POST_Test.php] [LINE: 445]";
        $httpCode = 500;

        $jsonInput = $this->createInputJson($accessToken, $duration, $instructions, $prompt, $semesterId, $testTypeId);
        $jsonOutput = $this->createOutputJson($code, $message, $details);

        $this->serviceDataArray['payload'] = json_encode($jsonInput);


        // ASSERTING STUB's METHODS
        $this->_tokenTblStub->expects($this->once())
            ->method('retrieveToken')
            ->with(
                $this->stringContains($accessToken))
            ->will($this->throwException(new PDOException($pdoMessage, '000')));

        $this->_testTblStub->expects($this->never())
            ->method('createTest');

        $this->_codeTblStub->expects($this->never())
            ->method('getCodeByTestId');

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
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $duration = '30';
        $instructions = 'These are the instructions.';
        $prompt = 'This is the prompt.';
        $semesterId = 1;
        $testTypeId = 2;

        //Output
        $arrayName = 'DependencyArray';
        $elementName = $dependencyName;
        $code = 'rest-999';
        $message = 'Internal Error';
        $details = "$this->basenameFile => Element: $elementName from $arrayName is null or empty.";
        $httpCode = 500;

        $jsonInput = $this->createInputJson($accessToken, $duration, $instructions, $prompt, $semesterId, $testTypeId);
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
        $this->_testTblStub->expects($this->never())
            ->method('createTest');
        $this->_codeTblStub->expects($this->never())
            ->method('getCodeByTestId');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }


    private function createInputJson($accessToken, $duration, $instructions, $prompt, $semesterId, $testTypeId)
    {
        return array(
            'accessToken' => $accessToken,
            'duration' => $duration,
            'instructions' => $instructions,
            'prompt' => $prompt,
            'semesterId' => $semesterId,
            'testTypeId' => $testTypeId);
    }

}