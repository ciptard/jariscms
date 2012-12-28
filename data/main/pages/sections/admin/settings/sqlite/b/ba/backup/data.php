<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the sqlite backup page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Sqlite Backup") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("edit_settings"));
        ?>
        
        <?php
            if(isset($_REQUEST["name"]))
            {
                if(JarisCMS\SQLite\DBExists($_REQUEST["name"]))
                {
                    JarisCMS\SQLite\Backup($_REQUEST["name"]);
                    
                    if(file_exists(JarisCMS\Setting\GetDataDirectory() . "sqlite/" . $_REQUEST["name"] . ".sql"))
                    {
                        JarisCMS\System\AddMessage(t("Backup successfully updated."));
                    }
                    else
                    {
                        JarisCMS\System\AddMessage(t("Backup successfully created."));
                    }
                }
            }
            
            JarisCMS\System\GoToPage("admin/settings/sqlite");
        ?>
    field;

    field: is_system
        1
    field;
row;
