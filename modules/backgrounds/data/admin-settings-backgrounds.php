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
        <?php print t("Backgrounds"); ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("edit_settings"));

            JarisCMS\System\AddTab(t("Add Background"), JarisCMS\Module\GetPageURI("admin/settings/backgrounds/add", "backgrounds"));
            JarisCMS\System\AddTab(t("Add Multi-background"), JarisCMS\Module\GetPageURI("admin/settings/backgrounds/multi/add", "backgrounds"));

            $backgrounds_settings = JarisCMS\Setting\GetAll("backgrounds");
            
            $backgrounds = unserialize($backgrounds_settings["backgrounds"]);
            
            if(is_array($backgrounds) && count($backgrounds) > 0)
            {
                print "<table class=\"navigation-list\">";
                print "<thead>";
                print "<tr>";
                print "<td>" . t("Description") . "</td>";
                print "<td>" . t("Actions") . "</td>";
                print "</tr>";
                print "</thead>";
                
                foreach($backgrounds as $background_id=>$background)
                {
                    print "<tr>";
                    
                    print "<td>{$background["description"]}</td>";
                    
                    print "<td>";
                    
                    if($background["multi"])
                        print "<a href=\"".JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/backgrounds/multi/edit", "backgrounds"), array("id"=>$background_id))."\">".t("Edit")."</a>&nbsp;";
                    else
                        print "<a href=\"".JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/backgrounds/edit", "backgrounds"), array("id"=>$background_id))."\">".t("Edit")."</a>&nbsp;";
                    
                    print "<a href=\"".JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/backgrounds/delete", "backgrounds"), array("id"=>$background_id))."\">".t("Delete")."</a>";
                    
                    print "</td>";
                    
                    print "</tr>";
                }
                
                print "</table>";
            }
            else
            {
                JarisCMS\System\AddMessage(t("No background images available. Click on one of the options add one."));
            }

        ?>
    field;

    field: is_system
        1
    field;
row;
