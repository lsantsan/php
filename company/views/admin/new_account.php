
<main>
    <div>
        <?php require_once ('views/modules/sub_header.php'); ?>

        <div id="main_info">
            <div class="content_container" id="create_container">
                <div class="container_title"><span>Create Account</span></div>
                <div id="container_form">
                    <form id="create_account_form" action='<?php echo $create_account_url ?>' method="post" >
                        <div id="row_container">
                            <div>
                                <label <?php echo isset($firstName_error) ? $firstName_error : "" ?>>First Name</label>                         
                                <br/>
                                <input id="firstName_input" type="text" name="first_name" value="<?php echo isset($_POST['first_name']) ? $_POST['first_name'] : '' ?>"/>
                            </div>
                            <div>
                                <label <?php echo isset($lastName_error) ? $lastName_error : "" ?>>Last Name</label>                     
                                <br/>
                                <input id="lastName_input" type="text" name="last_name" value="<?php echo isset($_POST['last_name']) ? $_POST['last_name'] : '' ?>"/>
                            </div>                            
                        </div>
                        <br/>
                        <div id="row_container">
                            <div>
                                <label id="create_form_label" <?php echo isset($username_error) ? $username_error : "" ?>>Username</label>
                                <br/>
                                <input id="username_input" type="text" name="username" value="<?php echo isset($_POST['username']) ? $_POST['username'] : '' ?>"/>
                            </div>
                            <div>
                                <label id="create_form_label">Is Admin?</label>
                                <br/>
                                <select name="is_admin" id="isAdmin_dropdown">                                    
                                    <option value="0"<?php
                                    echo isset($_POST['is_admin']) ?
                                            ($_POST['is_admin'] == '0') ? "selected='selected'" : "" : ""
                                    ?>>No</option>
                                    <option value="1"<?php
                                    echo isset($_POST['is_admin']) ?
                                            ($_POST['is_admin'] == '1') ? "selected='selected'" : "" : ""
                                    ?>>Yes</option>
                                </select>
                            </div>
                        </div>
                        <br/>
                        <div id="bottom_container">
                            <div class="error_message_container">
                                <span class="<?php echo isset($message_type) ? $message_type : "" ?>" 
                                      <?php echo isset($show_message) ? $show_message : "" ?>>
                                          <?php echo (isset($message) && $message != "") ? $message : "" ?>
                                </span>              
                            </div>
                            <div class="button_container">
                                <input <?php echo isset($lv_isDisabled) ? $lv_isDisabled : "" ?> class="button" id="create_button" type = "submit" value = "Create"/>
                            </div>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>   
</div>
</main>           
