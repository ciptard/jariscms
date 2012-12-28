<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the video settings management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Rating Settings") ?>
	field;

	field: content
		<?php

			JarisCMS\Security\ProtectPage(array("view_types", "edit_types"));

			//Get exsiting settings or defualt ones if main settings table doesn't exist
			$rating_settings = rating_get_settings($_REQUEST["type"]);

			if(isset($_REQUEST["btnSave"]))
			{
			     $data["enabled"] = $_REQUEST["enabled"];
                 $data["number_of_points"] = $_REQUEST["number_of_points"];
                 $data["on_icon"] = $_REQUEST["on_icon"];
                 $data["off_icon"] = $_REQUEST["off_icon"];
                 $data["half_icon"] = $_REQUEST["half_icon"];
                 $data["hints"] = $_REQUEST["hints"];
                 
				//Check if write is possible and continue to write settings
				if(JarisCMS\Setting\Save($_REQUEST["type"], serialize($data), "ratings"))
				{
					JarisCMS\System\AddMessage(t("Your rating settings have been successfully saved."));
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
				}

				JarisCMS\System\GoToPage("admin/types/edit", array("type"=>$_REQUEST["type"]));
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/types/edit", array("type"=>$_REQUEST["type"]));
			}
            
            JarisCMS\System\AddTab(t("Edit Type"), "admin/types/edit", array("type"=>$_REQUEST["type"]));

			$parameters["name"] = "edit-rating-settings";
			$parameters["class"] = "edit-rating-settings";
			$parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/types/ratings", "rating"));
			$parameters["method"] = "post";
            
            $fields[] = array("type"=>"hidden", "name"=>"type", "value"=>$_REQUEST["type"]);
            
            $enabled[t("Enable")] = true;
			$enabled[t("Disable")] = false;

			$fields[] = array("type"=>"radio", "name"=>"enabled", "id"=>"enabled", "value"=>$enabled, "checked"=>$rating_settings["enabled"]);
            
            $fields[] = array("type"=>"text", "name"=>"number_of_points", "label"=>t("Number of points:"), "id"=>"number_of_points", "value"=>$rating_settings["number_of_points"], "description"=>t("The maximun numbers of points user can selecte when rating."));
            $fields[] = array("type"=>"text", "name"=>"on_icon", "label"=>t("On icon:"), "id"=>"on_icon", "value"=>$rating_settings["on_icon"], "description"=>t("The image used to indicate the active points."));
            $fields[] = array("type"=>"text", "name"=>"off_icon", "label"=>t("Off icon:"), "id"=>"off_icon", "value"=>$rating_settings["off_icon"], "description"=>t("The image used to indicate the disabled points."));
            $fields[] = array("type"=>"text", "name"=>"half_icon", "label"=>t("Half icon:"), "id"=>"half_icon", "value"=>$rating_settings["half_icon"], "description"=>t("The image used to indicate a half point."));
            $fields[] = array("type"=>"textarea", "name"=>"hints", "label"=>t("Hints:"), "id"=>"hints", "value"=>$rating_settings["hints"], "description"=>t("A list of hints seperated by comma that describe each point. An example for 5 points would be: bad, poor, regular, good, gorgeous"));

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
