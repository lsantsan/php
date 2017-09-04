<script type="text/javascript" src="views/js/jquery-1.12.3.js"></script> 
<script>
//Confirms RESET password.
    $(document).ready(function () {
        $("#reset_button").click(function (e) {
            if (!confirm('Are you sure you want to reset this password?')) {
                e.preventDefault();
                return false;
            }

            var adminID = document.getElementById("admin_id").value;
            var teacherID = document.getElementById("teacher_id").value;
            $.ajax({
                type: "POST",
                url: "controllers/ajax/resetPassword_ajax.php",
                data: {admin_id: adminID, teacher_id: teacherID},
                success: function (data) {
                    if (data == "1") {                        
                        document.getElementById("edit_teacher_account_form").submit(); //Triggers success message.    
                        alert ("New password is rest123");
                        return true;
                    } else {                        
                        alert("Sorry, reset password failed!");                        
                        return false;

                    }

                }
            });

        });
    });
</script>


<main>
    <div>
        <?php require_once ('views/modules/sub_header.php'); ?>

        <div id="main_info">
            <div class="teacher_title_container">
                <h1>Teacher: <?php echo isset($lv_teacherObj) ? $lv_teacherObj->firstName . " " . $lv_teacherObj->lastName : "" ?></h1>
            </div>
            <div class="content_container" id="create_container">
                <div class="container_title"><span>Edit Account</span></div>
                <div id="container_form">
                    <form id="edit_teacher_account_form" onsubmit="event.preventExtensions()" action='<?php echo $update_teacher_account_url ?>' method="post" >
                        <div id="row_container">
                            <div>
                                <label <?php echo isset($firstName_error) ? $firstName_error : "" ?>>First Name</label>                         
                                <br/>
                                <input type="text" name="first_name" value="<?php echo isset($lv_teacherObj) ? $lv_teacherObj->firstName : "" ?>"/>
                                <input type="hidden" name="teacher_id" id="teacher_id" value="<?php echo $lv_teacherObj->id ?>"/>
                                <input type="hidden" name="admin_id" id="admin_id" value="<?php echo $lv_adminObj->id ?>"/>
                            </div>
                            <div>
                                <label <?php echo isset($lastName_error) ? $lastName_error : "" ?>>Last Name</label>                     
                                <br/>
                                <input type="text" name="last_name" value="<?php echo isset($lv_teacherObj) ? $lv_teacherObj->lastName : "" ?>"/>                          
                            </div>                            
                        </div>
                        <br/>
                        <div id="row_container">
                            <div>
                                <label id="create_form_label" <?php echo isset($username_error) ? $username_error : "" ?>>Username</label>
                                <br/>
                                <input type="text" name="username" value="<?php echo isset($lv_teacherObj) ? $lv_teacherObj->username : "" ?>"/>
                            </div>
                            <div>
                                <label id="create_form_label">Password</label>
                                <br/>
                                <input type="button" id="reset_button" class="button" value="Reset"></input>
                            </div>
                        </div>                       
                        <br/>
                        <div id="bottom_container">
                            <div class="error_message_container">
                                <?php
                                if (isset($lv_messages)) {
                                    foreach ($lv_messages as $message) {
                                        ?>
                                        <span id="span_message" class="<?php echo isset($lv_message_type) ? $lv_message_type : "" ?>" 
                                              <?php echo isset($show_message) ? $show_message : "" ?>>
                                                  <?php echo (isset($message) && $message != "") ? $message : "" ?>
                                        </span> 
                                        <?php
                                    }
                                }
                                ?>
                            </div>
                            <div class="button_container">
                                <input <?php echo isset($lv_isDisabled) ? $lv_isDisabled : "" ?>  class="button" type = "submit" value = "Update"/>
                            </div>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>   
</div>
</main>           
