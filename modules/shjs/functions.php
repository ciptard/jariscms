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

namespace JarisCMS\Module\SHJS\System
{
    use JarisCMS\URI;
    use JarisCMS\Setting;
    
    function GetStyles(&$styles)
    {
        $display_rule = Setting\Get("display_rule", "shjs");
        $pages = explode(",", Setting\Get("pages", "shjs"));

        if($display_rule == "all_except_listed")
        {
            foreach($pages as $page_check)
            {
                $page_check = trim($page_check);

                //Check if no pages listed and print jquery lightbox styles.
                if($page_check == "")
                {
                    $styles[] = URI\PrintURL("modules/shjs/shjs/sh_style.min.css");
                    return;
                }

                $page_check = str_replace(array("/", "/*"), array("\\/", "/.*"), $page_check);
                $page_check = "/^$page_check\$/";

                if(preg_match($page_check, URI\Get()))
                {
                    return;
                }
            }

            $styles[] = URI\PrintURL("modules/shjs/shjs/sh_style.min.css");
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
                    $styles[] = URI\PrintURL("modules/shjs/shjs/sh_style.min.css");
                    return;
                }
            }
        }
    }
    
    function GetScripts(&$scripts)
    {
        global $base_url;

        $display_rule = Setting\Get("display_rule", "shjs");
        $pages = explode(",", Setting\Get("pages", "shjs"));

        if($display_rule == "all_except_listed")
        {
            foreach($pages as $page_check)
            {
                $page_check = trim($page_check);

                //Check if no pages listed and print jquery lightbox styles.
                if($page_check == "")
                {
                    $scripts[] = URI\PrintURL("modules/shjs/shjs/sh_main.min.js");
                    $scripts[] = URI\PrintURL("shjs-init");
                    return;
                }

                $page_check = str_replace(array("/", "/*"), array("\\/", "/.*"), $page_check);
                $page_check = "/^$page_check\$/";

                if(preg_match($page_check, URI\Get()))
                {
                    return;
                }
            }

            $scripts[] = URI\PrintURL("modules/shjs/shjs/sh_main.min.js");
            $scripts[] = URI\PrintURL("shjs-init");
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
                    $scripts[] = URI\PrintURL("modules/shjs/shjs/sh_main.min.js");
                    $scripts[] = URI\PrintURL("shjs-init");
                    return;
                }
            }
        }
    }
}

namespace JarisCMS\Module\SHJS\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Module;
    
    function MakeTabsCode(&$tabs_array)
    {
        if(URI\Get() == "admin/settings")
        {
            $tabs_array[0][t("Syntax Highlighting")] = array("uri"=>Module\GetPageURI("admin/settings/shjs", "shjs"), "arguments"=>null);
        }
    }
    
    function GetPageTemplateFile(&$page, &$template_path)
    {
        $uri = URI\Get();

        if($uri == Module\GetPageURI("shjs-init", "shjs"))
        {
            $template_path = "modules/shjs/templates/page-empty.php";
        }
    }
    
    function GetContentTemplateFile(&$page, &$type, &$template_path)
    {
        $uri = URI\Get();

        if($uri == Module\GetPageURI("shjs-init", "shjs"))
        {
            $template_path = "modules/shjs/templates/content-empty.php";
        }
    }
}

?>
