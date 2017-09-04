<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
<script language=JavaScript>
    //Disable Create button.
    $(document).ready(function () {
        $("#create_test_form").submit(function () {
           $('#create_button').prop("disabled", true); //Locks submit button.
        });
    });
</script>
<main>
    <div>
        <?php require_once ('views/modules/sub_header.php'); ?>

        <div id="main_info">
            <div class="content_container" id="create_container">
                <div class="container_title"><span>Create Test</span></div>
                <div id="container_form">
                    <form id="create_test_form" action='<?php echo $create_test_url ?>' method="post" >
                        <div id="row_container">
                            <div>
                                <label <?php echo isset($duration_error) ? $duration_error : "" ?>>Duration</label>                         
                                <br/>
                                <input id="duration_input" type="number" name="duration" min="0" max="120" step="1" value="<?php echo isset($_POST['duration']) ? $_POST['duration'] : '30' ?>"><span> minutes.</span>                        
                            </div>
                            <div>
                                <label>Semester</label>                     
                                <br/>
                                <select name="semester" id="semester_dropdown">
                                    <option value="W"<?php
                                    echo isset($_POST['semester']) ?
                                            ($_POST['semester'] == 'W') ? "selected='selected'" : "" : ""
                                    ?>>Winter</option>
                                    <option value="S"<?php
                                    echo isset($_POST['semester']) ?
                                            ($_POST['semester'] == 'S') ? "selected='selected'" : "" : ""
                                    ?>>Summer</option>
                                    <option value="F"<?php
                                    echo isset($_POST['semester']) ?
                                            ($_POST['semester'] == 'F') ? "selected='selected'" : "" : ""
                                    ?>>Fall</option>                            
                                </select>                              
                            </div>
                            <div>
                                <label>Test Type</label>                     
                                <br/>
                                <select name="type" id="type_dropdown">
                                    <option value="E"<?php
                                    echo isset($_POST['type']) ?
                                            ($_POST['type'] == 'E') ? "selected='selected'" : "" : ""
                                    ?>>Exit</option>
                                    <option value="J"<?php
                                    echo isset($_POST['type']) ?
                                            ($_POST['type'] == 'J') ? "selected='selected'" : "" : ""
                                    ?>>Journal</option>
                                    <option value="T"<?php
                                    echo isset($_POST['type']) ?
                                            ($_POST['type'] == 'T') ? "selected='selected'" : "" : ""
                                    ?>>Timed</option>
                                    <option value="M"<?php
                                    echo isset($_POST['type']) ?
                                            ($_POST['type'] == 'M') ? "selected='selected'" : "" : ""
                                    ?>>Mid-Term</option>
                                    <option value="F"<?php
                                    echo isset($_POST['type']) ?
                                            ($_POST['type'] == 'F') ? "selected='selected'" : "" : ""
                                    ?>>Final</option>
                                    <option value="P"<?php
                                    echo isset($_POST['type']) ?
                                            ($_POST['type'] == 'F') ? "selected='selected'" : "" : ""
                                    ?>>Placement</option>
                                </select>
                            </div>
                        </div>
                        <br/>
                        <label id="create_form_label" <?php echo isset($instructions_error) ? $instructions_error : "" ?>>Instructions</label>
                        <br/>
                        <textarea id="instructions_txtarea" name="instructions"  rows="3" cols="40"><?php echo isset($_POST['instructions']) ? $_POST['instructions'] : '' ?></textarea>
                        <br/>
                        <label id="create_form_label" <?php echo isset($prompt_error) ? $prompt_error : "" ?>>Prompt</label>
                        <br/>
                        <textarea id="topic_txtarea" name="prompt" rows="3" cols="40"><?php echo isset($_POST['prompt']) ? $_POST['prompt'] : '' ?></textarea>
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
