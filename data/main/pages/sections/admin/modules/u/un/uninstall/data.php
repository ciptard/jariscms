﻿<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the modules page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Uninstall Module") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_modules", "uninstall_modules"));

            if(isset($_REQUEST["path"]))
            {
                $is_dependency = false;
                if(JarisCMS\Module\Uninstall($_REQUEST["path"], $is_dependency))
                {
                    JarisCMS\System\AddMessage(t("Module successfully uninstalled."));
                }
                else if(!$is_dependency)
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }
            }

            JarisCMS\System\GoToPage("admin/modules");
        ?>
    field;

    field: is_system
        1
    field;
row;
