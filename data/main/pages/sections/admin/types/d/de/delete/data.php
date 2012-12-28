<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the type delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Type") ?>
    field;
    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_types", "delete_types"));

            $type_data = JarisCMS\Type\GetData($_REQUEST["type"]);

            if(isset($_REQUEST["btnYes"]))
            {
                $message = JarisCMS\Type\Delete($_REQUEST["type"]);

                if($message == "true")
                {
                    JarisCMS\System\AddMessage(t("Type successfully deleted."));
                }
                else
                {
                    JarisCMS\System\AddMessage($message, "error");
                }

                JarisCMS\System\GoToPage("admin/types");
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage("admin/types");
            }
        ?>

        <form class="type-delete" method="post" action="<?php JarisCMS\URI\PrintURL("admin/types/delete") ?>">
            <input type="hidden" name="type" value="<?php print $_REQUEST["type"] ?>" />
            <br />
            <div><?php print t("Are you sure you want to delete the type?") ?>
            <div><b><?php print t("Type:") ?> <?php print t($type_data["name"]) ?></b></div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    field;

    field: is_system
        1
    field;
row;
