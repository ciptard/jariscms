<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the menus configuration page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Menu Configuration") ?>
	field;

	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("configure_menus"));
			
			if(isset($_REQUEST["btnSave"]))
			{
				JarisCMS\Setting\Save("primary_menu", $_REQUEST["primary"], "main");
				JarisCMS\Setting\Save("secondary_menu", $_REQUEST["secondary"], "main");
				
				JarisCMS\System\AddMessage(t("Your changes have been successfully saved."));
				
				JarisCMS\System\GoToPage("admin/menus");
			}
			else if(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/menus");
			}
		
			$parameters["name"] = "configure-menu";
			$parameters["class"] = "configure-menu";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/menus/configuration");
			$parameters["method"] = "post";

			$menu_list = JarisCMS\Menu\GetList();
	
			$menus = array();
			
			foreach($menu_list as $name)
			{
				$menus[$name] = $name;
			}
			
			$current_primary = JarisCMS\Setting\Get("primary_menu", "main");
			$current_secondary = JarisCMS\Setting\Get("secondary_menu", "main");
			
			$fields[] = array("type"=>"select", "name"=>"primary", "selected"=>$current_primary?$current_primary:"primary", "label"=>t("Primary menu:"), "id"=>"primary", "value"=>$menus, "description"=>t("Menu returned on the \$primary_links template variable"));
			$fields[] = array("type"=>"select", "name"=>"secondary", "selected"=>$current_secondary?$current_secondary:"secondary", "label"=>t("Secondary menu:"), "id"=>"secondary", "value"=>$menus, "description"=>t("Menu returned on the \$secondary_links template variable"));

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
