<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the languages management section.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Translate Page") ?>
    field;

    field: content

        <?php
            JarisCMS\Security\ProtectPage(array("translate_languages"));
            
            if(!JarisCMS\Page\IsOwner($_REQUEST["uri"]))
            {
                JarisCMS\Security\ProtectPage();
            }

            $arguments["uri"] = $_REQUEST["uri"];

            //Tabs
            if(JarisCMS\Group\GetPermission("edit_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Edit"), "admin/pages/edit", $arguments);
            }
            JarisCMS\System\AddTab(t("View"), $_REQUEST["uri"]);
            if(JarisCMS\Group\GetPermission("view_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Blocks"), "admin/pages/blocks", $arguments);
            }
            if(JarisCMS\Group\GetPermission("view_images", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Images"), "admin/pages/images", $arguments);
            }
            if(JarisCMS\Group\GetPermission("view_files", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Files"), "admin/pages/files", $arguments);
            }
            if(JarisCMS\Group\GetPermission("translate_languages", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Translate"), "admin/pages/translate", $arguments);
            }
            if(JarisCMS\Group\GetPermission("delete_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Delete"), "admin/pages/delete", $arguments);
            }
        ?>

        <?php

            $languages = JarisCMS\Language\GetAll();

            print "<table class=\"languages-list\">\n";

            print "<thead><tr>\n";

            print "<td>" . t("Code") . "</td>\n";
            print "<td>" . t("Name") . "</td>\n";
            print "<td>" . t("Operation") . "</td>\n";

            print  "</tr></thead>\n";

            foreach($languages as $code=>$name)
            {
                if($code != "en"){
                    print "<tr>\n";

                    print "<td>" . $code . "</td>\n";
                    print "<td>" . $name . "</td>\n";

                    $edit_url = JarisCMS\URI\PrintURL("admin/languages/translate",array("code"=>$code, "type"=>"page", "uri"=>$_REQUEST["uri"]));
                    $edit_text = t("Translate");

                    print "<td>
                            <a href=\"$edit_url\">$edit_text</a>&nbsp;
                           </td>\n";

                    print "</tr>\n";
                }
            }

            print "</table>\n";
        ?>
    field;

    field: is_system
        1
    field;
row;
