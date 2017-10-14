<?php

namespace lsantsan\model;

class Code {

    // attributes are public so they can become json
    public $id;
    public $firstPart;
    public $lastDigits;
    public $isActive;

    public function __construct($id, $firstPart, $lastDigits, $isActive) {
        $this->id = $id;
        $this->firstPart = $firstPart;
        $this->lastDigits = $lastDigits;
        $this->isActive = $isActive;
    }

}
