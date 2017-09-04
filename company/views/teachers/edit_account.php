<script type="text/javascript" src="views/js/jquery-1.12.3.js"></script> 
<script type="text/javascript">
    //Checks if user has to change password.
    $(document).ready(function () {
        var resetPassword = $('#reset_password').val();
        if (resetPassword == 1) {
            alert("This is your first time loging in.\nYou need to change your password!");
        }
    });
</script>

<main>
    <div>
        <?php require_once ('views/modules/sub_header.php'); ?>

        <div id="main_info">
            <div class="content_container" id="create_container">
                <div class="container_title"><span>Edit Account</span></div>
                <div id="container_form">
                    <form id="edit_teacher_account_form" action='<?php echo $update_account_url ?>' method="post" >
                        <div id="row_container">
                            <div>
                                <label <?php echo isset($firstName_error) ? $firstName_error : "" ?>>First Name</label>                         
                                <br/>
                                <input type="text" name="first_name" value="<?php echo $lv_teacherObj->firstName ?>"/>                                
                            </div>
                            <div>
                                <label <?php echo isset($lastName_error) ? $lastName_error : "" ?>>Last Name</label>                     
                                <br/>
                                <input type="text" name="last_name" value="<?php echo $lv_teacherObj->lastName ?>"/>                          
                            </div>                            
                        </div>
                        <br/>
                        <div id="row_container">
                            <div>
                                <label id="create_form_label" <?php echo isset($username_error) ? $username_error : "" ?>>Username</label>
                                <br/>
                                <input type="text" name="username" value="<?php echo $lv_teacherObj->username ?>"/>
                            </div>
                            <div>
                                <label id="create_form_label" <?php echo isset($currentPassword_error) ? $currentPassword_error : "" ?>>Current Password</label>                                
                                <br/>                                
                                <input type="password" name="current_password"/><span>(Complete only if changing password)</span>
                                <input type="hidden" id="reset_password" value="<?php echo $lv_teacherObj->resetPassword ?>"/>
                            </div>
                        </div>
                        <div id="row_container">
                            <div>
                                <label id="create_form_label" <?php echo isset($newPassword_1_error) ? $newPassword_1_error : "" ?>>New Password</label>
                                <br/>
                                <input type="password" name="new_password_1"/>
                            </div>
                            <div>
                                <label id="create_form_label" <?php echo isset($newPassword_2_error) ? $newPassword_2_error : "" ?>>Confirm New Password</label>
                                <br/>
                                <input type="password" name="new_password_2"/>
                            </div>
                        </div>
                        <br/>
                        <div id="bottom_container">
                            <div class="error_message_container">
                                <?php if (isset($lv_messages)) {
                                    foreach ($lv_messages as $message) {
                                        ?>
                                        <span class="<?php echo isset($lv_message_type) ? $lv_message_type : "" ?>" 
                                                  <?php echo isset($show_message) ? $show_message : "" ?>>
                                        <?php echo (isset($message) && $message != "") ? $message : "" ?>
                                        </span> 
                                    <?php }
                                }
                                ?>
                            </div>
                            <div class="button_container">
                                <input <?php echo isset($lv_isDisabled) ? $lv_isDisabled : "" ?> class="button" type = "submit" value = "Update"/>
                            </div>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>   
</div>
</main>           
