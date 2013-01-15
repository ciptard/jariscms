<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content edit page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php
            $type_data = JarisCMS\Type\GetData(JarisCMS\Page\GetType($_REQUEST["uri"]));
            
            print t("Edit") . " " . t($type_data["name"]);
        ?>
    field;

    field: content        
        <?php
            JarisCMS\Security\ProtectPage(array("edit_content"));
            
            if(!JarisCMS\Page\IsOwner(trim($_REQUEST["actual_uri"]) != ""?$_REQUEST["actual_uri"]:$_REQUEST["uri"]))
            {
                JarisCMS\Security\ProtectPage();
            }
            
            //Check if client trying to edit system page and exit
            if(JarisCMS\System\IsSystemPage($_REQUEST["uri"]) && !isset($_REQUEST["actual_uri"]))
            {
                JarisCMS\System\AddMessage(t("The content you was trying to edit is a system page."), "error");
                JarisCMS\System\GoToPage("admin/pages");
            }
            
            //Get page data
            $page_data = array();
            
            if(isset($_REQUEST["actual_uri"]))
                $page_data = JarisCMS\Page\GetData($_REQUEST["actual_uri"]);
            else
                $page_data = JarisCMS\Page\GetData($_REQUEST["uri"]);
            
            //If page has no type defaults to 'page' type
            $current_type = trim($page_data["type"]);
            if($current_type == "")
            {
                $current_type = "pages";
            }

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("edit-page-$current_type") && JarisCMS\Field\CheckUploadsFromType($current_type))
            {
                //Check if client is trying to submit content to a system page sending variables thru GET
                if(JarisCMS\System\IsSystemPage($_REQUEST["actual_uri"]))
                {
                    JarisCMS\System\AddMessage(t("The content you was trying to edit is a system page."), "error");
                    JarisCMS\System\GoToPage("admin/pages");
                }
                
                //Trim uri spaces
                $_REQUEST["uri"] = trim($_REQUEST["uri"]);
                $_REQUEST["actual_uri"] = trim($_REQUEST["actual_uri"]);
                
                $fields = JarisCMS\Page\GetData($_REQUEST["actual_uri"]);

                $fields["title"] = $_REQUEST["title"];
                $fields["content"] = $_REQUEST["content"];
                
                if(JarisCMS\Group\GetPermission("add_edit_meta_content", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    $fields["meta_title"] = $_REQUEST["meta_title"];
                    $fields["description"] = $_REQUEST["description"];
                    $fields["keywords"] = $_REQUEST["keywords"];
                }
                
                if(JarisCMS\Group\GetPermission("select_content_groups", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    $fields["groups"] = $_REQUEST["groups"];
                }
                
                if(JarisCMS\Group\GetPermission("select_type_content", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    $fields["type"] = $_REQUEST["type"];
                }
                
                $categories = array();
                foreach(JarisCMS\Category\GetList($fields["type"]) as $machine_name=>$values)
                {
                    if(isset($_REQUEST[$machine_name]))
                    {
                        $categories[$machine_name] = $_REQUEST[$machine_name];
                    }
                }
                
                $fields["categories"] = $categories;
                
                if(JarisCMS\Group\GetPermission("input_format_content", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    $fields["input_format"] = $_REQUEST["input_format"];
                }
                
                $fields["last_edit_by"] = JarisCMS\Security\GetCurrentUser();
                $fields["last_edit_date"] = time();
                
                JarisCMS\Field\AppendFieldsToType($fields["type"], $fields);
                
                if(!JarisCMS\Group\GetPermission("manual_uri_content", JarisCMS\Security\GetCurrentUserGroup()) || $_REQUEST["uri"] == "")
                {
                    $_REQUEST["uri"] = JarisCMS\URI\GenerateForType($fields["type"], $fields["title"], $fields["author"]);
                }

                if(JarisCMS\Page\Edit($_REQUEST["actual_uri"], $fields))
                {
                    JarisCMS\Field\SaveUploadsFromType($fields["type"], $_REQUEST["actual_uri"]);
                    
                    //Update all translations
                    $new_page_data = JarisCMS\Page\GetData($_REQUEST["actual_uri"]);
                    foreach(JarisCMS\Language\GetAll() as $code=>$name)
                    {
                        $translation_path = dt(JarisCMS\Page\GeneratePath($_REQUEST["actual_uri"]), $code);
                        $original_path = JarisCMS\Page\GeneratePath($_REQUEST["actual_uri"]);
                        
                        if($translation_path != $original_path)
                        {
                            $translation_data = JarisCMS\Page\GetData($_REQUEST["actual_uri"], $code);
                            
                            $new_page_data["title"] = $translation_data["title"];
                            $new_page_data["content"] = $translation_data["content"];
                            $new_page_data["description"] = $translation_data["description"];
                            $new_page_data["keywords"] = $translation_data["keywords"];
                            
                            JarisCMS\Language\TranslatePage($_REQUEST["actual_uri"], $new_page_data, $code);
                        }
                    }
                    
                    //Move page to new location
                    if($_REQUEST["actual_uri"] != $_REQUEST["uri"])
                    {
                        JarisCMS\Page\Move($_REQUEST["actual_uri"], $_REQUEST["uri"]);

                        //Also move its translations on the language directory
                        if(JarisCMS\Language\MovePageTranslations($_REQUEST["actual_uri"], $_REQUEST["uri"]))
                        {
                            JarisCMS\System\AddMessage(t("Translations repositioned."));
                        }
                        else
                        {
                            JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("translations_not_moved"), "error");
                        }
                    }

                    JarisCMS\System\AddMessage(t("Your changes have been successfully saved."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/pages/edit", array("uri"=>$_REQUEST["uri"]));
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/pages/edit", array("uri"=>$_REQUEST["actual_uri"]));
            }

            $arguments["uri"] = $_REQUEST["uri"];

            //Tabs
            if(JarisCMS\Group\GetPermission("edit_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Edit"), "admin/pages/edit", $arguments);
            }
            JarisCMS\System\AddTab(t("View"), $_REQUEST["uri"]);
            if(JarisCMS\Group\GetPermission("view_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Blocks"), "admin/pages/blocks", $arguments);
            }
            if(JarisCMS\Group\GetPermission("view_images", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Images"), "admin/pages/images", $arguments);
            }
            if(JarisCMS\Group\GetPermission("view_files", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Files"), "admin/pages/files", $arguments);
            }
            if(JarisCMS\Group\GetPermission("translate_languages", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Translate"), "admin/pages/translate", $arguments);
            }
            if(JarisCMS\Group\GetPermission("delete_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Delete"), "admin/pages/delete", $arguments);
            }

            $parameters["name"] = "edit-page-$current_type";
            $parameters["class"] = "edit-page-$current_type";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/pages/edit");
            $parameters["method"] = "post";
            
            $categories = JarisCMS\Category\GetList($page_data["type"]);
            if(count($categories) > 0)
            {
                $fields_categories = JarisCMS\Category\GenerateFieldList($page_data["categories"], null, $page_data["type"]);
                $fieldset[] = array("fields"=>$fields_categories, "name"=>t("Categories"), "collapsible"=>true);
            }

            $fields[] = array("type"=>"hidden", "name"=>"actual_uri", "value"=>$_REQUEST["actual_uri"]?$_REQUEST["actual_uri"]:$_REQUEST["uri"]);
            $fields[] = array("type"=>"text", "name"=>"title", "value"=>$page_data["title"], "label"=>JarisCMS\Type\GetLabel($page_data["type"], "title_label"), "id"=>"title", "required"=>true, "description"=>JarisCMS\Type\GetLabel($page_data["type"], "title_description"));
            $fields[] = array("type"=>"textarea", "name"=>"content", "value"=>$page_data["content"], "label"=>JarisCMS\Type\GetLabel($page_data["type"], "content_label"), "id"=>"content", "description"=>JarisCMS\Type\GetLabel($page_data["type"], "content_description"));
            
            $fieldset[] = array("fields"=>$fields);
            
            if(JarisCMS\Group\GetPermission("add_edit_meta_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $fields_meta[] = array("type"=>"textarea", "value"=>$page_data["meta_title"], "name"=>"meta_title", "label"=>t("Title:"), "id"=>"meta_title", "description"=>t("Overrides the original page title on search engine results. Leave blank for default."));
                $fields_meta[] = array("type"=>"textarea", "value"=>$page_data["description"], "name"=>"description", "label"=>t("Description:"), "id"=>"description", "description"=>t("Used to generate the meta description for search engines. Leave blank for default."));
                $fields_meta[] = array("type"=>"textarea", "value"=>$page_data["keywords"], "name"=>"keywords", "label"=>t("Keywords:"), "id"=>"keywords", "description"=>t("List of words seperated by comma (,) used to generate the meta keywords for search engines. Leave blank for default."));
                
                $fieldset[] = array("fields"=>$fields_meta, "name"=>t("Meta tags"), "collapsible"=>true, "collapsed"=>true);
            }
            
            if(JarisCMS\Group\GetPermission("input_format_content", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
            {
                $fields_inputformats = array();
                foreach(JarisCMS\InputFormat\GetAll() as $machine_name=>$fields_formats)
                {
                    
                    $fields_inputformats[] = array("type"=>"radio", "checked"=>$machine_name==$page_data["input_format"]?true:false, "name"=>"input_format", "description"=>$fields_formats["description"], "value"=>array($fields_formats["title"]=>$machine_name));
                }            
                $fieldset[] = array("fields"=>$fields_inputformats, "name"=>t("Input Format"));
            }
            
            $extra_fields = JarisCMS\Field\GenerateArrayFromType($current_type, $page_data, "edit");
            
            if($extra_fields)
            {
                $fieldset[] = array("fields"=>$extra_fields);
            }
            
            if(JarisCMS\Group\GetPermission("select_content_groups", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $fieldset[] = array("fields"=>JarisCMS\Group\GetListForFields($page_data["groups"]), "name"=>t("Users Access"), "collapsed"=>true, "collapsible"=>true, "description"=>t("Select the groups that can see this content. Don't select anything to display content to everyone."));
            }
            
            if(JarisCMS\Group\GetPermission("manual_uri_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $fields_other[] = array("type"=>"text", "name"=>"uri", "label"=>t("Uri:"), "id"=>"uri", "value"=>$_REQUEST["uri"], "description"=>t("The relative path to access the page, for example: section/page, section. Leave empty to auto-generate."));
            }

            if(JarisCMS\Group\GetPermission("select_type_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $types = array();
                $types_array = JarisCMS\Type\GetList(JarisCMS\Security\GetCurrentUserGroup(), JarisCMS\Security\GetCurrentUser());
                foreach($types_array as $machine_name=>$type_fields)
                {
                    $types[t(trim($type_fields["name"]))] = $machine_name;
                }
            
                $fields_other[] = array("type"=>"select", "selected"=>$current_type, "name"=>"type", "label"=>t("Type:"), "id"=>"type", "value"=>$types);
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
