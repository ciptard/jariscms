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

namespace JarisCMS\Module\JQueryLightBox\System
{
	use JarisCMS\URI;
	use JarisCMS\Setting;
	
	function GetStyles(&$styles)
	{
		$display_rule = Setting\Get("display_rule", "jquery-lightbox");
		$pages = explode(",", Setting\Get("pages", "jquery-lightbox"));

		if($display_rule == "all_except_listed")
		{
			foreach($pages as $page_check)
			{
				$page_check = trim($page_check);

				//Check if no pages listed and print jquery lightbox styles.
				if($page_check == "")
				{
					$styles[] = URI\PrintURL("modules/jquery_lightbox/lightbox/css/lightbox.css");
					return;
				}

				$page_check = str_replace(array("/", "*"), array("\\/", ".*"), $page_check);
				$page_check = "/^$page_check\$/";

				if(preg_match($page_check, URI\Get()))
				{
					return;
				}
			}

			$styles[] = URI\PrintURL("modules/jquery_lightbox/lightbox/css/lightbox.css");
		}
		else if($display_rule == "just_listed")
		{
			foreach($pages as $page_check)
			{
				$page_check = trim($page_check);
				$page_check = str_replace(array("/", "*"), array("\\/", ".*"), $page_check);
				$page_check = "/^$page_check\$/";

				if(preg_match($page_check, URI\Get()))
				{
					$styles[] = URI\PrintURL("modules/jquery_lightbox/lightbox/css/lightbox.css");
					return;
				}
			}
		}
	}
	
	function GetScripts(&$scripts)
	{
		global $base_url;

		$display_rule = Setting\Get("display_rule", "jquery-lightbox");
		$pages = explode(",", Setting\Get("pages", "jquery-lightbox"));

		if($display_rule == "all_except_listed")
		{
			foreach($pages as $page_check)
			{
				$page_check = trim($page_check);

				//Check if no pages listed and print jquery lightbox styles.
				if($page_check == "")
				{
					$scripts[] = URI\PrintURL("modules/jquery_lightbox/lightbox/jquery.lightbox.js");
					$scripts[] = URI\PrintURL("modules/jquery_lightbox/lightbox/configuration.php") . "?base_url=$base_url";
					return;
				}

				$page_check = str_replace(array("/", "*"), array("\\/", ".*"), $page_check);
				$page_check = "/^$page_check\$/";

				if(preg_match($page_check, URI\Get()))
				{
					return;
				}
			}

			$scripts[] = URI\PrintURL("modules/jquery_lightbox/lightbox/jquery.lightbox.js");
			$scripts[] = URI\PrintURL("modules/jquery_lightbox/lightbox/configuration.php") . "?base_url=$base_url";
		}
		else if($display_rule == "just_listed")
		{
			foreach($pages as $page_check)
			{
				$page_check = trim($page_check);
				$page_check = str_replace(array("/", "*"), array("\\/", ".*"), $page_check);
				$page_check = "/^$page_check\$/";

				if(preg_match($page_check, URI\Get()))
				{
					$scripts[] = URI\PrintURL("modules/jquery_lightbox/lightbox/jquery.lightbox.js");
					$scripts[] = URI\PrintURL("modules/jquery_lightbox/lightbox/configuration.php") . "?base_url=$base_url";
					return;
				}
			}
		}
	}
}

namespace JarisCMS\Module\JQueryLightBox\Theme
{
	use JarisCMS\URI;
	use JarisCMS\Module;
	
	function MakeTabsCode(&$tabs_array)
	{
		if(URI\Get() == "admin/settings")
		{
			$tabs_array[0][t("Jquery Lightbox")] = array("uri"=>Module\GetPageURI("admin/settings/jquery/lightbox", "jquery_lightbox"), "arguments"=>null);
		}
	}
}

?>
