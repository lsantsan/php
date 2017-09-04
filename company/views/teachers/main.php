<script type="text/javascript" src="views/js/jquery-1.12.3.js"></script>
<script type="text/javascript" src="views/js/jquery.tablesorter.js"></script>
<script>
    //Hides test list by default
    $(document).ready(function () {
        $(".hide").hide();
        $(".showFirst").show();
    });

    //Makes test table sortable.
    $(document).ready(function () {
        $("#history_table").tablesorter();
    });

    //Confirms test removal.
    $(document).ready(function () {
        $("a.delete").click(function (e) {
            if (!confirm('Are you sure you want to delete this test?')) {
                e.preventDefault();
                return false;
            }
            return true;
        });
    });

    //Hides table rows on click
    $(document).ready(function () {
        $('[data-toggle="toggle"]').change(function () {
            $(this).parents().next('.hide').toggle();
        });
    });

</script>
<main>
    <div>
        <?php require_once('views/modules/sub_header.php'); ?>

        <div id="main_info">
            <div>
                <button class='button create_button' id='new_test_button' type='button'
                        onclick='<?php echo "window.location.href = \"" . $create_test_url . "\"" ?>'>+ New Test
                </button>
            </div>
            <div>
                <table id="history_table" class="tablesorter">
                    <colgroup span="4"></colgroup>
                    <thead>
                    <tr>
                        <th>Code</th>
                        <th>Prompt</th>
                        <th>Time (minutes)</th>
                        <th>Actions</th>
                    </tr>
                    </thead>

                    <?php $showFirst = true;
                    foreach ($testListGroupedBySemester as $groupName => $subArray) { ?>
                        <tbody class="labels">
                        <tr>
                            <td colspan="5">
                                <label for="<?php echo $groupName; ?>"><?php echo $groupName; ?></label>
                                <input type="checkbox" id="<?php echo $groupName; ?>"
                                       data-toggle="toggle">
                            </td>
                        </tr>
                        </tbody>


                        <tbody class="hide <?php if ($showFirst) {
                            echo "showFirst";
                            $showFirst = false;
                        } ?>">
                        <?php foreach ($subArray as $test) { ?>
                            <tr>
                                <td class="code_column"><?php echo $test->codeObj->firstPart . $test->codeObj->lastDigits; ?></td>
                                <td class="topic_column"><?php echo $test->prompt; ?></td>
                                <td class="time_column"><?php echo $test->duration; ?></td>
                                <td class="actions_column">
                                    <a href='<?php echo $view_essays_url . '&id=' . $test->codeObj->id ?>'><img
                                                class="view_essays_img" alt="View" title="View Essays"
                                                src="views/images/doc.png"/></a>
                                    <a href='<?php echo $edit_test_url . '&id=' . $test->id ?>'><img alt="Edit"
                                                                                                     title="Edit Test"
                                                                                                     src="views/images/edit.png"/></a>
                                    <a href='<?php echo $remove_test_url . '&id=' . $test->id ?>' class="delete"><img
                                                alt="Remove" title="Remove Test" src="views/images/remove.png"/></a>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    <?php } ?>
                </table>
            </div>


        </div>
    </div>
</main>           
