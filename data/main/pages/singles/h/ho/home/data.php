<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the deault home page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Welcome to your new Jaris website!") ?>
	field;

	field: content
		<?php
			if(!JarisCMS\Security\IsAdminLogged())
			{
				print t("Enjoy your new webiste, to start working on it login on the left block with your administration account");
			}
			else
			{
				print t("Now that you are logged in you can start by using the administration navigation menu to modify your web page as you like.");
			}
		?>
	field;
	
	field: is_system
		1
	field;
	
row;
