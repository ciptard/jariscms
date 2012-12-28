<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the menu rename page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Rename Menu") ?>
	field;

	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("view_menus", "edit_menus"));

			if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("rename-menu"))
			{	 
				$message = JarisCMS\Menu\Rename($_REQUEST["current_name"], $_REQUEST["new_name"]);

				if($message == "true")
				{
					//If it is  primary or secondary menu change main config also.
					if(JarisCMS\Setting\Get("primary_menu", "main") == $_REQUEST["current_name"])
					{
						JarisCMS\Setting\Save("primary_menu", $_REQUEST["new_name"], "main");
					}
					else if(JarisCMS\Setting\Get("secondary_menu", "main") == $_REQUEST["current_name"])
					{
						JarisCMS\Setting\Save("secondary_menu", $_REQUEST["new_name"], "main");
					}
					
					//update the menu block
					$block = JarisCMS\Block\GetDataByField("menu_name", $_REQUEST["current_name"]);
					$block["menu_name"] = $_REQUEST["new_name"];
					$block["description"] = $_REQUEST["new_name"] . " menu";
					$block["content"] = "<?php\nprint JarisCMS\Theme\MakeLinks(JarisCMS\PHPDB\Sort(JarisCMS\Menu\GetSubItems(\"{$_REQUEST['new_name']}\"),\"order\"), \"{$_REQUEST['new_name']}\");\n?>";
					JarisCMS\Block\EditByField("menu_name", $_REQUEST["current_name"], $block);
				
					JarisCMS\System\AddMessage(t("Menu successfully renamed."));
				}
				else
				{
					JarisCMS\System\AddMessage($message, "error");
				}

				JarisCMS\System\GoToPage("admin/menus");
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/menus");
			}

			$parameters["name"] = "rename-menu";
			$parameters["class"] = "rename-menu";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/menus/rename");
			$parameters["method"] = "post";

			$fields[] = array("type"=>"hidden", "value"=>$_REQUEST["current_name"], "name"=>"current_name");
			$fields[] = array("type"=>"text", "value"=>$_REQUEST["current_name"], "name"=>"new_name", "label"=>t("New name:"), "id"=>"new_name", "description"=>t("A machine readable name. For example: my-menu"), "required"=>true);

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
