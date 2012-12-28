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

namespace JarisCMS\Module\Rating\System
{
	use JarisCMS\URI;
	use JarisCMS\Group;
	use JarisCMS\Module;
	use JarisCMS\System;
	use JarisCMS\Security;
	
	function GetPageData(&$page_data)
	{   
		$rating_settings = rating_get_settings($page_data[0]["type"]);

		if($rating_settings["enabled"])
		{    
			if(Group\GetPermission("view_ratings", Security\GetCurrentUserGroup()))
			{
				System\AddStyle("modules/rating/styles/ratings.css");
				System\AddScript("modules/rating/scripts/raty/js/jquery.raty.min.js");
				System\AddScript(Module\GetPageURI("ratings-script", "rating"), array("page"=>URI\Get(), "type"=>$page_data[0]["type"]));
			}
		}
	}
	
	function GenerateAdminPage(&$sections)
	{
		$group = Security\GetCurrentUserGroup();

		$title = t("Content");

		foreach($sections as $index=>$sub_section)
		{
			if($sub_section["title"] == $title)
			{
				if(Group\GetPermission("navigate_ratings", Security\GetCurrentUserGroup()))
				{
					$sub_section["sub_sections"][] = array("title"=>t("Navigate Rated Content"), "url"=>URI\PrintURL(Module\GetPageURI("admin/ratings/list", "rating")), "description"=>t("To see which content have been flagged."));   
					$sections[$index]["sub_sections"] = $sub_section["sub_sections"];
				}

				break;
			}
		}
	}
}

namespace JarisCMS\Module\Rating\Group
{
	function GetPermissions(&$permissions, &$group)
	{
		if($group != "guest")
		{
			$ratings["view_ratings"] = t("View");
			$ratings["navigate_ratings"] = t("Navigate");
			$ratings["delete_ratings"] = t("Delete");
			$ratings["rate_content"] = t("Rate content");

			$permissions[t("Ratings")] = $ratings;
		}
		else
		{
			$ratings["view_ratings"] = t("View");

			$permissions[t("Ratings")] = $ratings;
		}
	}
}

namespace JarisCMS\Module\Rating\Page
{
	use JarisCMS\Page;
	use JarisCMS\SQLite;
	
	function Create(&$uri, &$data, &$path)
	{
		$rating_settings = rating_get_settings($data["type"]);

		if($rating_settings["enabled"])
		{
			$fields["uri"] = $uri;
			$fields["day"] = date("j", $data["created_date"]);
			$fields["month"] = date("n", $data["created_date"]);
			$fields["year"] = date("Y", $data["created_date"]);
			$fields["type"] = $data["type"];

			SQLite\EscapeArray($fields);

			$db = SQLite\Open("ratings");

			$insert = "insert into ratings 
			(content_timestamp, day, month, year, uri, type, points, rates_count)
			values
			(
			'{$data['created_date']}',
			{$fields['day']},
			{$fields['month']},
			{$fields['year']},
			'{$fields['uri']}',
			'{$fields['type']}',
			0,
			0
			)";

			SQLite\Query($insert, $db);

			SQLite\Close($db);
		}
	}
	
	function Delete(&$page, &$page_path)
	{
		$fields["uri"] = $page;

		SQLite\EscapeArray($fields);

		//Delete from system db
		$db_system = SQLite\Open("ratings");
		$delete_system = "delete from ratings where uri='{$fields['uri']}'";
		SQLite\Query($delete_system, $db_system);
		SQLite\Close($db_system);
	}
	
	function Move(&$actual_uri, &$new_uri)
	{
		$page_data = Page\GetData($actual_uri);

		$rating_settings = rating_get_settings($page_data["type"]);

		if($rating_settings["enabled"])
		{
			$update = "update ratings set uri='$new_uri' where uri='$actual_uri'";

			$db = SQLite\Open("ratings");

			SQLite\Query($update, $db);

			SQLite\Close($db);
		}
	}
}

namespace JarisCMS\Module\Rating\Theme
{
	use JarisCMS\URI;
	use JarisCMS\Group;
	use JarisCMS\Module;
	use JarisCMS\Security;
	
	function MakeContent(&$content, &$content_title, &$content_data)
	{
		$rating_settings = rating_get_settings($content_data["type"]);

		if($rating_settings["enabled"])
		{    
			if(Group\GetPermission("view_ratings", Security\GetCurrentUserGroup()))
			{   
				$ratings_content = rating_print(URI\Get(), $content_data["type"]);

				$content = $ratings_content . $content;

				$content_data["ratings_content"] = $ratings_content;
			}
		}
	}
	
	function MakeTabsCode(&$tabs_array)
	{
		if(URI\Get() == "admin/types/edit")
		{
			$tabs_array[0][t("Ratings")] = array("uri"=>Module\GetPageURI("admin/types/ratings", "rating"), "arguments"=>array("type"=>$_REQUEST["type"]));
		}
	}
	
	function GetPageTemplateFile(&$page, &$template_path)
	{
		global $theme;
		
		$default_template = "themes/" . $theme . "/page.php";

		if($template_path == $default_template)
		{
			if(URI\Get() == Module\GetPageURI("add/rating", "rating"))
			{
				$template_path = "modules/rating/templates/page-empty.php";
			}
			elseif(URI\Get() == Module\GetPageURI("ratings-script", "rating"))
			{
				$template_path = "modules/rating/templates/page-empty.php";
			}
		}
	}
	
	function GetContentTemplateFile(&$page, &$type, &$template_path)
	{
		global $theme;
		 
		$default_template = "themes/" . $theme . "/content.php";

		if($template_path == $default_template)
		{
			if(URI\Get() == Module\GetPageURI("add/rating", "rating"))
			{
				$template_path = "modules/rating/templates/content-empty.php";
			}
			elseif(URI\Get() == Module\GetPageURI("ratings-script", "rating"))
			{
				$template_path = "modules/rating/templates/content-empty.php";
			}
		}
	}
}

?>