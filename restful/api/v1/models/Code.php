<?php

namespace lsantsan\model;

class Code {

    public $firstPart;
    public $lastDigits;
    public $isActive;

    public function __construct($firstPart, $lastDigits, $isActive) {
        $this->firstPart = $firstPart;
        $this->lastDigits = $lastDigits;
        $this->isActive = $isActive;
    }

}
