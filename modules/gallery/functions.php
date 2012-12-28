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

namespace JarisCMS\Module\ImageGallery\System
{
	use JarisCMS\URI;
	use JarisCMS\Page;
	use JarisCMS\Module;
	use JarisCMS\System;
	
	function Initialization()
	{
		$uri = $_REQUEST["uri"];

		if($uri && URI\Get() != "admin/pages/add")
		{
			$page_data = Page\GetData($uri);
			
			if($page_data["type"] == "gallery")
			{
				switch(URI\Get())
				{
					case "admin/pages/edit":
						System\GoToPage(Module\GetPageURI("admin/pages/gallery/edit", "gallery"), array("uri"=>$uri));
					default:
						break;
				}
			}
		}
		else if($_REQUEST["type"])
		{	
			$page = URI\Get();
			
			if($page == "admin/pages/add" && $_REQUEST["type"] == "gallery")
			{
				System\GoToPage(Module\GetPageURI("admin/pages/gallery/add", "gallery"), array("type"=>"gallery", "uri"=>$uri));
			}
		}
	}
}

namespace JarisCMS\Module\ImageGallery\Theme
{
	use JarisCMS\URI;
	use JarisCMS\Page;
	use JarisCMS\Group;
	use JarisCMS\Module;
	use JarisCMS\System;
	use JarisCMS\Security;
	
	function MakeTabsCode(&$tabs_array)
	{
		if(!System\IsSystemPage())
		{
			$page_data = Page\GetData(URI\Get());
			if($page_data["type"] == "gallery")
			{
				$tabs_array = array();

				if($page_data["author"] == Security\GetCurrentUser() || Security\IsAdminLogged() || Group\GetPermission("edit_all_user_content", Security\GetCurrentUserGroup()))
				{
					$tabs_array[0][t("Edit Gallery")] = array("uri"=>Module\GetPageURI("admin/pages/gallery/edit", "gallery"), "arguments"=>array("uri"=>URI\Get()));
				}
			}
		}
	}
	
	function GetContentTemplateFile(&$page, &$type, &$template_path)
	{
		global $theme;

		$default_template = "themes/" . $theme . "/content.php";

		if($type == "gallery" && $template_path == $default_template)
		{
			$template_path = "modules/gallery/templates/content-gallery.php";
		}
	}
}

?>
