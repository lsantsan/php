<?php

namespace lsantsan\model;

class Test
{

    public $id;
    public $teacherId;
    public $codeId;
    public $duration;
    public $instructions;
    public $prompt;
    public $semesterId;
    public $testTypeId;
    public $isActive;
    public $creationDate;


    public function __construct($teacherId, $duration, $instructions, $prompt, $semesterId, $testTypeId, $id = null, $codeId = null, $isActive = null, $creationDate = null)
    {
        $this->id = $id;
        $this->teacherId = $teacherId;
        $this->codeId = $codeId;
        $this->duration = $duration;
        $this->instructions = $instructions;
        $this->prompt = $prompt;
        $this->semesterId = $semesterId;
        $this->testTypeId = $testTypeId;
        $this->isActive = $isActive;
        $this->creationDate = $creationDate;
    }


}