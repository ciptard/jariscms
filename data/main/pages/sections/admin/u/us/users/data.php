<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the users navigation page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Users") ?>
	field;

	field: content
		<?php
			
			if(!JarisCMS\Group\GetPermission("view_users", JarisCMS\Security\GetCurrentUserGroup()))
			{
				JarisCMS\System\GoToPage("admin/user");
			}

            JarisCMS\System\AddTab(t("Navigation View"), "admin/users");
            JarisCMS\System\AddTab(t("List View"), "admin/users/list");
			JarisCMS\System\AddTab(t("Create User"), "admin/users/add");
			JarisCMS\System\AddTab(t("Groups"), "admin/groups");
            JarisCMS\System\AddTab(t("Export"), "admin/users/export");

			$directories = array();
			if(isset($_REQUEST["uri"]))
			{
				JarisCMS\System\AddMessage(t("You are currently navigating:") . " " . $_REQUEST["uri"], "normal");
				$directories = JarisCMS\FileSystem\GetDirectoriesFromPath(JarisCMS\Setting\GetDataDirectory() . "users/" . $_REQUEST["uri"]);
			}
			else
			{
				$directories = JarisCMS\FileSystem\GetDirectoriesFromPath(JarisCMS\Setting\GetDataDirectory() . "users");
			}

			if(count($directories) > 0)
			{
				$navigation = JarisCMS\FileSystem\GenerateNavigationList($directories, JarisCMS\Setting\GetDataDirectory() . "users");

				$groups = array();
				$alphabet = array();
				$pages = array();

				foreach($navigation as $link)
				{
					if($link["type"] == "section")
					{
						$groups[] = $link;
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
				if(count($groups) > 0)
				{
					print "<h3>" . t("Groups") . "</h3>";
					print "<ul>";
					foreach($groups as $link)
					{
						if($link["type"] == "section")
						{
							$url = JarisCMS\URI\PrintURL("admin/users", array("uri"=>$link['path']));
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
							$url = JarisCMS\URI\PrintURL("admin/users", array("uri"=>$link['path']));
							print "<a href=\"$url\">{$link['current']}</a> &nbsp;";
						}
					}
					print "</fieldset>";
				}

				if(count($pages) > 0)
				{
					print "<h3>" . t("Users") . "</h3>";
					print "<ul>";
					foreach($pages as $link)
					{
						if($link["type"] == "page")
						{
							$uri = JarisCMS\FileSystem\GetURIFromPath($link['path']);
							$url = JarisCMS\URI\PrintURL("admin/users/edit", array("username"=>$uri));
							print "<li><a href=\"$url\">{$link['current']}</a></li>";
						}
					}
					print "</ul>";
				}
			}
		?>
	field;

	field: is_system
		1
	field;
row;
