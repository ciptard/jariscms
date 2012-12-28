﻿<?php
/**
 *Copyright 2008, Jefferson Gonzï¿½lez (JegoYalu.com)
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
		<?php print t("Blocks Post Settings") ?>
	field;

	field: content
		<?php

			JarisCMS\Security\ProtectPage(array("edit_post_settings_content_blocks"));
            
            if(!JarisCMS\Page\IsOwner($_REQUEST["uri"]))
            {
                JarisCMS\Security\ProtectPage();
            }
			
			$page_uri = $_REQUEST["uri"];
			$arguments["uri"] = $page_uri;

			JarisCMS\System\AddTab(t("Edit"), "admin/pages/edit", $arguments);
			JarisCMS\System\AddTab(t("View"), $_REQUEST["uri"]);
			JarisCMS\System\AddTab(t("Blocks"), "admin/pages/blocks", $arguments);
			JarisCMS\System\AddTab(t("Images"), "admin/pages/images", $arguments);
			JarisCMS\System\AddTab(t("Files"), "admin/pages/files", $arguments);
			JarisCMS\System\AddTab(t("Translate"), "admin/pages/translate", $arguments);
			JarisCMS\System\AddTab(t("Delete"), "admin/pages/delete", $arguments);
			
			JarisCMS\System\AddTab(t("Create Block"), "admin/pages/blocks/add", $arguments, 1);
			JarisCMS\System\AddTab(t("Create Post Block"), "admin/pages/blocks/add/page", $arguments, 1);
			JarisCMS\System\AddTab(t("Post Settings"), "admin/pages/blocks/post/settings", $arguments, 1);

			if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("edit-block-post-settings"))
			{
				$fields["display_title"] = $_REQUEST["display_title"];
				$fields["display_image"] = $_REQUEST["display_image"];
				$fields["thumbnail_width"] = $_REQUEST["thumbnail_width"];
				$fields["thumbnail_height"] = $_REQUEST["thumbnail_height"];
				$fields["thumbnail_background_color"] = $_REQUEST["thumbnail_background_color"];
				$fields["keep_aspect_ratio"] = $_REQUEST["keep_aspect_ratio"];
				$fields["maximum_words"] = $_REQUEST["maximum_words"];
				$fields["display_view_more"] = $_REQUEST["display_view_more"];
		
				//Check if write is possible and continue to write settings
				if(JarisCMS\Block\SetPagePostSettings($fields, $_REQUEST["uri"]))
				{
					JarisCMS\System\AddMessage(t("Post settings successfully saved."));
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
			
			$settings = JarisCMS\Block\GetPagePostSettings($_REQUEST["uri"]);

			$parameters["name"] = "edit-block-post-settings";
			$parameters["class"] = "edit-block-post-settings";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/pages/blocks/post/settings");
			$parameters["method"] = "post";

			$enable_disable[t("Enable")] = true;
			$enable_disable[t("Disable")] = false;
			
			$display_title_fields[] = array("type"=>"radio", "name"=>"display_title", "id"=>"display_title", "value"=>$enable_disable, "checked"=>$settings["display_title"]);
			$fieldset[] = array("name"=>t("Display post title"), "fields"=>$display_title_fields, "collapsible"=>true, "collapsed"=>false);

			$display_image_fields[] = array("type"=>"radio", "name"=>"display_image", "id"=>"display_image", "value"=>$enable_disable, "checked"=>$settings["display_image"]);
			$display_image_fields[] = array("type"=>"text", "name"=>"thumbnail_width", "label"=>t("Thumbnail width:"), "id"=>"thumbnail_width", "value"=>$settings["thumbnail_width"], "required"=>true, "description"=>t("The maximum width of the image thumbnail in pixels."));
			$display_image_fields[] = array("type"=>"text", "name"=>"thumbnail_height", "label"=>t("Thumbnail height:"), "id"=>"thumbnail_height", "value"=>$settings["thumbnail_height"], "description"=>t("The maximum height of the image thumbnail in pixels."));
			$display_image_fields[] = array("type"=>"color", "name"=>"thumbnail_background_color", "label"=>t("Background color:"), "id"=>"thumbnail_background_color", "value"=>$settings["thumbnail_background_color"], "description"=>t("The background color of the thumbnail in case is neccesary."));
			$display_image_fields[] = array("type"=>"other", "html_code"=>"<br />");
			$display_image_fields[] = array("type"=>"checkbox", "label"=>t("Keep aspect ratio?"), "name"=>"keep_aspect_ratio", "id"=>"keep_aspect_ratio", "checked"=>$settings["keep_aspect_ratio"]);
			$fieldset[] = array("name"=>t("Display image thumbnail"), "fields"=>$display_image_fields, "collapsible"=>true, "collapsed"=>false);
			
			$display_link_fields[] = array("type"=>"radio", "name"=>"display_view_more", "id"=>"display_view_more", "value"=>$enable_disable, "checked"=>$settings["display_view_more"]);
			$fieldset[] = array("name"=>t("Display view more link"), "fields"=>$display_link_fields, "collapsible"=>true, "collapsed"=>false);

			$fields[] = array("type"=>"text", "name"=>"maximum_words", "id"=>"maximum_words", "label"=>t("Maximun amount of words:"), "value"=>$settings["maximum_words"], "required"=>true, "description"=>t("Amount of words displayed of the page summary."));

			$fields[] = array("type"=>"hidden", "name"=>"uri", "value"=>$_REQUEST["uri"]);
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
