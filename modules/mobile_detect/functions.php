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

namespace JarisCMS\Module\MobileDetect\System
{
    use \Mobile_Detect;
    use JarisCMS\Setting;
    
    function Initialization()
    {
        global $theme;

        if(isset($_REQUEST["device"]))
        {
            if(
                $_REQUEST["device"] == "desktop" ||
                $_REQUEST["device"] == "phone" ||
                $_REQUEST["device"] == "tablet"
            )
                $_SESSION["device"] = $_REQUEST["device"];
        }

        $mobile_theme = Setting\Get("mobile_theme", "mobile_detect");
        $tablet_theme = Setting\Get("tablet_theme", "mobile_detect");

        if(!$mobile_theme)
        {
            if(file_exists("themes/$theme/mobile/info.php"))
            {
                $mobile_theme = "$theme/mobile";
            }
        }

        if(!$tablet_theme)
        {
            if(file_exists("themes/$theme/tablet/info.php"))
            {
                $tablet_theme = "$theme/tablet";
            }
        }

        if(isset($_SESSION["device"]))
        {
            switch($_SESSION["device"])
            {
                case "phone":
                    if($mobile_theme)
                        $theme = $mobile_theme;
                    break;
                case "tablet":
                    if($tablet_theme)
                        $theme = $tablet_theme;
                    break;
            }
        }
        else
        {
            $device = new Mobile_Detect();

            if($device->isMobile() && !$device->isTablet())
            {
                if($mobile_theme)
                    $theme = $mobile_theme;
            }
            elseif($device->isTablet())
            {
                if($tablet_theme)
                    $theme = $tablet_theme;
            }
        }
    }
}

namespace JarisCMS\Module\MobileDetect\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Module;
    
    function GetEnabled(&$themes)
    {
        $themes_copy = array();
    
        foreach($themes as $theme)
        {
            $themes_copy[] = $theme;

            if(is_dir("themes/$theme/mobile"))
                $themes_copy[] = "$theme/mobile";

            if(is_dir("themes/$theme/tablet"))
                $themes_copy[] = "$theme/tablet";
        }

        $themes = $themes_copy;
    }
    
    function MakeTabsCode(&$tabs_array)
    {
        if(
            URI\Get() == "admin/themes" ||
            URI\Get() == "admin/themes/mobile" ||
            URI\Get() == "admin/themes/tablet"
        )
        {
            $tabs_array[0][t("Desktop")] = array("uri"=>"admin/themes", "arguments"=>null);
            $tabs_array[0][t("Mobile")] = array("uri"=>  Module\GetPageURI("admin/themes/mobile", "mobile_detect"), "arguments"=>null);
            $tabs_array[0][t("Tablet")] = array("uri"=>Module\GetPageURI("admin/themes/tablet", "mobile_detect"), "arguments"=>null);
        }
    }
}

?>
