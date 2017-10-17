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


class Tests_PUT_Test extends BasicTestCase
{

    const TEACHER_ID = 4422;
    const TEST_ID = 13;
    protected $serviceDataArray = array(
        'method' => 'PUT',
        'url' => [Tests_PUT_Test::TEACHER_ID, 'tests', Tests_PUT_Test::TEST_ID]); //URL: /{teacherId}/tests/{testId}
    protected $requiredInputsArray = ['accessToken', 'duration', 'instructions', 'prompt', 'semesterId', 'testTypeId'];
    protected $basenameFile = 'Tests.php';

    public function testTestsPUT_Success_Keep_Code() //Database's procedure should NOT change test's code
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TestTbl', ['getTestByTestId', 'updateTest']);
        $this->createTableMock('TeacherTbl', ['isTeacherAdmin']);
        $this->createTableMock('CodeTbl', ['getCodeByTestId']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $currentDuration = 30;
        $currentInstructions = 'These are the instructions.';
        $currentPrompt = 'This is the prompt.';
        $newDuration = 20;
        $newInstructions = 'These are the instructions_new.';
        $newPrompt = 'This is the prompt_new.';
        $semesterId = 1;
        $testTypeId = 2;
        $testId = Tests_PUT_Test::TEST_ID;
        $consumerId = Tests_PUT_Test::TEACHER_ID;
        $testObjReturn = new Test($consumerId, $currentDuration, $currentInstructions, $currentPrompt, $semesterId, $testTypeId, $testId);

        //Output
        $codeId = 3;
        $currentYear = substr(date("Y"), 2, 3); //Last two digits of the year.
        $codeFirstPart = 'F' . $currentYear . 'J';
        $lastDigits = '451';
        $testCode = $codeFirstPart . $lastDigits;
        $code = 'rest-200';
        $message = 'Test updated';
        $details = array('testCode' => $testCode);

        $jsonInput = $this->createInputJson($accessToken, $newDuration, $newInstructions, $newPrompt, $semesterId, $testTypeId);
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
            ->method('getTestByTestId')
            ->with(Tests_PUT_Test::TEST_ID)
            ->willReturn($testObjReturn);

        $this->_testTblStub->expects($this->once())
            ->method('updateTest')
            ->with(
                $this->callback(function ($_newTestObj) {
                    if ( // This function can't access the test's input variables
                        ($_newTestObj->teacherId == 4422) &&
                        ($_newTestObj->duration == 20) &&
                        ($_newTestObj->instructions == 'These are the instructions_new.') &&
                        ($_newTestObj->prompt == 'This is the prompt_new.') &&
                        ($_newTestObj->semesterId == 1) &&
                        ($_newTestObj->testTypeId == 2)
                    ) {
                        return true;
                    }
                    return false;
                })
            )
            ->willReturn(1);

        $this->_teacherTblStub->expects($this->once())
            ->method('isTeacherAdmin')
            ->with(
                $this->stringContains(Tests_PUT_Test::TEACHER_ID))
            ->willReturn(1);

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

    public function testTestsPUT_Success_New_Code() //Database's procedure SHOULD change test's code
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TestTbl', ['getTestByTestId', 'updateTest']);
        $this->createTableMock('TeacherTbl', ['isTeacherAdmin']);
        $this->createTableMock('CodeTbl', ['getCodeByTestId']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $currentDuration = 30;
        $currentInstructions = 'These are the instructions.';
        $currentPrompt = 'This is the prompt.';
        $currentSemesterId = 1;
        $currentTestTypeId = 2;
        $newSemesterId = 4;
        $newTestTypeId = 3;
        $testId = Tests_PUT_Test::TEST_ID;
        $consumerId = Tests_PUT_Test::TEACHER_ID;
        $testObjReturn = new Test($consumerId, $currentDuration, $currentInstructions, $currentPrompt, $currentSemesterId, $currentTestTypeId, $testId);

        //Output
        $codeId = 3;
        $currentYear = substr(date("Y"), 2, 3); //Last two digits of the year.
        $codeFirstPart = 'F' . $currentYear . 'J';
        $lastDigits = '451';
        $testCode = $codeFirstPart . $lastDigits;
        $code = 'rest-200';
        $message = 'Test updated';
        $details = array('testCode' => $testCode);

        $jsonInput = $this->createInputJson($accessToken, $currentDuration, $currentInstructions, $currentPrompt, $newSemesterId, $newTestTypeId);
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
            ->method('getTestByTestId')
            ->with(Tests_PUT_Test::TEST_ID)
            ->willReturn($testObjReturn);

        $this->_testTblStub->expects($this->once())
            ->method('updateTest')
            ->with(
                $this->callback(function ($_newTestObj) {
                    if ( // This function can't access the test's input variables
                        ($_newTestObj->teacherId == 4422) &&
                        ($_newTestObj->duration == 30) &&
                        ($_newTestObj->instructions == 'These are the instructions.') &&
                        ($_newTestObj->prompt == 'This is the prompt.') &&
                        ($_newTestObj->semesterId == 4) &&
                        ($_newTestObj->testTypeId == 3)
                    ) {
                        return true;
                    }
                    return false;
                })
            )
            ->willReturn(1);

        $this->_teacherTblStub->expects($this->once())
            ->method('isTeacherAdmin')
            ->with(
                $this->stringContains(Tests_PUT_Test::TEACHER_ID))
            ->willReturn(1);

        $this->_codeTblStub->expects($this->once())
            ->method('getCodeByTestId')
            ->with(
                $this->stringContains($testId)
            )
            ->willReturn(new Code($codeId, $codeFirstPart, $lastDigits, 1));

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $serviceOutput = $teacherService->runService();
        $this->assertJsonStringEqualsJsonString(
            json_encode($jsonOutput)
            , json_encode($serviceOutput)
        );
    }

    public function testTestPUT_Test_Not_Found()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);
        $this->createTableMock('TestTbl', ['getTestByTestId', 'updateTest']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $duration = 30;
        $instructions = 'These are the instructions.';
        $prompt = 'This is the prompt.';
        $semesterId = 1;
        $testTypeId = 2;
        $testId = Tests_PUT_Test::TEST_ID;
        $consumerId = Tests_PUT_Test::TEACHER_ID;

        //Output
        $code = 'rest-110';
        $message = 'Record Not Found';
        $details = "Test not found.";
        $httpCode = 404;

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

        // IMPLEMENTING STUB's METHODS
        $this->_testTblStub->expects($this->once())
            ->method('getTestByTestId')
            ->with(Tests_PUT_Test::TEST_ID)
            ->willReturn(NULL);

        $this->_testTblStub->expects($this->never())
            ->method('updateTest');

        $this->_teacherTblStub->expects($this->never())
            ->method('isTeacherAdmin');

        $this->_codeTblStub->expects($this->never())
            ->method('getCodeByTestId');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTestPUT_Consumer_Cannot_Modify_Test()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);
        $this->createTableMock('TestTbl', ['getTestByTestId', 'updateTest']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $duration = 30;
        $instructions = 'These are the instructions.';
        $prompt = 'This is the prompt.';
        $semesterId = 1;
        $testTypeId = 2;
        $testId = Tests_PUT_Test::TEST_ID;
        $consumerId = Tests_PUT_Test::TEACHER_ID;
        $aTeacherId = 456;
        $testObjReturn = new Test($aTeacherId, $duration, $instructions, $prompt, $semesterId, $testTypeId, $testId);

        //Output
        $code = 'rest-104';
        $message = 'Action Denied';
        $details = "Consumer cannot modify this test.";
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

        // IMPLEMENTING STUB's METHODS
        $this->_testTblStub->expects($this->once())
            ->method('getTestByTestId')
            ->with(Tests_PUT_Test::TEST_ID)
            ->willReturn($testObjReturn);

        $this->_testTblStub->expects($this->never())
            ->method('updateTest');

        $this->_codeTblStub->expects($this->never())
            ->method('getCodeByTestId');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();

    }

    public function testTestPUT_Update_Fail()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);
        $this->createTableMock('TestTbl', ['getTestByTestId', 'updateTest']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $duration = 30;
        $instructions = 'These are the instructions.';
        $prompt = 'This is the prompt.';
        $semesterId = 1;
        $testTypeId = 2;
        $testId = Tests_PUT_Test::TEST_ID;
        $consumerId = Tests_PUT_Test::TEACHER_ID;
        $testObjReturn = new Test($consumerId, $duration, $instructions, $prompt, $semesterId, $testTypeId, $testId);

        //Output
        $code = 'rest-999';
        $message = 'Internal Error';
        $details = "A problem happened while updating test.";
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

        // IMPLEMENTING STUB's METHODS
        $this->_testTblStub->expects($this->once())
            ->method('getTestByTestId')
            ->with(Tests_PUT_Test::TEST_ID)
            ->willReturn($testObjReturn);

        $this->_testTblStub->expects($this->once())
            ->method('updateTest')
            ->with($this->anything())
            ->willReturn(0);

        $this->_codeTblStub->expects($this->never())
            ->method('getCodeByTestId');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTestPUT_Code_Fail()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);
        $this->createTableMock('TestTbl', ['getTestByTestId', 'updateTest']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $duration = 30;
        $instructions = 'These are the instructions.';
        $prompt = 'This is the prompt.';
        $semesterId = 1;
        $testTypeId = 2;
        $testId = Tests_PUT_Test::TEST_ID;
        $consumerId = Tests_PUT_Test::TEACHER_ID;
        $testObjReturn = new Test($consumerId, $duration, $instructions, $prompt, $semesterId, $testTypeId, $testId);

        //Output
        $code = 'rest-999';
        $message = 'Internal Error';
        $details = "A problem happened while getting code.";
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

        // IMPLEMENTING STUB's METHODS
        $this->_testTblStub->expects($this->once())
            ->method('getTestByTestId')
            ->with(Tests_PUT_Test::TEST_ID)
            ->willReturn($testObjReturn);

        $this->_testTblStub->expects($this->once())
            ->method('updateTest')
            ->with($this->anything())
            ->willReturn(1);

        $this->_codeTblStub->expects($this->once())
            ->method('getCodeByTestId')
            ->with($this->stringContains($testId))
            ->willReturn(NULL);

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();

    }

    public function testTestsPUT_EmptyInput_All()
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

        $this->_tokenTblStub->expects($this->never())
            ->method('retrieveToken');

        $this->_testTblStub->expects($this->never())
            ->method('getTestByTestId');

        $this->_testTblStub->expects($this->never())
            ->method('updateTest');

        $this->_teacherTblStub->expects($this->never())
            ->method('isTeacherAdmin');

        $this->_codeTblStub->expects($this->never())
            ->method('getCodeByTestId');

        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTestsPUT_ExpiredToken()
    {
        //Defining the mocked methods
        $this->createUtilMock(['checkInput', 'nullCheckForTables']);
        $this->createTableMock('TokenTbl', ['retrieveToken']);

        //Input
        $accessToken = '9c8838a2942e47dff29e5b5dd3f0d9d6';
        $duration = 30;
        $instructions = 'These are the instructions.';
        $prompt = 'This is the prompt.';
        $semesterId = 4;
        $testTypeId = 3;
        $consumerId = Tests_PUT_Test::TEACHER_ID;

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
            ->method('getTestByTestId');

        $this->_testTblStub->expects($this->never())
            ->method('updateTest');

        $this->_teacherTblStub->expects($this->never())
            ->method('isTeacherAdmin');

        $this->_codeTblStub->expects($this->never())
            ->method('getCodeByTestId');


        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTestsPUT_CatchDatabaseException()
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
        $details = "$pdoMessage [FILE: C:\\wamp64\\www\\php\\restful\\api\\v1\\tests\\unitTest\\teachers\\tests\\Tests_PUT_Test.php] [LINE: 608]";
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

        // IMPLEMENTING STUB's METHODS
        $this->_testTblStub->expects($this->never())
            ->method('getTestByTestId');

        $this->_testTblStub->expects($this->never())
            ->method('updateTest');

        $this->_teacherTblStub->expects($this->never())
            ->method('isTeacherAdmin');

        $this->_codeTblStub->expects($this->never())
            ->method('getCodeByTestId');
        //TRIGGERING CALL
        $teacherService = new TeachersServiceController($this->serviceDataArray, $this->dependencyArray);
        $this->expectException(ServiceException::class);
        $this->expectExceptionCode($httpCode);
        $this->expectExceptionMessage(json_encode($jsonOutput));
        $teacherService->runService();
    }

    public function testTestsPUT_DatabaseDependencyNull()
    {
        $this->checkDependency('database');
    }

    public function testTestsPUT_UtilDependencyNull()
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