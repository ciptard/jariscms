<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the types uploads management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Type Upload Settings") ?>
	field;

	field: content
		<?php

			JarisCMS\Security\ProtectPage(array("edit_types"));
            
            JarisCMS\System\AddTab(t("Type"), "admin/types/edit", array("type"=>$_REQUEST["type"]));

			//Get exsiting settings or defualt ones if main settings table doesn't exist
			$type_settings = JarisCMS\Type\GetData($_REQUEST["type"]); 

			if(isset($_REQUEST["btnSave"]))
			{
                foreach(JarisCMS\Group\GetList() as $name=>$machine_name)
                {
                    $type_settings["uploads"][$machine_name]["maximum_images"] = $_REQUEST["{$machine_name}_maximum_images"];
                    $type_settings["uploads"][$machine_name]["maximum_files"] = $_REQUEST["{$machine_name}_maximum_files"];
                }
                
				//Check if save was successful
				if(JarisCMS\Type\Edit($_REQUEST["type"], $type_settings))
				{
					JarisCMS\System\AddMessage(t("Your settings have been successfully saved."));
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
				}

				JarisCMS\System\GoToPage("admin/types/edit", array("type"=>$_REQUEST["type"]));
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/types/edit", array("type"=>$_REQUEST["type"]));
			}

			$parameters["name"] = "type-upload-settings";
			$parameters["class"] = "type-upload-settings";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/types/uploads");
			$parameters["method"] = "post";
            
            $fields[] = array("type"=>"hidden", "name"=>"type", "value"=>$_REQUEST["type"]);
            
            foreach(JarisCMS\Group\GetList() as $name=>$machine_name)
            {
                unset($file_fields);
                
                $file_fields[] = array("type"=>"text", "label"=>t("Images:"), "name"=>"{$machine_name}_maximum_images", "id"=>"maximum_images", "value"=>$type_settings["uploads"][$machine_name]["maximum_images"]!=""?$type_settings["uploads"][$machine_name]["maximum_images"]:"-1", "description"=>t("Maximum images user can upload per post. -1 for unlimited"));
                $file_fields[] = array("type"=>"text", "label"=>t("Files:"), "name"=>"{$machine_name}_maximum_files", "id"=>"maximum_files", "value"=>$type_settings["uploads"][$machine_name]["maximum_files"]!=""?$type_settings["uploads"][$machine_name]["maximum_files"]:"-1", "description"=>t("Maximum files user can upload per post. -1 for unlimited"));
                
                $fieldset[] = array("name"=>t($name), "fields"=>$file_fields, "collapsible"=>true);
            }

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
