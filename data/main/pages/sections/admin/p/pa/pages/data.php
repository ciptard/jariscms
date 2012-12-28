<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content navigation page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Pages") ?>
	field;

	field: content
		<?php
			if(!JarisCMS\Group\GetPermission("view_content", JarisCMS\Security\GetCurrentUserGroup()))
			{
				JarisCMS\System\GoToPage("admin/user/content");
			}

			if(isset($_REQUEST["uri"]))
			{
				JarisCMS\System\AddMessage(t("You are currently navigating:") . " " . $_REQUEST["uri"], "normal");

				$directories = JarisCMS\FileSystem\GetDirectoriesFromPath(JarisCMS\Setting\GetDataDirectory() . "pages/" . $_REQUEST["uri"]);
				$navigation = JarisCMS\FileSystem\GenerateNavigationList($directories, JarisCMS\Setting\GetDataDirectory() . "pages");

				$sections = array();
				$alphabet = array();
				$pages = array();

				//Store Data
				foreach($navigation as $link)
				{
					if($link["type"] == "section")
					{
						$sections[] = $link;
					}
				}

				foreach($navigation as $link)
				{
					if($link["type"] == "alphabet")
					{
						$alphabet[] = $link;
					}
				}

				foreach($navigation as $link)
				{
					if($link["type"] == "page")
					{
						$pages[] = $link;
					}
				}

				//Display Data
				if(count($sections) > 0)
				{
					print "<h3>" . t("Sections") . "</h3>";
					print "<ul>";
					foreach($sections as $link)
					{
						if($link["type"] == "section")
						{
							$url = JarisCMS\URI\PrintURL("admin/pages", array("uri"=>$link['path']));
							print "<li><a href=\"$url\">{$link['current']}</a></li>";
						}
					}
					print "</ul>";
				}

				if(count($alphabet) > 0)
				{
					print "<h3>" . t("Alphabetical") . "</h3>";
					print "<fieldset>";
					foreach($alphabet as $link)
					{
						if($link["type"] == "alphabet")
						{
							$url = JarisCMS\URI\PrintURL("admin/pages", array("uri"=>$link['path']));
							print "<a href=\"$url\">{$link['current']}</a> &nbsp;";
						}
					}
					print "</fieldset>";
				}

				if(count($pages) > 0)
				{
					print "<h3>" . t("Pages") . "</h3>";
					print "<ul>";
					foreach($pages as $link)
					{
						if($link["type"] == "page")
						{
							$uri = JarisCMS\FileSystem\GetURIFromPath($link['path']);
							$url = JarisCMS\URI\PrintURL("admin/pages/edit", array("uri"=>$uri));
							print "<li><a href=\"$url\">{$link['current']}</a></li>";
						}
					}
					print "</ul>";
				}
			}
			else
			{
                JarisCMS\System\AddTab(t("List View"), "admin/pages/list");
				JarisCMS\System\AddTab(t("Create Page"), "admin/pages/types");
				$pages = JarisCMS\URI\PrintURL("admin/pages", array("uri"=>"singles"));
				$sections = JarisCMS\URI\PrintURL("admin/pages", array("uri"=>"sections"));

				print "<h3>" . t("Navigation") . "</h3>";
				print "<a href=\"$pages\">" . t("Singles") . "</<a><br />";
				print "<a href=\"$sections\">" . t("Sections") . "</<a><br />";
			}
		?>
	field;

	field: is_system
		1
	field;
row;
