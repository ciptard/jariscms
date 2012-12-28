<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the categories add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Create Category") ?>
	field;

	field: content
		<?php

			JarisCMS\Security\ProtectPage(array("view_categories", "add_categories"));

			if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("add-category"))
			{
				$fields["name"] = $_REQUEST["name"];
				$fields["description"] = $_REQUEST["description"];
				$fields["multiple"] = $_REQUEST["multiple"];
                $fields["sorting"] = $_REQUEST["sorting"];
                $fields["order"] = 0;

				$message = JarisCMS\Category\Create($_REQUEST["machine_name"], $fields); 
				
				if($message == "true")
				{
					JarisCMS\System\AddMessage(t("The category has been successfully created."));
				}
				else
				{
					JarisCMS\System\AddMessage($message, "error");
				}

				JarisCMS\System\GoToPage("admin/categories");
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/categories");
			}

			$parameters["name"] = "add-category";
			$parameters["class"] = "add-category";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/categories/add");
			$parameters["method"] = "post";

			$fields[] = array("type"=>"text", "value"=>$_REQUEST["machine_name"], "name"=>"machine_name", "label"=>t("Machine name:"), "id"=>"machine_name", "required"=>true, "description"=>t("A readable machine name, like for example: my-category."));
			$fields[] = array("type"=>"text", "value"=>$_REQUEST["name"], "name"=>"name", "label"=>t("Name:"), "id"=>"name", "required"=>true, "description"=>t("A human readable name like for example: My Category."));
			$fields[] = array("type"=>"text", "value"=>$_REQUEST["description"], "name"=>"description", "label"=>t("Description:"), "id"=>"description", "required"=>true, "description"=>t("A brief description of the category."));
			
			$fieldset[] = array("fields"=>$fields);
			
			$fields_multiple[] = array("type"=>"checkbox", "name"=>"multiple", "label"=>t("Enable multiple selection?:"), "id"=>"multiple");
			$fieldset[] = array("fields"=>$fields_multiple, "name"=>t("Multiple"));
            
            $fields_sorting[] = array("type"=>"checkbox", "name"=>"sorting", "label"=>t("Enable subcategory name sorting?:"), "id"=>"sorting");
			$fieldset[] = array("fields"=>$fields_sorting, "name"=>t("Subcategory sorting"), "description"=>t("To enable or disable automatic sorting."));

			$fields_submit[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
			$fields_submit[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

			$fieldset[] = array("fields"=>$fields_submit);

			print JarisCMS\Form\Generate($parameters, $fieldset);
		?>
	field;

	field: is_system
		1
	field;
row;
