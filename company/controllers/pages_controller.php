<?php

class PagesController {

    public function home() {
        $teacher_page = "?controller=teachers&action=main";
        $student_page = "?controller=students&action=main";
        require_once('views/pages/home.php');
    }

    public function error() {
        require_once('views/pages/error.php');
    }

}

?>