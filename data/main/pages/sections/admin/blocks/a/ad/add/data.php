<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the global add block page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Add Block") ?>
	field;

	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("view_blocks", "add_blocks"));

			if(isset($_REQUEST["btnSave"])  && !JarisCMS\Form\CheckFields("add-block"))
			{
				$fields["description"] = $_REQUEST["description"];
				$fields["title"] = $_REQUEST["title"];
				$fields["content"] = $_REQUEST["content"];
				$fields["groups"] = $_REQUEST["groups"];
				if(JarisCMS\Group\GetPermission("input_format_blocks", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
				{
					$fields["input_format"] = $_REQUEST["input_format"];
				}
				$fields["order"] = 0;
				$fields["display_rule"] = $_REQUEST["display_rule"];
				$fields["pages"] = $_REQUEST["pages"];
				if(JarisCMS\Group\GetPermission("return_code_blocks", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
				{
					$fields["return"] = $_REQUEST["return"];
				}

				if(JarisCMS\Block\Add($fields, $_REQUEST["position"], $page = ""))
				{
					JarisCMS\System\AddMessage(t("The block was successfully created."));
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
				}

				JarisCMS\System\GoToPage("admin/blocks");
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/blocks");
			}

			$parameters["name"] = "add-block";
			$parameters["class"] = "add-block";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/blocks/add");
			$parameters["method"] = "post";

			$positions[t("Header")] = "header";
			$positions[t("Left")] = "left";
			$positions[t("Right")] = "right";
			$positions[t("Center")] = "center";
			$positions[t("Footer")] = "footer";
			$positions[t("None")] = "none";

			$fields[] = array("type"=>"select", "name"=>"position", "label"=>t("Position:"), "id"=>"position", "value"=>$positions, "selected"=>"none");
			$fields[] = array("type"=>"text", "name"=>"description", "value"=>$_REQUEST["description"], "label"=>t("Description:"), "id"=>"description", "required"=>true);
			$fields[] = array("type"=>"text", "name"=>"title", "value"=>$_REQUEST["title"], "label"=>t("Title:"), "id"=>"title");
			$fields[] = array("type"=>"textarea", "name"=>"content", "value"=>$_REQUEST["content"], "label"=>t("Content:"), "id"=>"content");
			
			$fieldset[] = array("fields"=>$fields);
			
			if(JarisCMS\Group\GetPermission("input_format_blocks", JarisCMS\Security\GetCurrentUserGroup()))
			{
				$fields_inputformats = array();
				foreach(JarisCMS\InputFormat\GetAll() as $machine_name=>$fields_formats)
				{
					
					$fields_inputformats[] = array("type"=>"radio", "checked"=>$machine_name=="full_html"?true:false, "name"=>"input_format", "description"=>$fields_formats["description"], "value"=>array($fields_formats["title"]=>$machine_name));
				}			
				$fieldset[] = array("fields"=>$fields_inputformats, "name"=>t("Input Format"));
			}
			
			$fieldset[] = array("fields"=>JarisCMS\Group\GetListForFields(), "name"=>t("Users Access"), "collapsed"=>true, "collapsible"=>true, "description"=>t("Select the groups that can see the block. Don't select anything to display block to everyone."));
			
			$display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
			$display_rules[t("Just display on the listed pages.")] = "just_listed";
			
			$fields_pages[] = array("type"=>"radio", "checked"=>"all_except_listed", "name"=>"display_rule", "id"=>"display_rule", "value"=>$display_rules);
			$fields_pages[] = array("type"=>"uriarea", "name"=>"pages", "label"=>t("Pages:"), "id"=>"pages");
			
			$fieldset[] = array("fields"=>$fields_pages, "name"=>"Pages to display", "description"=>t("List of uri's seperated by comma (,). Also supports the wildcard (*), for example: my-section/*"));
			
			if(JarisCMS\Group\GetPermission("return_code_blocks", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
			{
				$fields_other[] = array("type"=>"textarea", "name"=>"return", "value"=>$_REQUEST["return"], "label"=>t("Return Code:"), "id"=>"return", "description"=>t("PHP code enclosed with &lt;?php code ?&gt; to evaluate if block should display by printing true or false. for example: &lt;?php if(JarisCMS\Security\IsUserLogged()) print \"true\"; else print \"false\"; ?&gt;"));
			}

			$fields_other[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
			$fields_other[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

			$fieldset[] = array("fields"=>$fields_other);

			print JarisCMS\Form\Generate($parameters, $fieldset);
		?>
	field;
	
	field: is_system
		1
	field;
row;
