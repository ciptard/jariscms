<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the language add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Edit Language Details") ?>
	field;

	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("view_languages", "edit_languages"));
			
			//Prevent editing non existing language code
			if(trim($_REQUEST["code"]) == "")
			{
				JarisCMS\System\GoToPage("admin/languages");
			}
			
			$language_details = JarisCMS\Language\GetInfo($_REQUEST["code"]);

			if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("edit-language"))
			{
				if(JarisCMS\Language\Edit($_REQUEST["code"], $_REQUEST["translator"], $_REQUEST["translator_email"], $_REQUEST["contributors"]))
				{
					JarisCMS\System\AddMessage(t("The language was successfully modified."));
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_language"));
				}
				
				JarisCMS\System\GoToPage("admin/languages");
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/languages");
			}

			$parameters["name"] = "edit-language";
			$parameters["class"] = "edit-language";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/languages/edit-info");
			$parameters["method"] = "post";
			
			$fields[] = array("type"=>"text", "name"=>"code", "value"=>$_REQUEST["code"], "label"=>t("Code:"), "id"=>"code", "readonly"=>true);
			
			$fields[] = array("type"=>"text", "name"=>"name", "value"=>$language_details["name"], "label"=>t("Name:"), "id"=>"name", "readonly"=>true);
			
			$fields[] = array("type"=>"text", "name"=>"translator", "value"=>$language_details["translator"], "label"=>t("Translator:"), "id"=>"translator", "description"=>t("Main translator for this language."));
			
			$fields[] = array("type"=>"text", "name"=>"translator_email", "value"=>$language_details["translator_email"], "label"=>t("E-mail:"), "id"=>"translator_email", "description"=>t("E-mail of the main translator."));
			
			$fields[] = array("type"=>"textarea", "name"=>"contributors", "value"=>$language_details["contributors"], "label"=>t("Contributors:"), "id"=>"contributors", "description"=>t("A list of contributors seperated by a new line for this translation."));

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
