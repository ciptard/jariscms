<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Add Page Block Post") ?>
	field;

	field: content		
		<?php
			JarisCMS\Security\ProtectPage(array("add_content_blocks"));
            
            if(!JarisCMS\Page\IsOwner($_REQUEST["uri"]))
            {
                JarisCMS\Security\ProtectPage();
            }

			if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("add-page-block-page"))
			{	
				//Trim uri spaces
				$_REQUEST["uri"] = trim($_REQUEST["uri"]);
				
				//Save Page
				$fields["title"] = $_REQUEST["title"];
				$fields["content"] = $_REQUEST["content"];
				$fields["description"] = $_REQUEST["description"];
				$fields["keywords"] = $_REQUEST["keywords"];
				
				$categories = array();
				foreach(JarisCMS\Category\GetList() as $machine_name=>$values)
				{
					if(isset($_REQUEST[$machine_name]))
					{
						$categories[$machine_name] = $_REQUEST[$machine_name];
					}
				}
				
				$fields["categories"] = $categories;
				if(JarisCMS\Group\GetPermission("input_format_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
				{
					$fields["input_format"] = $_REQUEST["input_format"];
				}
				else
				{
					$fields["input_format"] = "full_html";
				}
				$fields["created_date"] = time();
				$fields["author"] = JarisCMS\Security\GetCurrentUser();
				$fields["type"] = $_REQUEST["type"];

				//Stores the uri of the page to display the edit page after saving.
				$uri = "";

				if(JarisCMS\Page\Create($_REQUEST["page_uri"], $fields, $uri))
				{
					JarisCMS\System\AddMessage(t("The page was successfully created."));
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
				}
				
				//Save Block
				$fields_block["description"] = $_REQUEST["block_description"];
				$fields_block["title"] = $_REQUEST["title"];
				$fields_block["content"] = "";
				$fields_block["uri"] = $uri;
				$fields_block["groups"] = $_REQUEST["groups"];
				$fields_block["post_block"] = "1";
				$fields_block["input_format"] = "php_code";
				$fields_block["order"] = 0;
				$fields_block["display_rule"] = "all_except_listed";
				if(JarisCMS\Group\GetPermission("return_code_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
				{
					$fields_block["return"] = $_REQUEST["return"];
				}

				if(JarisCMS\Block\Add($fields_block, $_REQUEST["position"], $_REQUEST["uri"]))
				{
					JarisCMS\System\AddMessage(t("The blocks was successfully created."));
				}
				else 
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
				}

				JarisCMS\System\GoToPage("admin/pages/blocks", array("uri"=>$_REQUEST["uri"]));
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/pages/blocks", array("uri"=>$_REQUEST["uri"]));
			}

			$parameters["name"] = "add-page-block-page";
			$parameters["class"] = "add-page-block-page";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/pages/blocks/add/page");
			$parameters["method"] = "post";
			
			$categories = JarisCMS\Category\GetList();
			if(count($categories) > 0)
			{
                $fields_categories = JarisCMS\Category\GenerateFieldList();
                $fieldset[] = array("fields"=>$fields_categories, "name"=>t("Categories"), "collapsible"=>true);
			}

			$positions[t("Header")] = "header";
			$positions[t("Left")] = "left";
			$positions[t("Right")] = "right";
			$positions[t("Center")] = "center";
			$positions[t("Footer")] = "footer";
			$positions[t("None")] = "none";

			$fields[] = array("type"=>"hidden", "name"=>"uri", "value"=>$_REQUEST["uri"]);
			$fields[] = array("type"=>"select", "name"=>"position", "label"=>t("Position:"), "id"=>"position", "value"=>$positions, "selected"=>"none");
			$fields[] = array("type"=>"text", "name"=>"block_description", "value"=>$_REQUEST["block_description"], "label"=>t("Description:"), "id"=>"block_description", "required"=>true);
			$fields[] = array("type"=>"text", "name"=>"title", "value"=>$_REQUEST["title"], "label"=>t("Title:"), "id"=>"title", "required"=>true, "description"=>t("Displayed on the web browser title bar and inside the website."));
			$fields[] = array("type"=>"textarea", "name"=>"content", "value"=>$_REQUEST["content"], "label"=>t("Content:"), "id"=>"content");
			
			$fieldset[] = array("fields"=>$fields);
			
			$fields_meta[] = array("type"=>"textarea", "name"=>"description", "value"=>$_REQUEST["description"], "label"=>t("Description:"), "id"=>"description", "description"=>t("Used to generate the meta description for search engines. Leave blank for default."));
			$fields_meta[] = array("type"=>"textarea", "name"=>"keywords", "value"=>$_REQUEST["keywords"], "label"=>t("Keywords:"), "id"=>"keywords", "description"=>t("List of words seperated by comma (,) used to generate the meta keywords for search engines. Leave blank for default."));
			
			$fieldset[] = array("fields"=>$fields_meta, "name"=>t("Meta tags"), "collapsible"=>true, "collapsed"=>true);
			
			if(JarisCMS\Group\GetPermission("input_format_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
			{
				$fields_inputformats = array();
				foreach(JarisCMS\InputFormat\GetAll() as $machine_name=>$fields_formats)
				{
					
					$fields_inputformats[] = array("type"=>"radio", "checked"=>$machine_name=="full_html"?true:false, "name"=>"input_format", "description"=>$fields_formats["description"], "value"=>array($fields_formats["title"]=>$machine_name));
				}			
				$fieldset[] = array("fields"=>$fields_inputformats, "name"=>t("Input Format"));
			}
			
			$fieldset[] = array("fields"=>JarisCMS\Group\GetListForFields(), "name"=>t("Users Access"), "collapsed"=>true, "collapsible"=>true, "description"=>t("Select the groups that can see the block. Don't select anything to display block to everyone."));
			
			if(JarisCMS\Group\GetPermission("return_code_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
			{
				$fields_other[] = array("type"=>"textarea", "name"=>"return", "label"=>t("Return Code:"), "id"=>"return", "description"=>t("PHP code enclosed with &lt;?php code ?&gt; to evaluate if block should display by printing true or false. for example: &lt;?php if(JarisCMS\Security\IsUserLogged()) print \"true\"; else print \"false\"; ?&gt;"));
			}
			
			$fields_other[] = array("type"=>"text", "name"=>"page_uri", "value"=>$_REQUEST["page_uri"], "label"=>t("Uri:"), "id"=>"page_uri", "required"=>true, "description"=>t("The relative path to access the page, for example: section/page, section"));

			$types = array();
			$types_array = JarisCMS\Type\GetList();
			foreach($types_array as $machine_name=>$type_fields)
			{
				$types[t(trim($type_fields["name"]))] = $machine_name;
			}

			$fields_other[] = array("type"=>"select", "name"=>"type", "label"=>t("Type:"), "id"=>"type", "value"=>$types);

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
