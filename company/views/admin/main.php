<script type="text/javascript" src="views/js/jquery-1.12.3.js"></script> 
<script type="text/javascript" src="views/js/jquery.tablesorter.js"></script> 
<script>
    //Makes ACCOUNT TABLE sortable.
    $(document).ready(function () {
        $("#account_table").tablesorter();
    });
    //Confirms ACCOUNT removal.
    $(document).ready(function () {
        $("a.delete").click(function (e) {
            if (!confirm('Are you sure you want to delete this account?')) {
                e.preventDefault();
                return false;
            }
            return true;
        });
    });

</script>
<main>
    <div>
        <?php require_once ('views/modules/sub_header.php'); ?>

        <div id="main_info">
            <div id="new_account_button_container">
                <button class='button create_button' id='new_account_button' type='button' 
                        onclick='<?php echo "window.location.href = \"" . $create_account_url . "\"" ?>'>+ New Account</button>
            </div>
            <div id="table_title_container">
                <h1>Teacher Accounts</h1>
            </div>
            <div>
                <table id="account_table" class="tablesorter">
                    <colgroup span="4"></colgroup>
                    <thead> 
                        <tr>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Is Admin?</th>                             
                            <th>Actions</th>
                        </tr>
                    </thead> 
                    <tbody>
                       <?php foreach ($lv_teacher_list as $teacher) { ?>
                            <tr>
                                <td class="username_column"><?php echo $teacher->username ?></td>
                                <td class="name_column"><?php echo $teacher->firstName . " " . $teacher->lastName ; ?></td>
                                <td class="isAdmin_column"><?php echo ($teacher->isAdmin)? "Yes":"No" ?></td>
                                <td class="actions_column">
                                    <a href='<?php echo $view_teacher_url . '&id=' . $teacher->id ?>'><img alt="View" class="view_essays_img" title="View Teacher" src="views/images/doc.png"/></a>
                                    <a href='<?php echo $edit_teacher_url . '&id=' . $teacher->id ?>'><img alt="Edit" title="Edit Teacher" src="views/images/edit.png"/></a>
                                    <a href='<?php echo $remove_teacher_url . '&id=' . $teacher->id ?>' class="delete"><img alt="Remove" title="Remove Teacher" src="views/images/remove.png"/></a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>


        </div>   
    </div>
</main>           
