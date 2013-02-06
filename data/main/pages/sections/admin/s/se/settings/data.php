<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the site settings management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Site Settings") ?>
    field;

    field: content
        <?php

            JarisCMS\Security\ProtectPage(array("edit_settings"));

            JarisCMS\System\AddTab(t("Themes"), "admin/themes");
            JarisCMS\System\AddTab(t("Mailer"), "admin/settings/mailer");
            JarisCMS\System\AddTab(t("Cron Jobs"), "admin/settings/cron");
            JarisCMS\System\AddTab(t("Clear Image Cache"), "admin/settings/clear-image-cache");
            JarisCMS\System\AddTab(t("Clear Page Cache"), "admin/settings/clear-page-cache");
            JarisCMS\System\AddTab(t("Sqlite Backups"), "admin/settings/sqlite", null, 1);
            JarisCMS\System\AddTab(t("Re-index SQLite Search"), "admin/settings/reindex-search", null, 1);

            //Get exsiting settings or defualt ones if main settings table doesn't exist
            $site_settings = null;
            if(!($site_settings = JarisCMS\Setting\GetAll("main")))
            {
                global $title, $base_url, $slogan, $footer_message, $language, $clean_urls, $user_profiles;

                $site_settings["override"] = false;
                $site_settings["site_status"] = true;
                $site_settings["site_status_title"] = "Under mantainance";
                $site_settings["site_status_description"] = "The site is down for mantainance, sorry for any inconvenience it may cause you. Try again later.";
                $site_settings["override"] = false;
                $site_settings["title"] = $title;
                $site_settings["description"] = "";
                $site_settings["keywords"] = "";
                $site_settings["base_url"] = $base_url;
                $site_settings["slogan"] = $slogan;
                $site_settings["footer_message"] = $footer_message;
                $site_settings["language"] = $language;
                $site_settings["clean_urls"] = $clean_urls;
                $site_settings["new_registrations"] = false;
                $site_settings["registration_needs_approval"] = false;
                $site_settings["registration_can_select_group"] = false;
                $site_settings["registration_groups"] = "";
                $site_settings["registration_groups_approval"] = "";
                $site_settings["validate_ip"] = false;
                $site_settings["login_ssl"] = false;
                $site_settings["enable_cache"] = false;
                $site_settings["cache_php_pages"] = false;
                $site_settings["cache_ignore_db"] = "";
                $site_settings["cache_ignore_types"] = "";
                $site_settings["data_cache"] = false;
                $site_settings["user_profiles"] = $user_profiles;
                $site_settings["user_profiles_public"] = false;
                $site_settings["user_picture"] = false;
                $site_settings["user_picture_size"] = "150x150";
                $site_settings["image_compression"] = false;
                $site_settings["image_compression_maxwidth"] = "640";
                $site_settings["image_compression_quality"] = "75";
                $site_settings["home_page"] = "home";
                $site_settings["page_not_found"] = "";
            }
            $site_settings["cache_ignore_db"] = unserialize($site_settings["cache_ignore_db"]);
            $site_settings["cache_ignore_types"] = unserialize($site_settings["cache_ignore_types"]);
            $site_settings["registration_groups"] = unserialize($site_settings["registration_groups"]);
            $site_settings["registration_groups_approval"] = unserialize($site_settings["registration_groups_approval"]);

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("edit-site-settings"))
            {    
                //Check if write is possible and continue to write settings
                if(JarisCMS\Setting\Save("override", $_REQUEST["override"], "main"))
                {
                    JarisCMS\Setting\Save("site_status", $_REQUEST["site_status"], "main");
                    JarisCMS\Setting\Save("site_status_title", $_REQUEST["site_status_title"], "main");
                    JarisCMS\Setting\Save("site_status_description", $_REQUEST["site_status_description"], "main");
                    JarisCMS\Setting\Save("title", $_REQUEST["title"], "main");
                    JarisCMS\Setting\Save("description", $_REQUEST["description"], "main");
                    JarisCMS\Setting\Save("keywords", $_REQUEST["keywords"], "main");
                    JarisCMS\Setting\Save("auto_detect_base_url", $_REQUEST["auto_detect_base_url"], "main");
                    JarisCMS\Setting\Save("base_url", $_REQUEST["base_url"], "main");
                    JarisCMS\Setting\Save("slogan", $_REQUEST["slogan"], "main");
                    JarisCMS\Setting\Save("footer_message", $_REQUEST["footer_message"], "main");
                    JarisCMS\Setting\Save("timezone", $_REQUEST["timezone"], "main");
                    JarisCMS\Setting\Save("language", $_REQUEST["language"], "main");
                    JarisCMS\Setting\Save("clean_urls", $_REQUEST["clean_urls"], "main");
                    JarisCMS\Setting\Save("new_registrations", $_REQUEST["new_registrations"], "main");
                    JarisCMS\Setting\Save("registration_needs_approval", $_REQUEST["registration_needs_approval"], "main");
                    JarisCMS\Setting\Save("registration_can_select_group", $_REQUEST["registration_can_select_group"], "main");
                    JarisCMS\Setting\Save("registration_groups", serialize($_REQUEST["registration_groups"]), "main");
                    JarisCMS\Setting\Save("registration_groups_approval", serialize($_REQUEST["registration_groups_approval"]), "main");
                    JarisCMS\Setting\Save("registration_benefits", $_REQUEST["registration_benefits"], "main");
                    JarisCMS\Setting\Save("registration_terms", $_REQUEST["registration_terms"], "main");
                    JarisCMS\Setting\Save("validate_ip", $_REQUEST["validate_ip"], "main");
                    JarisCMS\Setting\Save("login_ssl", $_REQUEST["login_ssl"], "main");
                    JarisCMS\Setting\Save("enable_cache", $_REQUEST["enable_cache"], "main");
                    JarisCMS\Setting\Save("cache_php_pages", $_REQUEST["cache_php_pages"], "main");
                    JarisCMS\Setting\Save("cache_ignore_db", serialize($_REQUEST["cache_ignore_db"]), "main");
                    JarisCMS\Setting\Save("cache_ignore_types", serialize($_REQUEST["cache_ignore_types"]), "main");
                    JarisCMS\Setting\Save("data_cache", $_REQUEST["data_cache"], "main");
                    JarisCMS\Setting\Save("view_script_stats", $_REQUEST["view_script_stats"], "main");
                    JarisCMS\Setting\Save("user_profiles", $_REQUEST["user_profiles"], "main");
                    JarisCMS\Setting\Save("user_profiles_public", $_REQUEST["user_profiles_public"], "main");
                    JarisCMS\Setting\Save("user_picture", $_REQUEST["user_picture"], "main");
                    JarisCMS\Setting\Save("user_picture_size", $_REQUEST["user_picture_size"], "main");
                    JarisCMS\Setting\Save("image_compression", $_REQUEST["image_compression"], "main");
                    JarisCMS\Setting\Save("image_compression_maxwidth", $_REQUEST["image_compression_maxwidth"], "main");
                    JarisCMS\Setting\Save("image_compression_quality", $_REQUEST["image_compression_quality"], "main");
                    JarisCMS\Setting\Save("home_page", $_REQUEST["home_page"], "main");
                    JarisCMS\Setting\Save("page_not_found", $_REQUEST["page_not_found"], "main");
                    
                    //If data cache was enabled or disabled
                    
                    //Create data cache directory if it doesnt exists
                    if($_REQUEST["data_cache"])
                    {
                        if(!file_exists(JarisCMS\Setting\GetDataDirectory(). "data_cache"));
                            JarisCMS\FileSystem\MakeDir(JarisCMS\Setting\GetDataDirectory(). "data_cache");
                    }
                    //Empty data cache directory and remove it
                    else
                    {
                        $data_cache_dir = JarisCMS\Setting\GetDataDirectory(). "data_cache";

                        if(file_exists($data_cache_dir))
                        {
                            $dir = opendir($data_cache_dir);

                            while(($file = readdir($dir)) !== false)
                            {
                                if($file != "." && $file != "..")
                                    unlink ($data_cache_dir . "/" . $file);
                            }

                            rmdir($data_cache_dir);
                        }        
                    }

                    JarisCMS\System\AddMessage(t("Your settings have been successfully saved."));

                    global $clean_urls;

                    $clean_urls = $_REQUEST["clean_urls"];
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/settings");
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/settings");
            }

            $parameters["name"] = "edit-site-settings";
            $parameters["class"] = "edit-site-settings";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/settings");
            $parameters["method"] = "post";

            $override[t("Enable")] = true;
            $override[t("Disable")] = false;

            $override_fields[] = array("type"=>"radio", "name"=>"override", "id"=>"override", "value"=>$override, "checked"=>$site_settings["override"]);

            $fieldset[] = array("name"=>t("Override settings"), "fields"=>$override_fields, "collapsible"=>true, "collapsed"=>true);
            
            $sitestatus[t("Online")] = true;
            $sitestatus[t("Offline")] = false;

            $sitestatus_fields[] = array("type"=>"radio", "name"=>"site_status", "id"=>"site_status", "value"=>$sitestatus, "checked"=>$site_settings["site_status"]);
            $sitestatus_fields[] = array("type"=>"text", "name"=>"site_status_title", "label"=>t("Status title:"), "id"=>"site_status_title", "value"=>$site_settings["site_status_title"], "description"=>t("A brief description of site status like: Under Construction"));
            $sitestatus_fields[] = array("type"=>"textarea", "name"=>"site_status_description", "label"=>t("Status Description:"), "id"=>"site_status_description", "value"=>$site_settings["site_status_description"], "description"=>t("A detailed description of the site status."));

            $fieldset[] = array("name"=>t("Site Status"), "fields"=>$sitestatus_fields, "collapsible"=>true, "collapsed"=>true);

            $text_fields[] = array("type"=>"text", "name"=>"title", "label"=>t("Site title:"), "id"=>"site-title", "value"=>$site_settings["title"], "required"=>true);
            $text_fields[] = array("type"=>"other", "html_code"=>"<br />");
            $text_fields[] = array("type"=>"checkbox", "checked"=>$site_settings["auto_detect_base_url"], "label"=>t("Auto detect base url?"), "name"=>"auto_detect_base_url", "id"=>"auto_detect_base_url");
            $text_fields[] = array("type"=>"text", "name"=>"base_url", "label"=>t("Site url:"), "id"=>"site-url", "value"=>$site_settings["base_url"]);
            $text_fields[] = array("type"=>"textarea", "name"=>"slogan", "label"=>t("Slogan:"), "id"=>"slogan", "value"=>$site_settings["slogan"], "description"=>t("A short phrase that describes your company or organization goals."));
            $text_fields[] = array("type"=>"textarea", "name"=>"footer_message", "label"=>t("Footer message:"), "id"=>"footer-message", "value"=>$site_settings["footer_message"]);

            $fieldset[] = array("name"=>t("Site info"), "fields"=>$text_fields, "collapsible"=>true, "collapsed"=>true);

            $temp_languages = JarisCMS\Language\GetAll();
            $languages[t("auto-detect")] = "autodetect";
            foreach(JarisCMS\Language\GetAll() as $code=>$name)
            {
                $languages[$name] = $code;
            }

            $language_fields[] = array("type"=>"select", "name"=>"language", "label"=>t("Site language:"), "id"=>"language", "value"=>$languages, "selected"=>$site_settings["language"]);

            include("include/time_zones.php");
            $timezones_list = JarisCMS\System\GetTimezones();
            $timezones = array();
            foreach($timezones_list as $timezone_text)
            {
                $timezones["$timezone_text"] = "$timezone_text";
            }
            $language_fields[] = array("type"=>"select", "label"=>t("Timezone:"), "name"=>"timezone", "id"=>"timezone", "value"=>$timezones, "selected"=>$site_settings["timezone"]);

            $fieldset[] = array("name"=>t("Language and Timezone"), "fields"=>$language_fields, "collapsible"=>true, "collapsed"=>true);

            $new_registrations[t("Enable")] = true;
            $new_registrations[t("Disable")] = false;

            $new_registration_fields[] = array("type"=>"radio", "name"=>"new_registrations", "id"=>"new_registrations", "value"=>$new_registrations, "checked"=>$site_settings["new_registrations"]);
			$new_registration_fields[] = array("type"=>"other", "html_code"=>"<h4>" . t("Require administrator approval?") . "</h4>");
            $new_registration_fields[] = array("type"=>"radio", "name"=>"registration_needs_approval", "id"=>"registration_needs_approval", "value"=>$new_registrations, "checked"=>$site_settings["registration_needs_approval"]);
            
            $new_registration_fields[] = array("type"=>"other", "html_code"=>"<h4>" . t("Registrator can select group?") . "</h4>");
            $new_registration_fields[] = array("type"=>"radio", "name"=>"registration_can_select_group", "id"=>"registration_can_select_group", "value"=>$new_registrations, "checked"=>$site_settings["registration_can_select_group"]);
            
            $new_registration_fields[] = array("type"=>"other", "html_code"=>"<h4>" . t("Groups the registrator can select") . "</h4>");
            $new_registration_fields[] = array("type"=>"other", "html_code"=>"<table class=\"groups-list\">");
            $new_registration_fields[] = array("type"=>"other", "html_code"=>"<thead>");
            $new_registration_fields[] = array("type"=>"other", "html_code"=>"<tr>");
            $new_registration_fields[] = array("type"=>"other", "html_code"=>"<td>".t("Enable")."</td>");
            $new_registration_fields[] = array("type"=>"other", "html_code"=>"<td>".t("Group")."</td>");
            $new_registration_fields[] = array("type"=>"other", "html_code"=>"<td>".t("Description")."</td>");
            $new_registration_fields[] = array("type"=>"other", "html_code"=>"<td>".t("Requires Approval")."</td>");
            $new_registration_fields[] = array("type"=>"other", "html_code"=>"</tr>");
            $new_registration_fields[] = array("type"=>"other", "html_code"=>"</thead>");
            
            $new_registration_fields[] = array("type"=>"other", "html_code"=>"<tbody>");
            
            foreach(JarisCMS\Group\GetList() as $group_name=>$group_machine_name)
            {
                $group_data = JarisCMS\Group\GetData($group_machine_name);
                
                $group_html_code = "<tr>";
                
                $group_checked = "";
                $group_approval_checked = "";
                
                if(is_array($site_settings["registration_groups"]))
                {
                    if(in_array($group_machine_name, $site_settings["registration_groups"]))
                    {
                        $group_checked = "checked=\"checked\"";
                    }
                }
                
                if(is_array($site_settings["registration_groups_approval"]))
                {
                    if(in_array($group_machine_name, $site_settings["registration_groups_approval"]))
                    {
                        $group_approval_checked = "checked=\"checked\"";
                    }
                }
                
                $group_html_code .= "<td><input type=\"checkbox\" $group_checked name=\"registration_groups[]\" value=\"$group_machine_name\" /></td>";
                $group_html_code .= "<td>".t($group_name)."</td>";
                $group_html_code .= "<td>".t($group_data["description"])."</td>";
                $group_html_code .= "<td><input type=\"checkbox\" $group_approval_checked name=\"registration_groups_approval[]\" value=\"$group_machine_name\" /></td>";
        
                $group_html_code .= "</tr>";
                
                $new_registration_fields[] = array("type"=>"other", "html_code"=>$group_html_code);
            }
            
            $new_registration_fields[] = array("type"=>"other", "html_code"=>"</tbody></table>");
            
            $new_registration_fields[] = array("type"=>"textarea", "name"=>"registration_benefits", "label"=>t("Benefits:"), "id"=>"registration_benefits", "value"=>$site_settings["registration_benefits"], "description"=>t("This will be displayed on My Account (admin/user) login page. You can input html and php code."));
            $new_registration_fields[] = array("type"=>"textarea", "name"=>"registration_terms", "label"=>t("Terms and conditions:"), "id"=>"registration_terms", "value"=>$site_settings["registration_terms"], "description"=>t("The terms and conditions users have to agree before registering."));

            $fieldset[] = array("name"=>t("New registrations"), "fields"=>$new_registration_fields, "collapsible"=>true, "collapsed"=>true, "description"=>t("Enables or disable public registrations to the site at the register page."));
            
            $login_authentication[t("Enable")] = true;
            $login_authentication[t("Disable")] = false;

            $login_authentication_fields[] = array("type"=>"other", "html_code"=>"<h4>" . t("Enables or disable the validation of user ip address") . "</h4>");
            $login_authentication_fields[] = array("type"=>"radio", "name"=>"validate_ip", "id"=>"validate_ip", "value"=>$login_authentication, "checked"=>$site_settings["validate_ip"], "description"=>t("This increases security but may result on user logout on dynamic mobile connections that constantly change ip address."));
            
            $login_authentication_fields[] = array("type"=>"other", "html_code"=>"<h4>" . t("Force login over encrypted connection (https)") . "</h4>");
            $login_authentication_fields[] = array("type"=>"radio", "name"=>"login_ssl", "id"=>"login_ssl", "value"=>$login_authentication, "checked"=>$site_settings["login_ssl"]);

            $fieldset[] = array("name"=>t("Login and Authentication"), "fields"=>$login_authentication_fields, "collapsible"=>true, "collapsed"=>true);
            
            $cache[t("Enable")] = true;
            $cache[t("Disable")] = false;

            $cache_fields[] = array("type"=>"radio", "name"=>"enable_cache", "id"=>"enable_cache", "value"=>$cache, "checked"=>$site_settings["enable_cache"]);
            
            $cache_fields[] = array("type"=>"other", "html_code"=>"<h4>" . t("PHP pages caching?") . "</h4>");
            
            $cache_fields[] = array("type"=>"radio", "name"=>"cache_php_pages", "id"=>"cache_php_pages", "value"=>$cache, "checked"=>$site_settings["cache_php_pages"]);
            
            $cache_fields[] = array("type"=>"other", "html_code"=>"<h4>" . t("Select databases to ignore on timestamp check") . "</h4>");
            
            $cache_databases = JarisCMS\SQLite\ListDB();
            foreach($cache_databases as $db_name)
            {
                $checked = false;
                
                if($db_name == "search_engine" || $db_name == "users" || $db_name == "cache")
                {
                    continue;
                }
                
                if(is_array($site_settings["cache_ignore_db"]))
                {
                    
                    foreach($site_settings["cache_ignore_db"] as $selected_db)
                    {
                        if($db_name == $selected_db)
                        {
                            $checked = true;
                            break;
                        }
                    }
                }
                
                $cache_fields[] = array("type"=>"checkbox", "checked"=>$checked, "label"=>$db_name, "name"=>"cache_ignore_db[]", "id"=>"cache_ignore_db", "value"=>$db_name);                
            }
            
            $cache_fields[] = array("type"=>"other", "html_code"=>"<h4>" . t("Select types to disable page caching") . "</h4>");
            
            $cache_types = JarisCMS\Type\GetList();
            foreach($cache_types as $type_name=>$type_data)
            {
                $checked = false;
                
                if(is_array($site_settings["cache_ignore_types"]))
                {
                    
                    foreach($site_settings["cache_ignore_types"] as $selected_db)
                    {
                        if($type_name == $selected_db)
                        {
                            $checked = true;
                            break;
                        }
                    }
                }
                
                $cache_fields[] = array("type"=>"checkbox", "checked"=>$checked, "label"=>t($type_data["name"]), "name"=>"cache_ignore_types[]", "id"=>"cache_ignore_types", "value"=>$type_name);                
            }

            $fieldset[] = array("name"=>t("Page Caching"), "fields"=>$cache_fields, "collapsible"=>true, "collapsed"=>true, "description"=>t("Enables or disable the caching of pages content for fast retrieving."));
            
            
            $cache_data[t("Enable")] = true;
            $cache_data[t("Disable")] = false;

            $cache_data_fields[] = array("type"=>"radio", "name"=>"data_cache", "id"=>"validate_ip", "value"=>$cache_data, "checked"=>$site_settings["data_cache"]);

            $fieldset[] = array("name"=>t("Data Caching"), "fields"=>$cache_data_fields, "collapsible"=>true, "collapsed"=>true, "description"=>t("Special option that improves performance on embedded devices or low performance servers."));
            
            
            $scrip_stats[t("Enable")] = true;
            $scrip_stats[t("Disable")] = false;

            $script_stats_fields[] = array("type"=>"radio", "name"=>"view_script_stats", "id"=>"view_script_stats", "value"=>$scrip_stats, "checked"=>$site_settings["view_script_stats"]);

            $fieldset[] = array("name"=>t("Script stats"), "fields"=>$script_stats_fields, "collapsible"=>true, "collapsed"=>true, "description"=>t("Enables or disable the display of script stats at the end of page. For the purpose of measuring JarisCMS performance."));

            $user_profiles[t("Enable")] = true;
            $user_profiles[t("Disable")] = false;

            $user_profiles_fields[] = array("type"=>"radio", "name"=>"user_profiles", "id"=>"user_profiles", "value"=>$user_profiles, "checked"=>$site_settings["user_profiles"]);
            $user_profiles_fields[] = array("type"=>"other", "html_code"=>"<h4>" . t("Public profiles") . "</h4>");
            $user_profiles_fields[] = array("type"=>"radio", "name"=>"user_profiles_public", "id"=>"user_profiles_public", "value"=>$user_profiles, "checked"=>$site_settings["user_profiles_public"]);

            $fieldset[] = array("name"=>t("User profiles"), "fields"=>$user_profiles_fields, "collapsible"=>true, "collapsed"=>true);
            
            $user_picture[t("Enable")] = true;
            $user_picture[t("Disable")] = false;

            $user_fields[] = array("type"=>"radio", "name"=>"user_picture", "id"=>"user_picture", "value"=>$user_picture, "checked"=>$site_settings["user_picture"]);
            $user_fields[] = array("type"=>"text", "label"=>t("Size:"), "name"=>"user_picture_size", "id"=>"user_picture_size", "value"=>$site_settings["user_picture_size"], "description"=>t("The maximun width and height of the picture in the format 100x150 where 100 = width and 150 height."));

            $fieldset[] = array("name"=>t("User picture"), "fields"=>$user_fields, "collapsible"=>true, "collapsed"=>true);

            $image_compression[t("Enable")] = true;
            $image_compression[t("Disable")] = false;

            $image_uploads[] = array("type"=>"radio", "name"=>"image_compression", "id"=>"image_compression", "value"=>$image_compression, "checked"=>$site_settings["image_compression"]);
            $image_uploads[] = array("type"=>"text", "label"=>t("Maximun width:"), "name"=>"image_compression_maxwidth", "id"=>"image_compression_maxwidth", "value"=>$site_settings["image_compression_maxwidth"], "description"=>t("The maximun width for uploaded images."));
            $image_uploads[] = array("type"=>"text", "label"=>t("Image quality:"), "name"=>"image_compression_quality", "id"=>"image_compression_quality", "value"=>$site_settings["image_compression_quality"], "description"=>t("A range from 0 (worst quality, smaller file) to 100 (best quality, biggest file) for jpeg files."));

            $fieldset[] = array("name"=>t("Image compression"), "fields"=>$image_uploads, "collapsible"=>true, "collapsed"=>true);

            $cleanurl[t("Enable")] = true;
            $cleanurl[t("Disable")] = false;

            $clean_fields[] = array("type"=>"radio", "name"=>"clean_urls", "id"=>"cleanurl", "value"=>$cleanurl, "checked"=>$site_settings["clean_urls"]);

            $fieldset[] = array("name"=>t("Clean url"), "fields"=>$clean_fields, "collapsible"=>true, "collapsed"=>true);
            
            $home_fields[] = array("type"=>"uri", "label"=>t("Uri:"), "name"=>"home_page", "id"=>"home_page", "value"=>$site_settings["home_page"], "description"=>t("The uri to the page used as the home page."));

            $fieldset[] = array("name"=>t("Home page"), "fields"=>$home_fields, "collapsible"=>true, "collapsed"=>true);
            
            $page_not_found_fields[] = array("type"=>"uri", "label"=>t("Uri:"), "name"=>"page_not_found", "id"=>"page_not_found", "value"=>$site_settings["page_not_found"], "description"=>t("The uri to the page used as the page not found result."));

            $fieldset[] = array("name"=>t("Page not found"), "fields"=>$page_not_found_fields, "collapsible"=>true, "collapsed"=>true);

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
