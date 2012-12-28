<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the user delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete User") ?>
    field;
    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_users", "delete_users"));

            $arguments["username"] = $_REQUEST["username"];

            JarisCMS\System\AddTab(t("Edit"), "admin/users/edit", $arguments);

            $user_data = JarisCMS\User\GetData($_REQUEST["username"]);

            if(isset($_REQUEST["btnYes"]))
            {
                if(JarisCMS\User\Delete($_REQUEST["username"]))
                {
                    JarisCMS\System\AddMessage(t("User successfully deleted."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/users");
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage("admin/users/edit", $arguments);
            }
        ?>

        <form class="user-delete" method="post" action="<?php JarisCMS\URI\PrintURL("admin/users/delete") ?>">
            <input type="hidden" name="username" value="<?php print $_REQUEST["username"] ?>" />
            <br />
            <div><?php print t("This action will also delete all users content.") . " " . t("Are you sure you want to delete the user?") ?>
            <div><b><?php print t("Username:") ?> <?php print $_REQUEST["username"] ?></b></div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    field;

    field: is_system
        1
    field;
row;
