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
        <?php print t("Languages") ?>
    field;

    field: content

        <?php
            JarisCMS\Security\ProtectPage(array("view_languages"));

            JarisCMS\System\AddTab(t("Add Language"), "admin/languages/add");
        ?>

        <?php

            $languages = JarisCMS\Language\GetAll();

            print "<table class=\"languages-list\">\n";

            print "<thead><tr>\n";

            print "<td>" . t("Code") . "</td>\n";
            print "<td>" . t("Name") . "</td>\n";
            print "<td>" . t("Operation") . "</td>\n";

            print  "</tr></thead>\n";

            $title = t("View language info.");
            
            foreach($languages as $code=>$name)
            {
                if($code != "en"){
                    print "<tr>\n";

                    print "<td><a title=\"$title\" href=\"".JarisCMS\URI\PrintURL("admin/languages/info", array("code"=>$code))."\">" . $code . "</a></td>\n";
                    print "<td>" . $name . "</td>\n";

                    $edit_url = JarisCMS\URI\PrintURL("admin/languages/edit",array("code"=>$code));
                    $edit_text = t("Edit strings");

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
