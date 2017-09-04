
<main id="login_main">
    <div id="login_container">
        <div id="logo">
            <figure>
                <a href='<?php echo $this->home_logo_link ?>' title="Go to home page">
                    <img alt ="Logo" title="Logo Picture" src="views/images/logo.png"/> 
                </a>
                <figcaption></figcaption>
            </figure>
        </div>
        <div id="login">
            <form id="login_form" action = '<?php echo $submit_login_url ?>' method = "post">
                <label>Username</label>
                <br/>
                <input type = "text" name = "teacher_username" class = "box" autofocus/>
                <br/>
                <label>Password</label>
                <br/>
                <input type = "password" name = "teacher_password" class = "box" />
                <br/>
                <input class="button" type = "submit" value = "Login"/>
            </form>
        </div>  
        <div class="error_message_container">
            <span class="error_message" <?php echo isset($show_error)?"style='".$show_error.";'":""?>>
                <?php echo (isset($error_message) && $error_message != "")?$error_message:"" ?>
            </span>              
        </div>
    </div>

</main>           
