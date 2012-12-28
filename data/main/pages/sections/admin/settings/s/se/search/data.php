<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the site settings management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Search Settings") ?>
	field;

	field: content
		<?php

			JarisCMS\Security\ProtectPage(array("edit_settings"));

			//Get exsiting settings or defualt ones if main settings table doesn't exist
			$site_settings = JarisCMS\Setting\GetAll("main");

			if(isset($_REQUEST["btnSave"]))
			{
				//Check if write is possible and continue to write settings
				if(JarisCMS\Setting\Save("search_display_category_titles", $_REQUEST["search_display_category_titles"], "main"))
				{
                    JarisCMS\Setting\Save("search_display_images", $_REQUEST["search_display_images"], "main");
                    JarisCMS\Setting\Save("search_images_width", $_REQUEST["search_images_width"], "main");
                    JarisCMS\Setting\Save("search_images_height", $_REQUEST["search_images_height"], "main");
                    JarisCMS\Setting\Save("search_images_aspect_ratio", $_REQUEST["search_images_aspect_ratio"], "main");
                    JarisCMS\Setting\Save("search_images_background_color", $_REQUEST["search_images_background_color"], "main");
                    JarisCMS\Setting\Save("search_images_types", serialize($_REQUEST["types"]), "main");
                    
                    foreach(JarisCMS\Type\GetList() as $machine_name=>$data)
                    {
                        JarisCMS\Setting\Save("{$machine_name}_fields", $_REQUEST["{$machine_name}_fields"], "main");
                    }

					JarisCMS\System\AddMessage(t("Your settings have been successfully saved."));
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
				}

				JarisCMS\System\GoToPage("admin/settings/search");
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/settings/search");
			}

			$parameters["name"] = "edit-search-settings";
			$parameters["class"] = "edit-search-settings";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/settings/search");
			$parameters["method"] = "post";
            
            $display_category_titles[t("Enable")] = true;
			$display_category_titles[t("Disable")] = false;

			$category_fields[] = array("type"=>"radio", "name"=>"search_display_category_titles", "id"=>"search_display_category_titles", "value"=>$display_category_titles, "checked"=>$site_settings["search_display_category_titles"], "description"=>t("Enables displaying the searched categories as the title of the search page."));

			$fieldset[] = array("name"=>t("Display category titles?"), "fields"=>$category_fields, "collapsible"=>true);

			$display_images[t("Enable")] = true;
			$display_images[t("Disable")] = false;

			$image_fields[] = array("type"=>"radio", "name"=>"search_display_images", "id"=>"search_display_images", "value"=>$display_images, "checked"=>$site_settings["search_display_images"]);
			$image_fields[] = array("type"=>"text", "label"=>t("Width:"), "name"=>"search_images_width", "id"=>"search_images_width", "value"=>$site_settings["search_images_width"]?$site_settings["search_images_width"]:60, "description"=>t("The pixels width of the image displayed on search results. Default: 60px"));
            $image_fields[] = array("type"=>"text", "label"=>t("Height:"), "name"=>"search_images_height", "id"=>"search_images_height", "value"=>$site_settings["search_images_height"]?$site_settings["search_images_height"]:60, "description"=>t("The pixels height of the image displayed on search results. Default: 60px"));
            $image_fields[] = array("type"=>"other", "html_code"=>"<br />");
            $image_fields[] = array("type"=>"checkbox", "checked"=>$site_settings["search_images_aspect_ratio"], "label"=>t("Keep aspect ratio?"), "name"=>"search_images_aspect_ratio", "id"=>"search_images_aspect_ratio");
            $image_fields[] = array("type"=>"color", "label"=>t("Background color:"), "name"=>"search_images_background_color", "id"=>"search_images_background_color", "value"=>$site_settings["search_images_background_color"], "description"=>t("The background color of images when forced aspect ratio in html notation, example: d3d3d3."));

			$fieldset[] = array("name"=>t("Display Images"), "fields"=>$image_fields, "collapsible"=>true);
            
			$fieldset[] = array("name"=>t("Types where displaying images"), "fields"=>JarisCMS\Type\GenerateContentFieldList(unserialize($site_settings["search_images_types"])), "collapsible"=>true);
            
            $type_fields = array();
            foreach(JarisCMS\Type\GetList() as $machine_name=>$data)
            {
                $type_fields[] = array("type"=>"textarea", "label"=>t($data["name"]), "name"=>"{$machine_name}_fields", "id"=>"{$machine_name}_fields", "value"=>$site_settings["{$machine_name}_fields"]?$site_settings["{$machine_name}_fields"]:"content");
            }
            
            $fieldset[] = array("name"=>t("Content type fields"), "fields"=>$type_fields, "collapsible"=>true, "collapsed"=>true, "description"=>t("A list of field names in the format Label:field_name separated by comma for each content type that are displayed on search results. Example: Description:content, Page Views:views, etc."));

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
