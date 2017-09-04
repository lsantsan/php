
<main id="login_main">
    <div class="content_container" id="login_container">
        <div id="logo">
            <figure>
                <a href='<?php echo $this->home_logo_link ?>' title="Go to home page">
                    <img alt ="Logo" title="Logo Picture" src="views/images/logo.png"/> 
                </a>
                <figcaption></figcaption>
            </figure>
        </div>
        <div id="login">
            <form id="login_form" action = '<?php echo $lv_start_url ?>' method = "post">
                <label>Student Name</label>
                <br/>
                <input type = "text" name = "student_name" class = "box"/>
                <br/>
                <label>Code</label>
                <br/>
                <input type = "text" name = "test_code" class = "box" />
                <br/>
                <input class="button" type = "submit" value = "Start!"/><br />
            </form>
        </div>                      
        <div class="error_message_container">
            <span class="error_message" <?php echo isset($show_error) ? "style='" . $show_error . ";'" : "" ?>>
                <?php echo (isset($error_message) && $error_message != "") ? $error_message : "" ?>
            </span>              
        </div>
    </div>

</main>           
