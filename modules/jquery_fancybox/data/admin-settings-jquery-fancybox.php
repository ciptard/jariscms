<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the administration page for fancybox.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        Fancybox Settings
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("edit_settings"));

            if(isset($_REQUEST["btnSave"]))
            {
                if(JarisCMS\Setting\Save("display_rule", $_REQUEST["display_rule"], "jquery-fancybox"))
                {
                    JarisCMS\Setting\Save("pages", $_REQUEST["pages"], "jquery-fancybox");
                    JarisCMS\System\AddMessage(t("Your changes have been saved."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"));
                }

                JarisCMS\System\GoToPage("admin/settings");
            }

            $fancybox_settings = JarisCMS\Setting\GetAll("jquery-fancybox");

            $parameters["name"] = "jquery-fancybox-settings";
            $parameters["class"] = "jquery-fancybox-settings";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/jquery/fancybox", "jquery_fancybox"));
            $parameters["method"] = "post";

            $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
            $display_rules[t("Just display on the listed pages.")] = "just_listed";
            
            $fields_pages[] = array("type"=>"radio", "checked"=>$fancybox_settings["display_rule"], "name"=>"display_rule", "id"=>"display_rule", "value"=>$display_rules);
            $fields_pages[] = array("type"=>"uriarea", "name"=>"pages", "label"=>t("Pages:"), "id"=>"pages", "value"=>$fancybox_settings["pages"]);
            
            $fieldset[] = array("fields"=>$fields_pages, "name"=>"Pages to display", "description"=>t("List of uri's seperated by comma (,). Also supports the wildcard (*), for example: my-section/*"));

            $fields[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
            $fields[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

            $fieldset[] = array("fields"=>$fields);

            print JarisCMS\Form\Generate($parameters, $fieldset);

        ?>
    field;

    field: is_system
        1
    field;
row;
