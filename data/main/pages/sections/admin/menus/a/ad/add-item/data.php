<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the menu add item page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Add Menu Item") ?>
	field;

	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("view_menus", "add_menu_items"));

			if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("add-menu-item"))
			{
				if(trim($_REQUEST["url"]) == "")
				{
					$_REQUEST["url"] = JarisCMS\URI\FromText($_REQUEST["title"]);
				}
				
				$fields["title"] = $_REQUEST["title"];
				$fields["url"] = $_REQUEST["url"];
				$fields["description"] = $_REQUEST["description"];
                $fields["target"] = $_REQUEST["target"];
				$fields["parent"] = $_REQUEST["parent"];
				$fields["expanded"] = $_REQUEST["expanded"];
				$fields["order"] = 0;

				if(JarisCMS\Menu\AddItem($_REQUEST["menu"], $fields))
				{
					JarisCMS\System\AddMessage(t("The menu item was successfully created."));
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
				}

				JarisCMS\System\GoToPage("admin/menus");
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/menus");
			}

			$parameters["name"] = "add-menu-item";
			$parameters["class"] = "add-menu-item";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/menus/add-item");
			$parameters["method"] = "post";

			$fields[] = array("type"=>"hidden", "name"=>"menu", "value"=>$_REQUEST["menu"]);
			$fields[] = array("type"=>"text", "name"=>"title", "label"=>t("Title:"), "id"=>"title", "required"=>true);
			$fields[] = array("type"=>"uri", "name"=>"url", "label"=>t("Url:"), "id"=>"url", "description"=>t("The relative path to access a page, for example: section/page, section or the full url like http://domain.com/section. Leave empty to auto-generate."));
			$fields[] = array("type"=>"text", "name"=>"description", "label"=>t("Description:"), "id"=>"description", "description"=>T("Small descriptive popup shown to user on mouse over."));
            
            $targets[t("New Window")] = "_blank";
            $targets[t("Current Window")] = "_self";
            $targets[t("Parent frameset")] = "_parent";
            $targets[t("Full body of window")] = "_top";
            
            $fields[] = array("type"=>"select", "value"=>$targets, "selected"=>$_REQUEST["target"]?$_REQUEST["target"]:"_self", "name"=>"target", "label"=>t("Target:"), "id"=>"target");
			
			$menus["&lt;root&gt;"] = "root";

			$menu_items_array = JarisCMS\Menu\GetItemList($_REQUEST["menu"]);

			foreach($menu_items_array as $id=>$items)
			{
				$menus[$items["title"]] = "$id";
			}
			
			$fields[] = array("type"=>"select", "name"=>"parent", "selected"=>"root", "label"=>t("Parent:"), "id"=>"parent", "value"=>$menus);

			$fieldset[] = array("fields"=>$fields);
			
			$fields_expanded[] = array("type"=>"checkbox", "name"=>"expanded", "label"=>t("Show item elements?:"), "id"=>"expanded", "checked"=>false);
			$fieldset[] = array("fields"=>$fields_expanded, "name"=>t("Expanded"));

			$fields_submit[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
			$fields_submit[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));
			
			$fieldset[] = array("fields"=>$fields_submit);

			print "<h3>" . t("Menu:") . " " . t($_REQUEST["menu"]) . "</h3>";

			print JarisCMS\Form\Generate($parameters, $fieldset);
		?>
	field;
	
	field: is_system
		1
	field;
row;
