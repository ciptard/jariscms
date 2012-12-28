<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the categories delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Category") ?>
    field;
    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_categories", "delete_categories"));

            $category_data = JarisCMS\Category\GetData($_REQUEST["category"]);

            if(isset($_REQUEST["btnYes"]))
            {
                if(JarisCMS\Category\Delete($_REQUEST["category"]))
                {
                    JarisCMS\System\AddMessage(t("Category successfully deleted."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/categories");
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage("admin/categories");
            }
        ?>

        <form class="categorye-delete" method="post" action="<?php JarisCMS\URI\PrintURL("admin/categories/delete") ?>">
            <input type="hidden" name="category" value="<?php print $_REQUEST["category"] ?>" />
            <br />
            <div><?php print t("Are you sure you want to delete the category?") ?>
            <div><b><?php print t("Category:") ?> <?php print t($category_data["name"]) ?></b></div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    field;

    field: is_system
        1
    field;
row;
