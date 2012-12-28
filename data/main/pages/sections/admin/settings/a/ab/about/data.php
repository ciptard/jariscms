<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the site settings management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("About JarisCMS") ?>
	field;

	field: content
		<?php
			//Stop unauthorized access
			JarisCMS\Security\ProtectPage();
		?>
	
		<?php
			print "<b>" . t("You are using Jaris CMS Version:") . "</b> " . JARIS_CMS_VERSION . "<br /><br />";
            print t("Copyright &copy; 2008 - 2010, All Rights Reserved by JegoYalu.");
            print " " . t("JarisCMS is developed by JegoYalu") . " <a target=\"_blank\" href=\"http://jegoyalu.com\">(jegoyalu.com)</a>";
            print " " . t("and is under the GPL license") . " <a target=\"_blank\" href=\"http://www.gnu.org/licenses/gpl.html\">(http://www.gnu.org/licenses/gpl.html)</a>";
		?>
	field;

	field: is_system
		1
	field;
row;
