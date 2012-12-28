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

namespace JarisCMS\Module\GoogleTranslate\Theme
{
	use JarisCMS\URI;
	use JarisCMS\Module;
	
	function MakeTabsCode(&$tabs_array)
	{
		$uri = URI\Get();
		
		switch($uri)
		{
			case "admin/settings":
				$tabs_array[1][t("Google Translate")] = array("uri"=>Module\GetPageURI("admin/settings/google-translate", "google_translate"), "arguments"=>null);
				break;
		}
	}
}
?>
