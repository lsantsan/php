<?php

namespace lsantsan\model;

class TestType
{

    // attributes are public so they can become json
    public $id;
    public $name;

    public function __construct($id, $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

}