<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the groups management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Groups") ?>
	field;

	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("view_groups"));

			JarisCMS\System\AddTab(t("Users"), "admin/users");
			JarisCMS\System\AddTab(t("Create Group"), "admin/groups/add");
		?>

		<?php

			$groups = JarisCMS\Group\GetList();
            $groups["Guest"] = "guest";

			print "<table class=\"groups-list\">\n";

			print "<thead><tr>\n";

			print "<td>" . t("Name") . "</td>\n";
			print "<td>" . t("Description") . "</td>\n";
			print "<td>" . t("Operation") . "</td>\n";

			print  "</tr></thead>\n";

			foreach($groups as $name=>$machine_name)
			{
				$group_data = JarisCMS\Group\GetData($machine_name);
				$description = $group_data["description"];

				print "<tr>\n";

				print "<td>" . t($name) . "</td>\n";
				print "<td>" . t($description) . "</td>\n";

				$edit_url = JarisCMS\URI\PrintURL("admin/groups/edit",array("group"=>$machine_name));
				$permissions_url = JarisCMS\URI\PrintURL("admin/groups/permissions",array("group"=>$machine_name));
				$delete_url = JarisCMS\URI\PrintURL("admin/groups/delete", array("group"=>$machine_name));
				$edit_text = t("Edit");
				$permissions_text = t("Permissions");
				$delete_text = t("Delete");

				print "<td>
						<a href=\"$edit_url\">$edit_text</a>&nbsp;
						<a href=\"$permissions_url\">$permissions_text</a>&nbsp;
						<a href=\"$delete_url\">$delete_text</a>
					   </td>\n";

				print "</tr>\n";
			}

			print "</table>\n";
		?>
	field;
	
	field: is_system
		1
	field;
row;
