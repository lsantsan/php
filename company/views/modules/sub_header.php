<?php
//JAVA SCRIPT CONTROLLS WHO CAN SEE WHICH OPTIONS
//If this header is for a STUDENT, "My Account" option is DISABLED.
if (isset($_SESSION['studentName'])) {
    echo '<style type="text/css">
        #sub_header_gretting ul li ul.dropdown li:not(#log_out_option){
        display: none;
        }
        </style>';
}
//If this header is for a TEACHER, "Admin" option is DISABLED.
if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 0 ) {   
    echo '<style type="text/css">
        #sub_header_gretting ul li ul.dropdown #admin_option {
        display: none;
        }
        </style>';
}
?>

<div id="sub_header">
    <div id="sub_header_logo">
        <a href='<?php echo $this->logo_link ?>' title="Go to home page">
            <img alt ="Logo" title="Logo Picture" src="views/images/logo.png"/> 
        </a>
    </div>
    <div id="sub_header_gretting">
        <h1>Hello, 
            <ul id="user_options">
                <li>
                    <a id="username"><?php echo $_SESSION['first_name'] ?>
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown">
                        <?php //JAVA SCRIPT CONTROLLS WHO CAN SEE WHICH OPTIONS ?>
                        <li id="my_account_option" ><a href='<?php echo (isset($this->my_account_link)) ? $this->my_account_link : "" ?>'>My Account</a></li>
                        <li id="admin_option" ><a href='<?php echo (isset($_SESSION['isAdmin'])) ? $this->admin_or_teacher_link : "" ?>'><?php echo (get_class($this)=="TeachersController")?"Admin" : "Teacher"; ?></a></li>
                        <li id="log_out_option"><a href='<?php echo $this->logout_link ?>'>Log out</a></li>
                    </ul>
                </li>
            </ul>

        </h1>
    </div>
</div>