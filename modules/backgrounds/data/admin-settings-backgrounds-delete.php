<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Background") ?>
    field;
    
    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("edit_settings"));

            $backgrounds_settings = JarisCMS\Setting\GetAll("backgrounds");
            $backgrounds = unserialize($backgrounds_settings["backgrounds"]);
            
            $background = $backgrounds[intval($_REQUEST["id"])];

            if(isset($_REQUEST["btnYes"]))
            {    
                unset($backgrounds[intval($_REQUEST["id"])]);
                
                if(JarisCMS\Setting\Save("backgrounds", serialize($backgrounds), "backgrounds"))
                {
                    if($background["multi"])
                    {
                        $images = unserialize($background["images"]);
                        foreach($images as $image)
                        {
                            unlink("files/backgrounds/" . $image);
                        }
                    }
                    else
                    {
                        unlink("files/backgrounds/" . $background["image"]);
                    }
                    
                    JarisCMS\System\AddMessage(t("Background successfully deleted."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/settings/backgrounds", "backgrounds"));
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/settings/backgrounds", "backgrounds"));
            }
        ?>

        <?php if($background["multi"]) { ?>
        <form class="background-delete" method="post" action="<?php JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/backgrounds/delete", "backgrounds")) ?>">
            <input type="hidden" name="id" value="<?php print intval($_REQUEST["id"]) ?>" />
            <div><?php print t("Are you sure you want to delete the multi-image background?") ?>
            <div>
                <?php 
                    $images = unserialize($background["images"]);
                    foreach($images as $image)
                    {
                ?>
                <a style="display: block; margin-bottom: 7px" href="<?php print JarisCMS\URI\PrintURL("files/backgrounds/{$image}"); ?>">
                    <img width="300px" src="<?php print JarisCMS\URI\PrintURL("files/backgrounds/{$image}"); ?>" />
                </a>
                <?php } ?>
            </div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
        <?php } else { ?>
        <form class="background-delete" method="post" action="<?php JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/backgrounds/delete", "backgrounds")) ?>">
            <input type="hidden" name="id" value="<?php print intval($_REQUEST["id"]) ?>" />
            <div><?php print t("Are you sure you want to delete the background image?") ?>
            <div>
                <a href="<?php print JarisCMS\URI\PrintURL("files/backgrounds/{$background['image']}"); ?>">
                    <img width="300px" src="<?php print JarisCMS\URI\PrintURL("files/backgrounds/{$background['image']}"); ?>" />
                </a>
            </div>
            </div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
        <?php } ?>
    field;

    field: is_system
        1
    field;
row;
