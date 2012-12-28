<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the right blocks of the page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0

	field: description
		site search
	field;
	
	field: title
		Search
	field;

	field: content
		<?php

			$parameters["class"] = "block-search";
			$parameters["action"] = JarisCMS\URI\PrintURL("search");
			$parameters["method"] = "get";

			$fields[] = array("type"=>"hidden", "name"=>"search", "value"=>1);
			$fields[] = array("type"=>"text", "name"=>"keywords", "id"=>"search", "value"=>$_REQUEST["keywords"]);
			$fields[] = array("type"=>"submit", "value"=>t("Search"));

			$fieldset[] = array("fields"=>$fields);

			print JarisCMS\Form\Generate($parameters, $fieldset);

        ?>
	field;

	field: order
		0
	field;
	
	field: display_rule
		all_except_listed
	field;

	field: pages
	field;

	field: return
		<?php
			if(JarisCMS\URI\Get() == "search")
        	{
               print "false";
            }
            else
            {
               print "true";
            }
        ?>
	field;
	
	field: is_system
		1
	field;

row;
