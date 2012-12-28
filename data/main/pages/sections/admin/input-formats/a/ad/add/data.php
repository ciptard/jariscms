<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the input format add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Create Input Format") ?>
	field;

	field: content
		<?php

			JarisCMS\Security\ProtectPage(array("add_input_formats"));

			if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("add-input-format"))
			{
				$fields["name"] = $_REQUEST["name"];
				$fields["description"] = $_REQUEST["description"];
                $fields["allowed_tags"] = $_REQUEST["allowed_tags"];
                $fields["parse_url"] = $_REQUEST["parse_url"];
                $fields["parse_email"] = $_REQUEST["parse_email"];
                $fields["parse_line_breaks"] = $_REQUEST["parse_line_breaks"];

				$message = JarisCMS\InputFormat\Add($_REQUEST["machine_name"], $fields);

				if($message == "true")
				{
					JarisCMS\System\AddMessage(t("The input format has been successfully created."));
				}
				else
				{
					JarisCMS\System\AddMessage($message, "error");
				}

				JarisCMS\System\GoToPage("admin/input-formats");
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/input-formats");
			}

			$parameters["name"] = "add-input-format";
			$parameters["class"] = "add-input-format";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/input-formats/add");
			$parameters["method"] = "post";

			$fields[] = array("type"=>"text", "value"=>$_REQUEST["machine_name"], "name"=>"machine_name", "label"=>t("Machine name:"), "id"=>"machine_name", "required"=>true, "description"=>t("A readable machine name, like for example: my-input-format."));
			$fields[] = array("type"=>"text", "value"=>$_REQUEST["name"], "name"=>"name", "label"=>t("Name:"), "id"=>"name", "required"=>true, "description"=>t("A human readable name like for example: My Input Format."));
			$fields[] = array("type"=>"text", "value"=>$_REQUEST["description"], "name"=>"description", "label"=>t("Description:"), "id"=>"description", "required"=>true, "description"=>t("A brief description of the input format."));
            $fields[] = array("type"=>"textarea", "value"=>$_REQUEST["allowed_tags"], "name"=>"allowed_tags", "label"=>t("Allowed tags:"), "id"=>"allowed_tags", "description"=>t("A list of the allowed tags for this input format. Example: &lt;a&gt;&lt;p&gt;&lt;i&gt;&lt;strong&gt;"));

            $fieldset[] = array("fields"=>$fields);

            $true_false[t("Enable")] = true;
			$true_false[t("Disable")] = false;

			$parse_url_fields[] = array("type"=>"radio", "name"=>"parse_url", "id"=>"parse_url", "value"=>$true_false, "checked"=>$_REQUEST["parse_url"]);
            $fieldset[] = array("name"=>t("Parse url's"), "fields"=>$parse_url_fields, "collapsible"=>true, "description"=>t("To enable or disable parsing of url's."));
            
            $parse_email_fields[] = array("type"=>"radio", "name"=>"parse_email", "id"=>"parse_email", "value"=>$true_false, "checked"=>$_REQUEST["parse_email"]);
            $fieldset[] = array("name"=>t("Parse emails"), "fields"=>$parse_email_fields, "collapsible"=>true, "description"=>t("To enable or disable parsing of emails."));
            
            $parse_line_ends_fields[] = array("type"=>"radio", "name"=>"parse_line_breaks", "id"=>"parse_line_breaks", "value"=>$true_false, "checked"=>$_REQUEST["parse_line_breaks"]);
            $fieldset[] = array("name"=>t("Convert line breaks"), "fields"=>$parse_line_ends_fields, "collapsible"=>true, "description"=>t("To enable or disable conversion of line breaks to &lt;br&gt; tag."));

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
