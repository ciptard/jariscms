<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the delete sqlite database script.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Sqlite Database") ?>
    field;

    field: content
        <?php

            JarisCMS\Security\ProtectPage(array("edit_settings"));
            
            if(!isset($_REQUEST["name"]))
            {
                JarisCMS\System\GoToPage("admin/settings/sqlite");
            }            

            if(isset($_REQUEST["btnYes"]))
            {
                if(JarisCMS\SQLite\DBExists($_REQUEST["name"]))
                {
                    unlink(JarisCMS\Setting\GetDataDirectory() . "sqlite/" . $_REQUEST["name"]);
                    unlink(JarisCMS\Setting\GetDataDirectory() . "sqlite/" . $_REQUEST["name"] . ".sql");
                }
                
                JarisCMS\System\AddMessage(t("Database successfully deleted."));
                
                JarisCMS\System\GoToPage("admin/settings/sqlite");
            }
            elseif(isset($_REQUEST["btnNo"]))
            {
                JarisCMS\System\GoToPage("admin/settings/sqlite");
            }

        ?>
        
        <form class="clear-image_cache" method="post" action="<?php JarisCMS\URI\PrintURL("admin/settings/sqlite/delete") ?>">
            <input type="hidden" name="name" value="<?php print $_REQUEST["name"] ?>" />
            <div><?php print t("Are you sure you want to delete the database?") ?></div>
            <div><b><?php print t("Database:") ?></b> <?php print $_REQUEST["name"] ?></div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    field;

    field: is_system
        1
    field;
row;
