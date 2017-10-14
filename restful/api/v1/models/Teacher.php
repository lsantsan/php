<?php

namespace lsantsan\model;

class Teacher {

    // attributes are public so they can become json
    public $id;
    public $username;
    public $firstName;
    public $lastName;
    public $isAdmin;
    public $isActive;
    public $resetPassword;
    public $creationDate;

    public function __construct($id, $username, $firstName, $lastName, $isAdmin, $isActive, $resetPassword, $creationDate) {
        $this->id = $id;
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->isAdmin = $isAdmin;
        $this->isActive = $isActive;
        $this->resetPassword = $resetPassword;
        $this->creationDate = $creationDate;
    }

}
