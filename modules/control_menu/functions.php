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

namespace JarisCMS\Module\ControlMenu\System
{
	use JarisCMS\URI;
	use JarisCMS\Module;
	use JarisCMS\Security;
	
	function GetStyles(&$styles)
	{
		if(Security\IsUserLogged())
		{
			$styles[] = URI\PrintURL("modules/control_menu/styles/style.css");
            $styles[] = URI\PrintURL(Module\GetPageURI("style/control-menu", "control_menu"));
		}
	}
	
	function GetScripts(&$scripts)
	{
		if(Security\IsUserLogged())
		{
			$scripts[] = URI\PrintURL(Module\GetPageURI("script/control-menu", "control_menu"));
		}
	}
}

namespace JarisCMS\Module\ControlMenu\Theme
{
	use JarisCMS\URI;
	use JarisCMS\Module;
    
    function MakeTabsCode(&$tabs_array)
    {
        if(URI\Get() == "admin/settings")
        {
            $tabs_array[0][t("Control Menu")] = array("uri"=>Module\GetPageURI("admin/settings/control-menu", "control_menu"), "arguments"=>null);
        }
    }
	
	function GetPageTemplateFile(&$page, &$template_path)
	{
		$uri = URI\Get();

		if($uri == Module\GetPageURI("script/control-menu", "control_menu"))
		{
			$template_path = "modules/control_menu/templates/page-empty.php";
		}
        elseif($uri == Module\GetPageURI("style/control-menu", "control_menu"))
		{
			$template_path = "modules/control_menu/templates/page-empty.php";
		}
	}
	
	function GetContentTemplateFile(&$page, &$type, &$template_path)
	{
		$uri = URI\Get();

		if($uri == Module\GetPageURI("script/control-menu", "control_menu"))
		{
			$template_path = "modules/control_menu/templates/content-empty.php";
		}
        elseif($uri == Module\GetPageURI("style/control-menu", "control_menu"))
		{
			$template_path = "modules/control_menu/templates/content-empty.php";
		}
	}
}

?>
