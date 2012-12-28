<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the navigation menu available for registered users.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>


row: 0

	field: title
		Home
	field;

	field: url
	field;

	field: description
	field;

	field: order
		0
	field;

	field: parent
		root
	field;

row;

row: 1

	field: title
		Control Center
	field;

	field: url
		admin/start
	field;

	field: description
	field;

	field: order
		1
	field;

	field: parent
		root
	field;

row;

row: 2

	field: title
		My Account
	field;

	field: url
		admin/user
	field;

	field: description
	field;

	field: order
		2
	field;

	field: parent
		root
	field;

row;

row: 3

	field: title
		Logout
	field;

	field: url
		admin/logout
	field;

	field: description
	field;

	field: order
		3
	field;

	field: parent
		root
	field;

row;