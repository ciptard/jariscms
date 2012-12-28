<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module functions file
 *
 *@note File that stores all hook functions.
 */

namespace
{
	$display_whizzywig_on_current_page = false;
}

namespace JarisCMS\Module\Whizzywig\Form
{
	use JarisCMS\Setting;
	use JarisCMS\Security;
	use JarisCMS\URI;
	use JarisCMS\Module;
	
	function Generate(&$parameters, &$fieldsets)
	{
		global $display_whizzywig_on_current_page, $page_data;

		$actual_items = unserialize(Setting\Get("toolbar_items", "whizzywig"));
		$textarea_id = unserialize(Setting\Get("teaxtarea_id", "whizzywig"));
		$forms_to_display = unserialize(Setting\Get("forms", "whizzywig"));
		$groups = unserialize(Setting\Get("groups", "whizzywig"));
		$disable_editor = unserialize(Setting\Get("disable_editor", "whizzywig"));

		if(!is_array($actual_items)) $actual_items = array();
		if(!is_array($textarea_id)) $textarea_id = array();
		if(!is_array($forms_to_display)) $forms_to_display = array();
		if(!is_array($groups)) $groups = array();
		if(!is_array($disable_editor)) $disable_editor = array();

		if(!$actual_items[Security\GetCurrentUserGroup()])
		{
			$actual_items[Security\GetCurrentUserGroup()] = "all";
		}

		if(!$textarea_id[Security\GetCurrentUserGroup()])
		{
			$textarea_id[Security\GetCurrentUserGroup()] = "content";
		}
		else
		{
			$textarea_id[Security\GetCurrentUserGroup()] = explode(",", $textarea_id[Security\GetCurrentUserGroup()]);
		}

		if(!$forms_to_display[Security\GetCurrentUserGroup()])
		{
			$forms_to_display[] = "add-page,edit-page,translate-page,add-page-block,block-page-edit,add-block,block-edit,add-page-block-page";
		}
		else
		{
			$forms_to_display[Security\GetCurrentUserGroup()] = explode(",", $forms_to_display[Security\GetCurrentUserGroup()]);
		}

		//Check if current user is on one of the groups that can use the editor
		if(!$groups[Security\GetCurrentUserGroup()])
		{
			return;
		}

		foreach($forms_to_display[Security\GetCurrentUserGroup()] as $form_name)
		{
			$form_name = trim($form_name);

			if($parameters["name"] == $form_name)
			{
				if($disable_editor[Security\GetCurrentUserGroup()])
				{
					if(isset($_REQUEST["disable_whizzywig"]))
					{
						$_SESSION["disable_whizzywig"] = 1;
					}
					if(isset($_REQUEST["enable_whizzywig"]))
					{
						$_SESSION["disable_whizzywig"] = 0;
					}
				}

				foreach($textarea_id[Security\GetCurrentUserGroup()] as $id)
				{
					$id = trim($id);

					$full_id = $parameters["name"] . "-" . $id;

					/**
					 * Whizzywig Configuration variables
					 * 
					 * buttonPath = "/btn/"; //path to toolbar button images; "textbuttons" (the default) means don't use images
					 * cssFile = "stylesheet.css"; //url of CSS stylesheet to attach to edit area
					 * imageBrowse = "whizzypic.php"; //path to page for image browser (see below)
					 * linkBrowse = "picklink.php"; //path to page for link browser (see below)
					 */


					$disable = "<input type=\"submit\" name=\"disable_whizzywig\" value=\"" . t("Disable Editor") . "\" />";
					$editor_buttons = URI\PrintURL("modules/whizzywig/whizzywig/icons.png");
					$editor_image_browser = URI\PrintURL(Module\GetPageURI("whizzypic", "whizzywig"), array("uri"=>$_REQUEST["uri"], "element_id"=>$full_id));
					$editor_link_browser = URI\PrintURL(Module\GetPageURI("whizzylink", "whizzywig"), array("uri"=>$_REQUEST["uri"], "element_id"=>$full_id));
					$editor = "
					<script type=\"text/javascript\">
					whizzywig.btn._f = \"$editor_buttons\";
					whizzywig.linkBrowse = \"$editor_link_browser\";
					whizzywig.imageBrowse = \"$editor_image_browser\";
					whizzywig.makeWhizzyWig(\"$full_id\", \"" . $actual_items[Security\GetCurrentUserGroup()] . "\");
					</script>";

					$fields = array();

					foreach($fieldsets as $fieldsets_index=>$fieldset_fields)
					{
						$found = false;
						$fields = array();

						foreach($fieldset_fields["fields"] as $fields_index=>$values)
						{
							if($values["type"] == "textarea" && $values["id"] == $id && ("" . strpos($values["value"], "<?php") . "" == ""))
							{                            
								if($disable_editor[Security\GetCurrentUserGroup()])
								{
									if($_SESSION["disable_whizzywig"])
									{
										$fields[] = array("type"=>"submit", "name"=>"enable_whizzywig", "value"=>t("Enable Editor"));
										$fields[] = $values;
									}
									else
									{
										$values["code"] = "style=\"width: 100%\" width=\"100%\"";
										$values["class"] = "whizzywig";
										$fields[] = $values;
										$fields[] = array("type"=>"other", "html_code"=>$disable . $editor);
									}
								}
								else
								{
									$values["code"] = "style=\"width: 100%\" width=\"100%\"";
									$values["class"] = "whizzywig";
									$fields[] = $values;
									$fields[] = array("type"=>"other", "html_code"=>$editor);
								}

								$new_fields = array();

								foreach($fieldset_fields["fields"]  as $check_index=>$field_data)
								{
									//Copy new fields to the position of replaced textarea with whizzywig
									if($check_index == $fields_index)
									{
										foreach($fields as $field)
										{
											$new_fields[] = $field;
										}
									}

									//Copy the other fields on the fieldset
									else
									{
										$new_fields[] = $field_data;
									}
								}

								//Replace original fields with newly fields with whizzywig added
								$fieldsets[$fieldsets_index]["fields"] = $new_fields;

								//Exit the fields check loop and fieldsets loop
								break 2;
							}
						}
					}
				}

				//Indicates that a field that matched was found and whizzywig should be displayed
				$display_whizzywig_on_current_page = true;

				//Exit the form name search loop since the form name was already found
				break;
			}
		}
	}
}

namespace JarisCMS\Module\Whizzywig\System
{
	use JarisCMS\URI;
	use JarisCMS\Module;
	
	function GetStyles(&$styles)
	{
		global $display_whizzywig_on_current_page;

		if($display_whizzywig_on_current_page )
		{
			$styles[] = URI\PrintURL("modules/whizzywig/whizzywig.css");
		}
	}
	
	function GetScripts(&$scripts)
	{
		global $display_whizzywig_on_current_page;

		if($display_whizzywig_on_current_page)
		{
			$scripts[] = URI\PrintURL("modules/whizzywig/whizzywig/whizzywig-v63.js");
			$scripts[] = URI\PrintURL(Module\GetPageURI("whizzylang", "whizzywig"));
		}
	}
}

namespace JarisCMS\Module\Whizzywig\Theme
{
	use JarisCMS\URI;
	use JarisCMS\Module;
	
	function MakeTabsCode(&$tabs_array)
	{
		if(URI\Get() == "admin/settings")
		{
			$tabs_array[0][t("Whizzywig Editor")] = array("uri"=>Module\GetPageURI("admin/settings/whizzywig", "whizzywig"), "arguments"=>null);
		}
	}
	
	function GetPageTemplateFile(&$page, &$template_path)
	{
		$uri = URI\Get();
		
		if($uri == Module\GetPageURI("whizzypic", "whizzywig"))
		{
			$template_path = "modules/whizzywig/templates/page-empty.php";
		}
		else if($uri == Module\GetPageURI("whizzylink", "whizzywig"))
		{
			$template_path = "modules/whizzywig/templates/page-empty.php";
		}
		else if($uri == Module\GetPageURI("whizzylang", "whizzywig"))
		{
			$template_path = "modules/whizzywig/templates/page-empty-lang.php";
		}
	}
	
	function GetContentTemplateFile(&$page, &$type, &$template_path)
	{
		$uri = URI\Get();

		if($uri == Module\GetPageURI("whizzypic", "whizzywig"))
		{
			$template_path = "modules/whizzywig/templates/content-empty.php";
		}
		else if($uri == Module\GetPageURI("whizzylink", "whizzywig"))
		{
			$template_path = "modules/whizzywig/templates/content-empty.php";
		}
		else if($uri == Module\GetPageURI("whizzylang", "whizzywig"))
		{
			$template_path = "modules/whizzywig/templates/content-empty-lang.php";
		}
	}
}

?>
