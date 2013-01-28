<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the themes management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0

    field: title
        <?php print t("Themes") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("edit_settings"));

            $is_override_on = JarisCMS\Setting\Get("override", "main");

            if(!$is_override_on)
            {
                JarisCMS\System\AddMessage(t("In order to change the default theme you need to enable Override settings"), "error");
            }

            if(isset($_REQUEST["btnSave"]))
            {
                if(is_array($_REQUEST["themes_enabled"]))
                {
                    if(!in_array($_REQUEST["theme"], $_REQUEST["themes_enabled"]))
                        $_REQUEST["themes_enabled"][] = $_REQUEST["theme"];
                }
                
                JarisCMS\Setting\Save("theme", $_REQUEST["theme"], "main");
                
                JarisCMS\Setting\Save("themes_enabled", serialize($_REQUEST["themes_enabled"]), "main");

                JarisCMS\System\AddMessage(t("Changes successfully saved."));

                JarisCMS\System\GoToPage("admin/themes");
            }
        ?>

        <form class="themes" action="<?php print JarisCMS\URI\PrintURL("admin/themes"); ?>" method="post">

        <?php

            print "<table class=\"themes-list\">\n";

            print "<thead><tr>\n";

            print "<td>" . t("Preview") . "</td>\n";
            print "<td>" . t("Name") . "</td>\n";
            print "<td>" . t("Enabled") . "</td>\n";
            print "<td>" . t("Default") . "</td>\n";

            print  "</tr></thead>\n";

            $themes = JarisCMS\Theme\GetAll();
            
            $default_theme = JarisCMS\Setting\Get("theme", "main");
            $themes_enabled = unserialize(JarisCMS\Setting\Get("themes_enabled", "main"));

            foreach($themes as $theme_path=>$theme_info)
            {
                //Used to print the theme preview
                global $base_url;

                $alt = t("Preview not available");
                $title = t("View theme info.");
                $more_url = JarisCMS\URI\PrintURL("admin/themes/view", array("path"=>$theme_path));
                $thumbnail = $base_url . "/themes/$theme_path/preview.png";
                $selected = $default_theme == $theme_path?"checked=\"checked\"":"";
                $checked = "";
                
                if(is_array($themes_enabled))
                {
                    if(in_array($theme_path, $themes_enabled))
                        $checked = "checked=\"checked\"";
                }

                print "<tr>\n";
                
                if($theme_info != null)
                {
                    print "<td><a title=\"$title\" href=\"$more_url\"><img alt=\"$alt\" src=\"$thumbnail\" /></a></td>\n";
                    print "<td>" . t($theme_info['name']) . "</td>\n";
                }
                else
                {
                    print "<td><img alt=\"$alt\" src=\"$thumbnail\" /></td>\n";
                    print "<td>$theme_path</td>\n";
                }
                
                print "<td><input $checked type=\"checkbox\" name=\"themes_enabled[]\" value=\"$theme_path\" /></td>\n";
                print "<td><input $selected type=\"radio\" name=\"theme\" value=\"$theme_path\" /></td>\n";
                
                print "</tr>\n";

            }

            print "</table>"
        ?>

        <div>
        <br />
        <input class="form-submit" type="submit" name="btnSave" value="<?php print t("Save") ?>" />
        &nbsp;
        <input class="form-submit" type="submit" name="btnCancel" value="<?php print t("Cancel") ?>" />
        </div>
        </form>
    field;

    field: is_system
        1
    field;
row;
