<?php
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
        <?php print t("Modules") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_modules"));
        ?>

        <?php

            print "<table class=\"modules-list\">\n";

            print "<thead><tr>\n";

            print "<td>" . t("Name") . "</td>\n";
            print "<td>" . t("Status") . "</td>\n";
            print "<td>" . t("Operation") . "</td>\n";
            print "<td>" . t("Dependencies") . "</td>\n";

            print  "</tr></thead>\n";

            $modules = JarisCMS\Module\GetAll();

            foreach($modules as $module_path=>$module_info)
            {
                $title = t("View module info.");
                $more_url = JarisCMS\URI\PrintURL("admin/modules/view", array("path"=>$module_path));
                $installed_version = JarisCMS\Module\GetVersion($module_path);

                print "<tr>\n";

                print "<td><a title=\"$title\" href=\"$more_url\">{$module_info['name']}</a></td>\n";

                print "<td>";
                if(JarisCMS\Module\IsInstalled($module_path))
                {
                    print t("Enabled");
                    print "<br />" . t("Version installed:") . " " . $installed_version;

                    if($installed_version < $module_info["version"])
                    {
                        print "<br />" . t("Actual version:") . " " . $module_info["version"];
                    }
                }
                else
                {
                    print t("Disabled");
                    print "<br />" . t("Version:") . " " . $module_info["version"];
                }
                print "</td>\n";

                print "<td>";
                if(!JarisCMS\Module\IsInstalled($module_path))
                {
                    print "<a href=\"" . JarisCMS\URI\PrintURL("admin/modules/install", array("path"=>$module_path)) . "\">" . t("Install") . "</a>";
                }
                else
                {
                    print "<a href=\"" . JarisCMS\URI\PrintURL("admin/modules/uninstall", array("path"=>$module_path)) . "\">" . t("Uninstall") . "</a>";

                    if($installed_version < $module_info["version"])
                    {
                        print "&nbsp;<a href=\"" . JarisCMS\URI\PrintURL("admin/modules/upgrade", array("path"=>$module_path)) . "\">" . t("Upgrade") . "</a>";
                    }
                }
                print "</td>\n";
                
                print "<td>";
                if(isset($module_info["dependencies"]))
                {
                    $dependencies = "";
                    foreach($module_info["dependencies"] as $dependency_name)
                    {
                        $dependency_data = JarisCMS\Module\GetInfo($dependency_name);
                        
                        if($dependency_data)
                        {
                            $dependencies .= $dependency_data["name"] . ", ";
                        }
                        else
                        {
                            $dependencies .= $dependency_name . ", ";
                        }
                        
                        unset($dependency_data);
                    }
                    
                    print trim($dependencies, ", ");
                }
                print "</td>\n";

                print "</tr>\n";

            }

            print "</table>"
        ?>
    field;
    
    field: is_system
        1
    field;
row;
