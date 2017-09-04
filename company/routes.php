<?php

function call($controller, $action) {
    require_once('controllers/' . $controller . '_controller.php');

    switch ($controller) {
        case 'pages':
            $controller = new PagesController();
            break;
        case 'teachers':
            require_once('models/teacher.php');
            $controller = new TeachersController();
            break;
        case 'students':
            //require_once('models/student.php');
            $controller = new StudentsController();
            break;
        case 'admin':
            //require_once('models/student.php');
            $controller = new AdminController();
            break;
    }

    $controller->{ $action }();
}

// we're adding an entry for the new controller and its actions
$controller_list = array('pages' => ['home', 'error'],
                         'teachers' => ['main', 'new_test', 'view_essays', 'login'
                                       , 'submit_login', 'logout', 'create_test'
                                       , 'edit_test', 'update_test','remove_test'
                                       , 'edit_account', 'update_account', 'teacher_logo_link'],
                         'students' => ['main', 'login', 'logout', 'start_test', 'submit_essay'
                                       , 'auto_save'],
                         'admin' => [ 'main', 'new_account', 'edit_teacher_account'
                                    , 'create_account', 'remove_account', 'update_teacher_account']);

if (array_key_exists($controller, $controller_list)) {
    if (in_array($action, $controller_list[$controller])) {
        call($controller, $action);
    } else {
        call('pages', 'error');
    }
} else {
    call('pages', 'error');
}
?>