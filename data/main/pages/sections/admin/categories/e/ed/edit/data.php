<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the categories edit page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Edit Category") ?>
	field;

	field: content
		<?php

			JarisCMS\Security\ProtectPage(array("view_categories", "edit_categories"));

			if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("edit-category"))
			{
                $fields = JarisCMS\Category\GetData($_REQUEST["category"]);
                
				$fields["name"] = $_REQUEST["name"];
				$fields["description"] = $_REQUEST["description"];
				$fields["multiple"] = $_REQUEST["multiple"];
                $fields["sorting"] = $_REQUEST["sorting"];

				if(JarisCMS\Category\Edit($_REQUEST["category"], $fields))
				{
					JarisCMS\System\AddMessage(t("Your changes have been saved."));
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
				}

				JarisCMS\System\GoToPage("admin/categories");
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/categories");
			}

			$category_data = JarisCMS\Category\GetData($_REQUEST["category"]);

			$parameters["name"] = "edit-category";
			$parameters["class"] = "edit-category";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/categories/edit");
			$parameters["method"] = "post";

			$fields[] = array("type"=>"hidden", "value"=>$_REQUEST["category"], "name"=>"category");
			$fields[] = array("type"=>"text", "readonly"=>true, "value"=>$_REQUEST["category"], "name"=>"machine_name", "label"=>t("Machine name:"), "id"=>"machine-name", "description"=>t("The machine name of the category."));
			$fields[] = array("type"=>"text", "value"=>$category_data["name"], "name"=>"name", "label"=>t("Name:"), "id"=>"name", "required"=>true, "description"=>t("A human readable name like for example: My Category."));
			$fields[] = array("type"=>"text", "value"=>$category_data["description"], "name"=>"description", "label"=>t("Description:"), "id"=>"description", "required"=>true, "description"=>t("A brief description of the category."));

			$fieldset[] = array("fields"=>$fields);

			$fields_multiple[] = array("type"=>"checkbox", "name"=>"multiple", "label"=>t("Enable multiple selection?:"), "id"=>"multiple", "checked"=>$category_data["multiple"]);
			$fieldset[] = array("fields"=>$fields_multiple, "name"=>t("Multiple"));
            
            $fields_sorting[] = array("type"=>"checkbox", "name"=>"sorting", "label"=>t("Enable subcategory name sorting?:"), "id"=>"sorting", "checked"=>$category_data["sorting"]);
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
