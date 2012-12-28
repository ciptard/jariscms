<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the administration page for shjs.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("RSS Selector") ?>
	field;

	field: content
		<?php
		
			if(isset($_REQUEST["btnView"]))
			{
				if($_REQUEST["type"] != "")
				{
					JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("rss", "rss"), array("type"=>$_REQUEST["type"]));
				}
				
				JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("rss", "rss"));
			}

			$parameters["name"] = "rss-selector";
			$parameters["class"] = "rss-selector";
			$parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("rss/selector", "rss"));
			$parameters["method"] = "post";
			
			$types = JarisCMS\Type\GetList();
			$types_list = array();
			$types_list[t("All")] = "";
			
			foreach($types as $type_name=>$type_data)
			{
				$types_list[t($type_data["name"])] = $type_name;
			}

			$fields[] = array("type"=>"select", "name"=>"type", "label"=>t("Type of content:"), "id"=>"type", "value"=>$types_list, "selected"=>"");
			$fields[] = array("type"=>"submit", "name"=>"btnView", "value"=>t("View"));

			$fieldset[] = array("fields"=>$fields);

			print JarisCMS\Form\Generate($parameters, $fieldset);

		?>
	field;

	field: is_system
		1
	field;
row;
