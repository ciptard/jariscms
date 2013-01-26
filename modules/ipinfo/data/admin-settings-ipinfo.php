<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the administration page for whizzywig.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("IP Info Settings") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("edit_settings"));
            
            $settings = JarisCMS\Setting\GetAll("ipinfo");

            if(isset($_REQUEST["btnSave"]))
            {
                if(JarisCMS\Setting\Save("enable_api", $_REQUEST["enable_api"], "ipinfo"))
                {
                    JarisCMS\Setting\Save("use_external_api", $_REQUEST["use_external_api"], "ipinfo");
                    JarisCMS\Setting\Save("city_group_url", $_REQUEST["city_group_url"], "ipinfo");
                    JarisCMS\Setting\Save("country_group_url", $_REQUEST["country_group_url"], "ipinfo");
                    
                    JarisCMS\System\AddMessage(t("Your changes have been saved."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"));
                }

                JarisCMS\System\GoToPage("admin/settings");
            }

            $parameters["name"] = "ipinfo-settings";
            $parameters["class"] = "ipinfo-settings";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/ipinfo", "ipinfo"));
            $parameters["method"] = "post";

            $enable_api[t("Enable")] = true;
            $enable_api[t("Disable")] = false;

            $enable_api_fields[] = array("type"=>"radio", "name"=>"enable_api", "id"=>"enable_api", "value"=>$enable_api, "checked"=>$settings["enable_api"]);

            $fieldset[] = array("name"=>t("IP info API"), "description"=>t("To control wheter the module offers api access for retreiving IP information. For this to work you need to have the ip_group_city and ip_group_country databases on the module db directory."), "fields"=>$enable_api_fields, "collapsible"=>true, "collapsed"=>false);
            
            
            
            $text_fields[] = array("type"=>"checkbox", "checked"=>$settings["use_external_api"], "label"=>t("Use external api?"), "name"=>"use_external_api", "id"=>"use_external_api", "description"=>t("If you dont enable this you will need to have the ip_group_city and ip_group_country databases on the module db directory."));
            $text_fields[] = array("type"=>"text", "name"=>"city_group_url", "label"=>t("City group url:"), "id"=>"city_group_url", "value"=>$settings["city_group_url"]?$settings["city_group_url"]:"http://ipinfodb.com/ip_query.php", "description"=>t("The url of the external city group api to use."));
            $text_fields[] = array("type"=>"text", "name"=>"country_group_url", "label"=>t("Country group url:"), "id"=>"country_group_url", "value"=>$settings["country_group_url"]?$settings["country_group_url"]:"http://ipinfodb.com/ip_query_country.php", "description"=>t("The url of the external country group api to use."));

            $fieldset[] = array("name"=>t("IP information retrieval"), "fields"=>$text_fields, "collapsible"=>true, "collapsed"=>false, "description"=>t("The way in wich the api internal functions will retreive IP information."));
            
            
            
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
