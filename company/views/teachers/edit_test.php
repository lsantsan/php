
<main>
    <div>
        <?php require_once ('views/modules/sub_header.php'); ?>

        <div id="main_info">
            <div class="content_container" id="create_container">
                <div class="container_title"><span>Edit Test</span></div>
                <div id="container_form">
                    <form id="create_form" action='<?php echo $update_test_url . '&id=' . $lv_testObj->id ?>' method="post" >
                        <div id="row_container">
                            <div>
                                <label <?php echo isset($duration_error) ? $duration_error : "" ?>>Duration</label>                         
                                <br/>
                                <input id="duration_input" type="number" name="duration" min="0" max="120" step="1" value="<?php echo $lv_testObj->duration ?>"><span> minutes.</span>                        
                            </div>
                            <div>
                                <label>Semester</label>                     
                                <br/>
                                <select name="semester" id="semester_dropdown">
                                    <option value="W"<?php
                                        echo ($lv_testSemester == 'W') ? "selected='selected'" : ""
                                    ?>>Winter</option>
                                    <option value="S"<?php
                                        echo ($lv_testSemester== 'S') ? "selected='selected'" : ""
                                    ?>>Summer</option>
                                    <option value="F"<?php
                                    echo ($lv_testSemester == 'F') ? "selected='selected'" : ""
                                    ?>>Fall</option>                            
                                </select>                              
                            </div>
                            <div>
                                <label>Test Type</label>                     
                                <br/>
                                <select name="type" id="type_dropdown">
                                    <option value="E"<?php
                                        echo ($lv_testType == 'E') ? "selected='selected'" : ""
                                    ?>>Exit</option>
                                    <option value="J"<?php
                                        echo ($lv_testType == 'J') ? "selected='selected'" : ""
                                    ?>>Journal</option>
                                    <option value="T"<?php
                                        echo ($lv_testType == 'T') ? "selected='selected'" : ""
                                    ?>>Timed</option>
                                    <option value="M"<?php
                                        echo ($lv_testType == 'M') ? "selected='selected'" : "" 
                                    ?>>Mid-Term</option>
                                    <option value="F"<?php
                                        echo ($lv_testType == 'F') ? "selected='selected'" : ""
                                    ?>>Final</option>
                                </select>
                            </div>
                        </div>
                        <br/>
                        <label id="create_form_label" <?php echo isset($instructions_error) ? $instructions_error : "" ?>>Instructions</label>
                        <br/>
                        <textarea id="instructions_txtarea" name="instructions"  rows="3" cols="40"><?php echo $lv_testObj->instructions ?></textarea>
                        <br/>
                        <label id="create_form_label" <?php echo isset($prompt_error) ? $prompt_error : "" ?>>Prompt</label>
                        <br/>
                        <textarea id="topic_txtarea" name="prompt" rows="3" cols="40"><?php echo $lv_testObj->prompt ?></textarea>
                        <br/>
                        <div id="bottom_container">
                            <div class="error_message_container">
                                <span class="<?php echo isset($message_type) ? $message_type : "" ?>" 
                                      <?php echo isset($show_message) ? $show_message : "" ?>>
                                          <?php echo (isset($message) && $message != "") ? $message : "" ?>
                                </span>              
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
