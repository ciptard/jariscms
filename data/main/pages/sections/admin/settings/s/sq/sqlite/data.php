<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the sqlite backup center.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Sqlite Database Center") ?>
	field;

	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("edit_settings"));
		?>
		
		<?php

			JarisCMS\System\AddTab(t("Upload Database Backup"), "admin/settings/sqlite/upload");

			$databases = JarisCMS\SQLite\ListDB();
			
			print "<p>" . t("You need to backup first to be able to download.") . "</p>";

			print "<table class=\"languages-list\">\n";

			print "<thead><tr>\n";

			print "<td>" . t("Database") . "</td>\n";
			print "<td>" . t("Operation") . "</td>\n";

			print  "</tr></thead>\n";

			foreach($databases as $name)
			{
				print "<tr>\n";

				print "<td>" . $name . "</td>\n";

				$backup_url = JarisCMS\URI\PrintURL("admin/settings/sqlite/backup",array("name"=>$name));
				$backup_text = t("Backup");
				
				$download_url = JarisCMS\URI\PrintURL("admin/settings/sqlite/download",array("name"=>$name));
				$download_text = t("Download Backup");
				
				$delete_url = JarisCMS\URI\PrintURL("admin/settings/sqlite/delete",array("name"=>$name));
				$delete_text = t("Delete");
				
				$download = "";
				
				if(file_exists(JarisCMS\Setting\GetDataDirectory() . "sqlite/$name.sql"))
				{
					$download = "<a href=\"$download_url\">$download_text</a>";
					
					$backup_text = t("Update Backup");
				}

				print "<td>
						<a href=\"$backup_url\">$backup_text</a>&nbsp;
						$download
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
