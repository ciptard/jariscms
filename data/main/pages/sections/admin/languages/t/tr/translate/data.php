<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the language edit strings page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Translate content") ?>
	field;

	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("translate_languages"));

			$language_code = $_REQUEST["code"];
			$type = $_REQUEST["type"];
			$save = $_REQUEST["save"];

			//Display translation form
			if(isset($type) && $type == "page")
			{
				$uri = $_REQUEST["uri"];
				$original_data = JarisCMS\Page\GetData($uri, $language_code);

				$parameters["name"] = "translate-page";
				$parameters["class"] = "translate-page";
				$parameters["action"] = JarisCMS\URI\PrintURL("admin/languages/translate");
				$parameters["method"] = "post";

				$fields[] = array("type"=>"hidden", "name"=>"uri", "value"=>$uri);
				$fields[] = array("type"=>"hidden", "name"=>"code", "value"=>$language_code);
				$fields[] = array("type"=>"hidden", "name"=>"save", "value"=>"page");
				$fields[] = array("type"=>"text", "value"=>$original_data["title"], "name"=>"title", "label"=>t("Title:"), "id"=>"title", "required"=>true);
				$fields[] = array("type"=>"textarea", "value"=>$original_data["content"], "name"=>"content", "label"=>t("Content:"), "id"=>"content");
                
                $fieldset[] = array("fields"=>$fields);
                
                $fields_meta[] = array("type"=>"textarea", "name"=>"description", "value"=>$original_data["description"], "label"=>t("Description:"), "id"=>"description", "description"=>t("Used to generate the meta description for search engines. Leave blank for default."));
    			$fields_meta[] = array("type"=>"textarea", "name"=>"keywords", "value"=>$original_data["keywords"], "label"=>t("Keywords:"), "id"=>"keywords", "description"=>t("List of words seperated by comma (,) used to generate the meta keywords for search engines. Leave blank for default."));
    			
    			$fieldset[] = array("fields"=>$fields_meta, "name"=>t("Meta tags"), "collapsible"=>true, "collapsed"=>true);

				$fields_buttons[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
				$fields_buttons[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

				$fieldset[] = array("fields"=>$fields_buttons);

				print JarisCMS\Form\Generate($parameters, $fieldset);
			}
			else if(isset($type) && $type == "block")
			{

			}
			else if(isset($type) && $type == "content-block")
			{

			}
			else if(isset($type) && $type == "menu")
			{

			}


			//Save translations
			if(isset($save) && $save == "page")
			{
				$uri = $_REQUEST["uri"];

				if(isset($_REQUEST["btnSave"]))
				{
					$original_data = JarisCMS\Page\GetData($uri, $language_code);

					$original_data["title"] = $_REQUEST["title"];
					$original_data["content"] = $_REQUEST["content"];
                    $original_data["description"] = $_REQUEST["description"];
                    $original_data["keywords"] = $_REQUEST["keywords"];

					if(!JarisCMS\Language\TranslatePage($uri, $original_data, $language_code))
					{
						JarisCMS\System\AddMessage(t("Check your write permissions on the <b>language</b> directory."), "error");
					}
					else
					{
						JarisCMS\System\AddMessage(t("Translation saved successfully!"));
					}
				}

				JarisCMS\System\GoToPage($uri);
			}
			else if(isset($save) && $save == "block")
			{

			}
			else if(isset($save) && $save == "content-block")
			{

			}
			else if(isset($save) && $save == "menu")
			{

			}
		?>
	field;
	
	field: is_system
		1
	field;
row;
