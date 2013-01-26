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

namespace JarisCMS\Module\IPInfo\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Module;
    
    function MakeTabsCode(&$tabs_array)
    {
        if(URI\Get() == "admin/settings")
        {
            $tabs_array[0][t("IP Info")] = array("uri"=>Module\GetPageURI("admin/settings/ipinfo", "ipinfo"), "arguments"=>null);
        }
    }
    
    function GetPageTemplateFile(&$page, &$template_path)
    {
        $uri = URI\Get();
        
        if($uri == Module\GetPageURI("ip-city", "ipinfo"))
        {
            $template_path = "modules/ipinfo/templates/page-empty.php";
        }
        else if($uri == Module\GetPageURI("ip-country", "ipinfo"))
        {
            $template_path = "modules/ipinfo/templates/page-empty.php";
        }
    }
    
    function GetContentTemplateFile(&$page, &$type, &$template_path)
    {
        $uri = URI\Get();

        if($uri == Module\GetPageURI("ip-city", "ipinfo"))
        {
            $template_path = "modules/ipinfo/templates/content-empty.php";
        }
        else if($uri == Module\GetPageURI("ip-country", "ipinfo"))
        {
            $template_path = "modules/ipinfo/templates/content-empty.php";
        }
    }
}

?>
