<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file The page that serve for restricted areas.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php 
			JarisCMS\System\GetHTTPStatusHeader(401);
			print t("Access Denied") 
		?>
	field;

	field: content
		<?php
			print t("You dont have sufficient permissions to access the page.") 
		?>
	field;

	field: is_system
		1
	field;
row;
