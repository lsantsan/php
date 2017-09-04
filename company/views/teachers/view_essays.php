<script type="text/javascript" src="views/js/jquery-1.12.3.js"></script> 
<script type="text/javascript">
    $(document).ready(function () {
        var checked = false;
        $("input[type=checkbox].check_all").click(function () {
            checked = !checked;
            $("input[type=checkbox].check_student").prop('checked', checked); // jQuery 1.6+            
        });
    });
</script>
<main>
    <div>
        <?php require_once ('views/modules/sub_header.php'); ?>

        <div id="main_info">
            <div class="content_container" id="student_list_container">
                <div class="container_title" id="student_list_container_title"><span>Student List</span></div>
                <div id="student_list_content_container">
                    <form id="student_list_form" action = '<?php echo $print_essay_url ?>' target="_blank" method="post" >
                        <div id="id_name_div">
                            <div>
                                <a href='<?php echo $view_essays_url . '&id=' . $lv_codeID . '&nameid=1' ?>'><label>ID/Name list</label></a>
                                <input type="checkbox" name="id_name_list_checkbox" id="id_name_list_checkbox" class="check_list" value="1" /><br />                                
                                <input type="hidden" name="code_id" value="<?php echo (isset($lv_codeID)) ? $lv_codeID : "" ?>">
                            </div>
                            <input type="hidden" name="teacher_fullname" value="<?php echo isset($lv_teacherObj) ? $lv_teacherObj->firstName . " " . $lv_teacherObj->lastName : "" ?>">
                        </div>
                        <div id="names_container">
                            <div id="all_essays_div">
                                <label>All essays</label>
                                <input type="checkbox" name="checkbox_all" class="check_all" value="all" />
                                <br/>
                            </div>   
                            <?php foreach ($lv_studentList as $key => $value) { ?>
                                <div id="name_container">
                                    <a href='<?php echo $view_essays_url . '&id=' . $lv_codeID . '&eid=' . $key ?>'><label><?php echo $value ?></label></a>
                                    <input type="checkbox" name="student_checkbox[]" class="check_student" value="<?php echo $key ?>" />
                                    <br />
                                </div>  
                            <?php } ?>                             
                        </div>
                        <div id="hide_names_div">
                            <div>
                                <input id="hide_names_checkbox" type="checkbox" name="hide_names_checkbox" value="1" />
                                <label>Hide student names</label>
                            </div>
                        </div>    
                        <div class="button_container">
                            <input class="button" type = "submit" value = "Print"/>
                        </div>
                    </form>     
                </div>
            </div>

            <!-- ******************** ESSAY VIEW **************************************  -->
            <div style="<?php echo (isset($lv_hideEssayView)) ? $lv_hideEssayView : "" ?>" class="content_container" id="essay_view_container">
                <div class="container_title" id="essay_view_container_title"><span>Essay View</span></div>
                <div id="container_form">
                    <form id="writing_form"  method="post" >
                        <div class="writing_label_line">
                            <div> 
                                <label class="writing_left_col">Student: <?php echo isset($lv_essayObj->studentName) ? $lv_essayObj->studentName : "" ?></label>
                            </div>
                            <div>
                                <label class="writing_right_col">Duration: <?php echo isset($lv_essayObj->timeSpent) ? $lv_essayObj->timeSpent : "" ?> minutes</label>
                            </div>
                        </div>
                        <div class="writing_label_line">
                            <div>
                                <label class="writing_left_col">Teacher: <?php echo isset($lv_teacherObj) ? $lv_teacherObj->firstName . " " . $lv_teacherObj->lastName : "" ?></label>
                            </div>
                            <div>
                                <label class="writing_right_col">Word Count: <?php echo isset($lv_essayObj->wordCount) ? $lv_essayObj->wordCount : "" ?> words</label>
                            </div>
                        </div>                            
                        <label id="instructions_label">Instructions: <?php echo isset($lv_testObj->instructions) ? $lv_testObj->instructions : "" ?></label>
                        <br/>
                        <label id="prompt_label" class="prompt_label">Prompt: <?php echo isset($lv_testObj->prompt) ? $lv_testObj->prompt : "" ?></label>
                        <br/>
                        <textarea disabled id="essay_textarea" name="essay" cols="40"><?php echo isset($lv_essayObj->content) ? $lv_essayObj->content : "" ?></textarea>                                    
                    </form>                                
                </div>
            </div>


            <!-- ******************** NAME/ID LIST **************************************  -->
            <div style="<?php echo (isset($lv_hideNameID)) ? $lv_hideNameID : "display:none;" ?>" class="content_container" id="essay_view_container">
                <div class="container_title" id="essay_view_container_title"><span>ID/Name List</span></div>
                <div id="container_form">
                    <ul id="nameID_list">   
                        <?php
                        if (isset($lv_nameIDList)) {
                            foreach ($lv_nameIDList as $key => $value) {
                                ?>
                                <li><span><?php echo $key ?></span><span><?php echo $value ?></span></li>
                                <?php
                            }
                        }
                        ?>                      
                    </ul>                           
                </div>
            </div>





        </div>   
    </div>
</main>           
