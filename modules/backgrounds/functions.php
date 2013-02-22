<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module functions file
 *
 *@note File that stores all hook functions.
 */

namespace JarisCMS\Module\Backgrounds\System
{
    use JarisCMS\URI;
    use JarisCMS\Group;
    use JarisCMS\Module;
    use JarisCMS\System;
    use JarisCMS\Setting;
    use JarisCMS\Security;
    
    function GetScripts(&$scripts)
    {
        global $base_url;

        $backgrounds_settings = Setting\GetAll("backgrounds");
        $backgrounds = unserialize($backgrounds_settings["backgrounds"]);

        if(System\IsSystemPage(URI\Get()))
        {
            return;
        }

        if(is_array($backgrounds) && count($backgrounds) > 0)
        {
            //Sort array from just_listed to all_except_listed
            $just_listed = array();
            $all_except_listed = array();
            foreach($backgrounds as $id=>$data)
            {
                if($data["display_rule"] == "just_listed")
                {
                    $just_listed[$id] = $data;
                }
                else
                {
                    $all_except_listed[$id] = $data;
                }
            }

            $backgrounds = array();

            foreach($just_listed as $id=>$data)
            {
                $backgrounds[$id] = $data;
            }

            foreach($all_except_listed as $id=>$data)
            {
                $backgrounds[$id] = $data;
            }
            //end sort

            foreach($backgrounds as $id=>$data)
            {    
                $display_rule = $data["display_rule"];
                $pages = explode(",", $data["pages"]);

                if($display_rule == "all_except_listed")
                {
                    foreach($pages as $page_check)
                    {
                        $page_check = trim($page_check);

                        //Check if no pages listed and print jquery lightbox styles.
                        if($page_check == "")
                        {
                            if($data["multi"])
                                $scripts[] = URI\PrintURL("modules/backgrounds/scripts/backstretch/jquery.backstretch.min.js");
                            
                            $scripts[] = URI\PrintURL(Module\GetPageURI("script/background", "backgrounds"), array("id"=>$id));
                            return;
                        }

                        $page_check = str_replace(array("/", "/*"), array("\\/", "/.*"), $page_check);
                        $page_check = "/^$page_check\$/";

                        if(preg_match($page_check, URI\Get()))
                        {
                            return;
                        }
                    }
                    
                    if($data["multi"])
                        $scripts[] = URI\PrintURL("modules/backgrounds/scripts/backstretch/jquery.backstretch.min.js");
                    
                    $scripts[] = URI\PrintURL(Module\GetPageURI("script/background", "backgrounds"), array("id"=>$id));
                }
                else if($display_rule == "just_listed")
                {
                    foreach($pages as $page_check)
                    {
                        $page_check = trim($page_check);
                        $page_check = str_replace(array("/", "/*"), array("\\/", "/.*"), $page_check);
                        $page_check = "/^$page_check\$/";

                        if(preg_match($page_check, URI\Get()))
                        {
                            if($data["multi"])
                                $scripts[] = URI\PrintURL("modules/backgrounds/scripts/backstretch/jquery.backstretch.min.js");
                            
                            $scripts[] = URI\PrintURL(Module\GetPageURI("script/background", "backgrounds"), array("id"=>$id));
                            return;
                        }
                    }
                }
            }
        }
    }
    
    function GenerateAdminPage(&$sections)
    {
        $group = Security\GetCurrentUserGroup();

        $title = t("Settings");

        foreach($sections as $index=>$sub_section)
        {
            if($sub_section["title"] == $title)
            {
                if(Group\GetPermission("edit_settings", Security\GetCurrentUserGroup()))
                {
                    $sub_section["sub_sections"][] = array("title"=>t("Backgrounds"), "url"=>URI\PrintURL(Module\GetPageURI("admin/settings/backgrounds", "backgrounds")), "description"=>t("To see, add and edit the background images of the site."));   
                    $sections[$index]["sub_sections"] = $sub_section["sub_sections"];
                }

                break;
            }
        }
    }
}

namespace JarisCMS\Module\Backgrounds\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Module;
    
    function MakeTabsCode(&$tabs_array)
    {
        if(URI\Get() == "admin/settings")
        {
            $tabs_array[0][t("Backgrounds")] = array("uri"=>Module\GetPageURI("admin/settings/backgrounds", "backgrounds"), "arguments"=>null);
        }
    }
    
    function GetPageTemplateFile(&$page, &$template_path)
    {
        $uri = URI\Get();

        if($uri == Module\GetPageURI("script/background", "backgrounds"))
        {
            $template_path = "modules/backgrounds/templates/page-empty.php";
        }
    }
    
    function GetContentTemplateFile(&$page, &$type, &$template_path)
    {
        $uri = URI\Get();

        if($uri == Module\GetPageURI("script/background", "backgrounds"))
        {
            $template_path = "modules/backgrounds/templates/content-empty.php";
        }
    }
}

?>
