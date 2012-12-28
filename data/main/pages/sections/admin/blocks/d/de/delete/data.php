<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the global delete block page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Block") ?>
    field;
    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_blocks", "delete_blocks"));

            $block_data = JarisCMS\Block\GetData($_REQUEST["id"], $_REQUEST["position"]);
            
            if($block_data["is_system"])
            {
                JarisCMS\System\AddMessage(t("Can't delete system generated block."), "error");
                JarisCMS\System\GoToPage("admin/blocks");
            }

            if(isset($_REQUEST["btnYes"]))
            {
                if(JarisCMS\Block\Delete($_REQUEST["id"], $_REQUEST["position"]))
                {
                    JarisCMS\System\AddMessage(t("Block successfully deleted."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/blocks");
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage("admin/blocks");
            }
        ?>

        <form class="blocks-delete" method="post" action="<?php JarisCMS\URI\PrintURL("admin/blocks/delete") ?>">
            <input type="hidden" name="id" value="<?php print $_REQUEST["id"] ?>" />
            <input type="hidden" name="position" value="<?php print $_REQUEST["position"] ?>" />
            <div><?php print t("Are you sure you want to delete the block?") ?>
            <div><b><?php print t("Description: ") ?><?php print $block_data["description"] ?></b></div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    field;
    
    field: is_system
        1
    field;
row;
