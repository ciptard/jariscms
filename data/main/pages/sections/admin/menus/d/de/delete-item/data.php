<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the menu delete item page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Menu Item") ?>
    field;
    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_menus", "delete_menu_items"));

            $menu_data = JarisCMS\Menu\GetItemData($_REQUEST["id"], $_REQUEST["menu"]);

            if(isset($_REQUEST["btnYes"]))
            {
                if(JarisCMS\Menu\DeleteItem($_REQUEST["id"], $_REQUEST["menu"]))
                {
                    JarisCMS\System\AddMessage(t("Menu item successfully deleted."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/menus");
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage("admin/menus");
            }
        ?>

        <form class="menus-delete" method="post" action="<?php JarisCMS\URI\PrintURL("admin/menus/delete") ?>">
            <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
            <input type="hidden" name="menu" value="<?php print $_REQUEST["menu"] ?>" />
            <div><?php print t("Are you sure you want to delete the menu item?") ?>
            <div><b><?php print t("Title:") ?> <?php print t($menu_data["title"]) ?></b></div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    field;
    
    field: is_system
        1
    field;
row;
