<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the delete subcategory page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Subcategory") ?>
    field;
    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_subcategories", "delete_subcategories"));

            $subcategory_data = JarisCMS\Category\GetChildData($_REQUEST["category"], $_REQUEST["id"]);

            if(isset($_REQUEST["btnYes"]))
            {
                if(JarisCMS\Category\DeleteChild($_REQUEST["category"], $_REQUEST["id"]))
                {
                    JarisCMS\System\AddMessage(t("Subcategory successfully deleted."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/categories/subcategories", array("category"=>$_REQUEST["category"]));
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage("admin/categories/subcategories", array("category"=>$_REQUEST["category"]));
            }
        ?>

        <form class="subcategories-delete" method="post" action="<?php JarisCMS\URI\PrintURL("admin/categories/subcategories/delete") ?>">
            <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
            <input type="hidden" name="category" value="<?php print $_REQUEST["category"] ?>" />
            <div><?php print t("Are you sure you want to delete the subcategory?") ?>
            <div><b><?php print t("Title:") ?> <?php print t($subcategory_data["title"]) ?></b></div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    field;
    
    field: is_system
        1
    field;
row;
