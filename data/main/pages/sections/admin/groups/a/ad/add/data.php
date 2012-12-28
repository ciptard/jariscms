<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the group add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Create Group") ?>
	field;

	field: content
		<?php

			JarisCMS\Security\ProtectPage(array("view_groups", "add_groups"));

			if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("add-group"))
			{
				$fields["name"] = $_REQUEST["name"];
				$fields["description"] = $_REQUEST["description"];

				$message = JarisCMS\Group\Add($_REQUEST["machine_name"], $fields);

				if($message == "true")
				{
					JarisCMS\System\AddMessage(t("The group has been successfully created."));
				}
				else
				{
					//An error ocurred so display the error message
					JarisCMS\System\AddMessage($message, "error");
				}

				JarisCMS\System\GoToPage("admin/groups");
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/groups");
			}

			$parameters["name"] = "add-group";
			$parameters["class"] = "add-group";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/groups/add");
			$parameters["method"] = "post";

			$fields[] = array("type"=>"text", "value"=>$_REQUEST["machine_name"], "name"=>"machine_name", "label"=>t("Machine name:"), "id"=>"machine_name", "required"=>true, "description"=>t("A readable machine name, like for example: my-group."));
			$fields[] = array("type"=>"text", "value"=>$_REQUEST["name"], "name"=>"name", "label"=>t("Name:"), "id"=>"name", "required"=>true, "description"=>t("A human readable name like for example: My Group."));
			$fields[] = array("type"=>"text", "name"=>"description", "label"=>t("Description:"), "id"=>"description", "required"=>true, "description"=>t("A brief description of the group."));

			$fields[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
			$fields[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

			$fieldset[] = array("fields"=>$fields);

			print JarisCMS\Form\Generate($parameters, $fieldset);
		?>
	field;
	
	field: is_system
		1
	field;
row;
