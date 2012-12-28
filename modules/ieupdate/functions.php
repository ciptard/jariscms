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

namespace JarisCMS\Module\IEUpdate\System
{
    use JarisCMS\URI;
    use JarisCMS\System;
    
    function GetStyles(&$styles)
    {
        if(System\GetUserBrowser() == "ie" && $_SESSION["ie_check"] != "yes")
        {
            $styles[] = URI\PrintURL("modules/ieupdate/css/style.css");
        }
    }
    
    function GetScripts(&$scripts)
    {
        if(System\GetUserBrowser() == "ie" && $_SESSION["ie_check"] != "yes")
        {
            $scripts[] = URI\PrintURL("ie-update-script");
        }

        $_SESSION["ie_check"] = "yes";
    }
}

namespace JarisCMS\Module\IEUpdate\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Module;
    
    function GetPageTemplateFile(&$page, &$template_path)
    {
        if(URI\Get() == Module\GetPageURI("ie-update-script", "ieupdate"))
        {
            $template_path = "modules/ieupdate/templates/page-empty.php";
        }
    }
    
    function GetContentTemplateFile(&$page, &$type, &$template_path)
    {
        if(URI\Get() == Module\GetPageURI("ie-update-script", "ieupdate"))
        {
            $template_path = "modules/ieupdate/templates/content-empty.php";
        }
    }
}

?>
