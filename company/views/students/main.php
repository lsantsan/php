<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
<script language=JavaScript>
    var isEssaySubmitted = false;
    var autoSaveID;

    $(document).ready(loadVars);
    $(document).ready(runCountdown);
    $(document).ready(autoSave);
    //$(document).ready(confirmSubmit);


    $(function enable_tab() {
        $(document).delegate('#essay_textarea', 'keydown', function (e) {
            var keyCode = e.keyCode || e.which;

            if (keyCode == 9) {
                e.preventDefault();
                var start = $(this).get(0).selectionStart;
                var end = $(this).get(0).selectionEnd;

                // set textarea value to: text before caret + tab + text after caret
                $(this).val($(this).val().substring(0, start)
                        + "        "
                        + $(this).val().substring(end));

                // put caret at right position again
                $(this).get(0).selectionStart =
                        $(this).get(0).selectionEnd = start + 8;
            }
        });
    });

    function check_length() {
        var text = document.getElementById("essay_textarea").value;
        if (text == null || text == "") {
            //document.getElementById("word_count").value = "0";
            jQuery("label[id='word_count']").html("0");
        } else {
            //document.getElementById("word_count").value = text.match(/\S+/g).length;
            value = text.match(/\S+/g).length;
            jQuery("label[id='word_count']").html(value);
        }
    }

    function countdown(minutes, seconds) {
        if (seconds == null || seconds == 0) {
            var sec = 60;
            var mins = minutes - 1;
        } else {
            var sec = seconds;
            var mins = minutes;
        }
        //If minutes are less than 3, change timer's color changes to red.
        if (mins < 3) {
            changeColor('timer', 'red');
        }

        function tick() {
            if (isEssaySubmitted) { //Global variable                
                return;
            }

            var counter = document.getElementById("timer");
            var current_minutes = mins;
            sec--;
            var output = current_minutes.toString() + ":" + (sec < 10 ? "0" : "") + String(sec);
            counter.innerHTML = output;
            //Calls php file to save time on a Session variable.
            //$.get("controllers/ajax/saveTime_ajax.php", {time: output});
            if (sec > 0) {
                setTimeout(tick, 1000);
            } else {
                if (mins >= 1) {
                    setTimeout(function () {
                        countdown(mins);
                    }, 1000);
                } else { // IF TIME IS OVER, DO THIS....
                    submitEssay();
                    alert("Time is over! Your essay has been submited.");
                    return;
                }
            }
        }
        tick();
    }

    function submitEssay() {
        var codeID = document.getElementById("code_id").value;
        var studentName = document.getElementById("student_name").textContent;
        $('#essay_textarea').prop("disabled", true); //Locks textarea.
        $('#submit_essay_button').prop("disabled", true); //Locks submit button.
        var essayContent = document.getElementById("essay_textarea").value;
        var wordCount = document.getElementById("word_count").textContent;
        var duration = document.getElementById("duration").value;
        //If test is NOT timed, timeSpent is 0; otherwise, do math.
        if (duration == "0") {
            var timeSpent = "0";
        } else {
            //Calculate the time spent to write the essay
            var timeRemained = document.getElementById("timer").textContent;
            var timeSpent = calcTimeSpent(duration, timeRemained);
        }

        $.ajax({
            type: "POST",
            url: "?controller=students&action=submit_essay",
            data: {code_id: codeID, student_name: studentName, essay_content: essayContent, time_spent: timeSpent, word_count: wordCount},
            success: function () {
                $(".success_message").text("Essay has been submitted! You can logout now.");
                isEssaySubmitted = true;//Global variable
            }
        });

    }

    function runCountdown() {
        var timer_value = document.getElementById("timer").textContent;

        if (timer_value == "0")
            return;

        var array = timer_value.split(':');
        var minutes = array[0];
        var seconds = array[1];
        // If time is over, do not run countdown.
        if (minutes == 0 && seconds == 0) {
            return;
        }
        countdown(array[0], array[1]);
    }

    /* The AutoSave feature works in the following way:
     *      There are two saving processes that run every 5 seconds: In Session and In Database.
     *         -> In Session: The "saveEssayInSession()" calls a php file that will instantiate the Session variables.
     *                      The Student Controller will check if those Session variables were set. If so, 
     *                      it loads the saved info; otherwise, it loads info from objects.
     *         -> In Database: The "saveEssayInDatabase()" calls a php file that will insert or update the info on
     *                      the database. The process has the insert ID (autoSaveID) as the main reference to keep saving
     *                      the essay on the database as the time goes on. For the first access, the process starts 
     *                      on the Database by generating the autoSaveID. The first insert will generate the ID, which 
     *                      will be kept in the Session variable by saveEssayInSession(). After that, the ID will be used
     *                      only to update the record on the Database.
     *                        */
    function autoSave() {
        check_length();

        saveEssayInSession();
        saveEssayInDatabase();
        setTimeout(autoSave, 5 * 1000);
    }

    function saveEssayInDatabase() {
        var studentName = document.getElementById("student_name").textContent;
        var prompt = document.getElementById("prompt_content").textContent;
        var essayContent = document.getElementById("essay_textarea").value;
        var timePassed = document.getElementById("timer").textContent;

        $.ajax({
            type: "POST",
            url: "controllers/ajax/autoSave_ajax.php",
            data: {auto_save_id: autoSaveID, student_name: studentName, prompt: prompt, essay_content: essayContent, time_passed: timePassed},
            success: function (data) {
                autoSaveID = data;
            }
        });
    }

    function saveEssayInSession() {
        var essayContent = document.getElementById("essay_textarea").value;
        var timeSpent = document.getElementById("timer").textContent;

        $.ajax({
            type: "POST",
            url: "controllers/ajax/saveEssay_ajax.php",
            data: {auto_save_id: autoSaveID, essay_content: essayContent, time_passed: timeSpent},
            success: function () {
            }
        });
    }

    function loadVars() {
        autoSaveID = document.getElementById("auto_save_id").value;
    }

    function confirmSubmit() {
        if (confirm('Are you sure you want to submit your essay?')) {
            submitEssay();
            return true;
        } else {
            e.preventDefault();
            return false;
        }


    }

    function calcTimeSpent(duration, timeRemained) {
        //Convert duration in seconds.
        var durationInSecs = duration * 60;

        //Prepare timeRemained and convert to seconds.
        var splitted = timeRemained.split(":");
        var remainedMinutes = splitted[0];
        var remainedSeconds = splitted[1];
        remainedSeconds = +remainedSeconds + (+remainedMinutes * 60);
        //Calculate time used.
        var timeUsed = +durationInSecs - +remainedSeconds;
        //Convert seconds back to mm:ss format
        var usedMinutes = Math.floor(+timeUsed / 60);
        var usedSeconds = +timeUsed % 60;

        var result = usedMinutes.toString().concat(':', (usedSeconds < 10 ? "0" : ""), usedSeconds);
        return result;
    }

    function changeColor(id, newColor) {
        $('#' + id).css('color', newColor);
    }

</script>

<main>
    <div>
        <?php require_once ('views/modules/sub_header.php'); ?>

        <div id="main_info">
            <div class="content_container" id="writing_container">
                <div class="container_title"><span>Writing Test</span></div>
                <div id="container_form">
                    <form id="writing_form" action="" method="post" >
                        <div class="writing_label_line">
                            <div class="writing_left_col"> 
                                <label>Student: </label>
                                <label id="student_name"><?php echo $_SESSION['studentName'] ?></label>
                            </div>
                            <div class="writing_right_col">
                                <label>Duration: </label>
                                <label id="timer"><?php echo $lv_duration ?></label>
                                <input type="hidden" name="duration" id="duration" value="<?php echo $lv_testObj->duration ?>" />
                                <label> minutes</label>
                            </div>
                        </div>
                        <div class="writing_label_line">
                            <div>
                                <label class="writing_left_col">Teacher: <?php echo $lv_essayObj->teacherName ?></label>
                                <input type="hidden" id="code_id" value="<?php echo $lv_essayObj->codeObj->id ?>" />
                                <input type="hidden" id="auto_save_id" value="<?php echo $lv_autoSaveID ?>"/>
                            </div>
                            <div class="writing_right_col">
                                <label>Word Count: </label>                                     
                                <label id="word_count">0</label>
                                <label> words</label>
                            </div>
                        </div>                            
                        <label id="instructions_label">Instructions: <?php echo $lv_testObj->instructions ?></label>
                        <br/>
                        <label id="prompt_label" class="prompt_label">Prompt: </label>
                        <label id="prompt_content" class="prompt_label"><?php echo $lv_testObj->prompt ?></label>
                        <br/>
                        <textarea onKeyUp=check_length(); id="essay_textarea" name="essay" rows="10" cols="40"
                                  autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"><?php echo $lv_essayContent ?></textarea>
                        <br/> 

                        <div id="bottom_container">
                            <div class="error_message_container">
                                <span class="success_message" style='display:block;'>                                        
                                </span>              
                            </div>
                            <div class="button_container">
                                <input class="button" id="submit_essay_button" type="submit" value="Submit" onclick="confirmSubmit()"/>
                            </div>
                        </div>
                    </form>                                
                </div>
            </div>
        </div>   
    </div>
</main>           
