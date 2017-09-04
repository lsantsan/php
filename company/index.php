<?php

    //http://requiremind.com/a-most-simple-php-mvc-beginners-tutorial/
    require_once('./../../db_config/connection.php');

    //Treating parameters.
    if (isset($_GET['controller']) && isset($_GET['action'])) {
        $controller = $_GET['controller'];
        $action = $_GET['action'];
    } else {
        $controller = 'pages';
        $action = 'home';
    }
    
    //Treating page title.
    $pos = strpos($action, '_');
    if ($pos === false) {
        $title = ucfirst($action);
    } else {
        $lowerCase = str_replace('_', " ", $action);
        $title = ucwords($lowerCase);
    }
    
    require_once('views/layout.php');
?>