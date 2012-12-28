<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the group delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Group") ?>
    field;
    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_groups", "delete_groups"));

            $group_data = JarisCMS\Group\GetData($_REQUEST["group"]);

            if(isset($_REQUEST["btnYes"]))
            {
                $message = JarisCMS\Group\Delete($_REQUEST["group"]);

                if($message == "true")
                {
                    JarisCMS\System\AddMessage(t("Group successfully deleted."));
                }
                else
                {
                    //An error ocurred so display the error message
                    JarisCMS\System\AddMessage($message, "error");
                }

                JarisCMS\System\GoToPage("admin/groups");
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage("admin/groups");
            }
        ?>

        <form class="group-delete" method="post" action="<?php JarisCMS\URI\PrintURL("admin/groups/delete") ?>">
            <input type="hidden" name="group" value="<?php print $_REQUEST["group"] ?>" />
            <br />
            <div><?php print t("Are you sure you want to delete the group?") ?>
            <div><b><?php print t("Group:") ?> <?php print t($group_data["name"]) ?></b></div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    field;
    
    field: is_system
        1
    field;
row;
