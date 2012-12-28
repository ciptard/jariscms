<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the clear image cache script.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Clear Page Cache") ?>
    field;

    field: content
        <?php

            JarisCMS\Security\ProtectPage(array("edit_settings"));

            if(isset($_REQUEST["btnYes"]))
            {
                if(JarisCMS\FileSystem\RemoveDirRecursively(JarisCMS\Setting\GetDataDirectory() . "cache", true))
                {
                    JarisCMS\System\AddMessage(t("Page cache cleared successfully."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }
                
                JarisCMS\System\GoToPage("admin/settings");
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage("admin/settings");
            }

        ?>
        
        <form class="clear-page_cache" method="post" action="<?php JarisCMS\URI\PrintURL("admin/settings/clear-page-cache") ?>">
            <div><?php print t("Are you sure you want to clear the page cache?") ?></div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    field;

    field: is_system
        1
    field;
row;
