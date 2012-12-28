<?php
/**
 *Copyright 2008, Jefferson Gonzï¿½lez (JegoYalu.com)
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
        <?php print t("Reindex SQLite Search Engine Database") ?>
    field;

    field: content
        <?php

            JarisCMS\Security\ProtectPage(array("edit_settings"));

            if(isset($_REQUEST["btnYes"]))
            {
                ini_set('max_execution_time', '0');
                
                if(JarisCMS\Search\ReindexSQLite())
                {
                    JarisCMS\System\AddMessage(t("Indexation of SQLite search database completed."));
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
        
        <form class="reindex-search-engine" method="post" action="<?php JarisCMS\URI\PrintURL("admin/settings/reindex-search") ?>">
            <div><?php print t("The proces of recreating sqlite search engine index could take some time. Are you sure you want do this?") ?></div>
            <input class="form-submit" type="submit" name="btnYes" value="<?php print t("Yes") ?>" />
            <input class="form-submit" type="submit" name="btnNo" value="<?php print t("No") ?>" />
        </form>
    field;

    field: is_system
        1
    field;
row;
