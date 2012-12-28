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
        <?php print t("Delete Content Type Field") ?>
    field;
    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_types_fields", "delete_types_fields"));

            $field_data = JarisCMS\Field\GetTypeData($_REQUEST["id"], $_REQUEST["type_name"]);

            if(isset($_REQUEST["btnYes"]))
            {
                if(JarisCMS\Field\DeleteType($_REQUEST["id"], $_REQUEST["type_name"]))
                {
                    JarisCMS\System\AddMessage(t("Type field successfully deleted."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/types/fields", array("type"=>$_REQUEST["type_name"]));
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage("admin/types/fields", array("type"=>$_REQUEST["type_name"]));
            }
        ?>

        <form class="type-field-delete" method="post" action="<?php JarisCMS\URI\PrintURL("admin/types/fields/delete") ?>">
            <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
            <input type="hidden" name="type_name" value="<?php print $_REQUEST["type_name"] ?>" />
            <br />
            <div><?php print t("Are you sure you want to delete the field?") ?>
            <div><b><?php print t("Field:") ?> <?php print t($field_data["name"]) ?></b></div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    field;

    field: is_system
        1
    field;
row;
