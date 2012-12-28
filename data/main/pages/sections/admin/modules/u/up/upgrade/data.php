<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the module upgrade page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Upgrade Module") ?>
	field;

	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("view_modules", "upgrade_modules"));

			if(isset($_REQUEST["path"]))
			{
				if(upgrade_module($_REQUEST["path"]))
				{
					JarisCMS\System\AddMessage(t("Module successfully upgraded."));
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
				}
			}

			JarisCMS\System\GoToPage("admin/modules");
		?>
	field;

	field: is_system
		1
	field;
row;
