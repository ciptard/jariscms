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
        <?php 
            $type_data = JarisCMS\Type\GetData($_REQUEST["type"]);
            
            print t("Add") . " " . t($type_data["name"]);
        ?>
    field;

    field: content        
        <?php
            JarisCMS\Security\ProtectPage(array("add_content"));
            
            if(!JarisCMS\Group\GetTypePermission($_REQUEST["type"], JarisCMS\Security\GetCurrentUserGroup(), JarisCMS\Security\GetCurrentUser()))
            {
                JarisCMS\Security\ProtectPage();
            }

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("add-page-{$_REQUEST['type']}") && JarisCMS\Group\GetTypePermission($_REQUEST["type"], JarisCMS\Security\GetCurrentUserGroup(), JarisCMS\Security\GetCurrentUser()) && JarisCMS\Field\CheckUploadsFromType($_REQUEST["type"]))
            {
                //Trim uri spaces
                $_REQUEST["uri"] = trim($_REQUEST["uri"]);
                
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
                else
                {
                    $fields["groups"] = array();
                }
                
                $categories = array();
                foreach(JarisCMS\Category\GetList($_REQUEST["type"]) as $machine_name=>$values)
                {
                    if(isset($_REQUEST[$machine_name]))
                    {
                        $categories[$machine_name] = $_REQUEST[$machine_name];
                    }
                }
                
                $fields["categories"] = $categories;
                if(JarisCMS\Group\GetPermission("input_format_content", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
                {
                    $fields["input_format"] = $_REQUEST["input_format"];
                }
                else
                {
                    $fields["input_format"] = JarisCMS\Type\GetDefaultInputFormat($_REQUEST["type"]);
                }
                $fields["created_date"] = time();
                $fields["author"] = JarisCMS\Security\GetCurrentUser();
                $fields["type"] = $_REQUEST["type"];
                
                JarisCMS\Field\AppendFieldsToType($fields["type"], $fields);

                //Stores the uri of the page to display the edit page after saving.
                $uri = "";
                
                if(!JarisCMS\Group\GetPermission("manual_uri_content", JarisCMS\Security\GetCurrentUserGroup()) || $_REQUEST["uri"] == "")
                {
                    $_REQUEST["uri"] = JarisCMS\URI\GenerateForType($fields["type"], $fields["title"], $fields["author"]);
                }

                if(JarisCMS\Page\Create($_REQUEST["uri"], $fields, $uri))
                {
                    JarisCMS\Field\SaveUploadsFromType($fields["type"], $uri);
                    
                    JarisCMS\System\AddMessage(t("The page was successfully created."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }
                
                if(JarisCMS\Group\GetPermission("edit_content"))
                {
                    JarisCMS\System\GoToPage("admin/pages/edit", array("uri"=>$uri));
                }
                else
                {
                    JarisCMS\System\GoToPage($uri);
                }
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                if(JarisCMS\Group\GetPermission("view_content"))
                {
                    JarisCMS\System\GoToPage("admin/pages");
                }
                else
                {
                    JarisCMS\System\GoToPage("admin/pages/types");
                }
            }
            else if(!JarisCMS\Group\GetTypePermission($_REQUEST["type"], JarisCMS\Security\GetCurrentUserGroup(), JarisCMS\Security\GetCurrentUser()))
            {
                JarisCMS\System\AddMessage(t("You do not have permissions to add content of that type."), "error");
            }

            $parameters["name"] = "add-page-{$_REQUEST['type']}";
            $parameters["class"] = "add-page-{$_REQUEST['type']}";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/pages/add");
            $parameters["method"] = "post";
            
            $categories = JarisCMS\Category\GetList($_REQUEST["type"]);
            if(count($categories) > 0)
            {
                 
                $fields_categories = JarisCMS\Category\GenerateFieldList(null,null, $_REQUEST["type"]);
                $fieldset[] = array("fields"=>$fields_categories, "name"=>t("Categories"), "collapsible"=>true);
            }

            $fields[] = array("type"=>"text", "name"=>"title", "value"=>$_REQUEST["title"], "label"=>JarisCMS\Type\GetLabel($_REQUEST["type"], "title_label"), "id"=>"title", "required"=>true, "description"=>JarisCMS\Type\GetLabel($_REQUEST["type"], "title_description"));
            $fields[] = array("type"=>"textarea", "name"=>"content", "value"=>$_REQUEST["content"], "label"=>JarisCMS\Type\GetLabel($_REQUEST["type"], "content_label"), "id"=>"content", "description"=>JarisCMS\Type\GetLabel($_REQUEST["type"], "content_description"));
            
            $fieldset[] = array("fields"=>$fields);
            
            if(JarisCMS\Group\GetPermission("add_edit_meta_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $fields_meta[] = array("type"=>"textarea", "name"=>"meta_title", "value"=>$_REQUEST["meta_title"], "label"=>t("Title:"), "id"=>"meta_title", "description"=>t("Overrides the original page title on search engine results. Leave blank for default."));
                $fields_meta[] = array("type"=>"textarea", "name"=>"description", "value"=>$_REQUEST["description"], "label"=>t("Description:"), "id"=>"description", "description"=>t("Used to generate the meta description for search engines. Leave blank for default."));
                $fields_meta[] = array("type"=>"textarea", "name"=>"keywords", "value"=>$_REQUEST["keywords"], "label"=>t("Keywords:"), "id"=>"keywords", "description"=>t("List of words seperated by comma (,) used to generate the meta keywords for search engines. Leave blank for default."));
                
                $fieldset[] = array("fields"=>$fields_meta, "name"=>t("Meta tags"), "collapsible"=>true, "collapsed"=>true);
            }
            
            if(JarisCMS\Group\GetPermission("input_format_content", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
            {
                $fields_inputformats = array();
                foreach(JarisCMS\InputFormat\GetAll() as $machine_name=>$fields_formats)
                {
                    
                    $fields_inputformats[] = array("type"=>"radio", "checked"=>$machine_name==JarisCMS\Type\GetDefaultInputFormat($_REQUEST["type"])?true:false, "name"=>"input_format", "description"=>$fields_formats["description"], "value"=>array($fields_formats["title"]=>$machine_name));
                }            
                $fieldset[] = array("fields"=>$fields_inputformats, "name"=>t("Input Format"));
            }
            
            //If page has no type defaults to 'pages' type
            $current_type = trim($_REQUEST["type"]);
            if($current_type == "")
            {
                $current_type = "pages";
            }
            
            $extra_fields = JarisCMS\Field\GenerateArrayFromType($current_type);
            
            if($extra_fields)
            {
                $fieldset[] = array("fields"=>$extra_fields);
            }
            
            if(JarisCMS\Group\GetPermission("select_content_groups", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $fieldset[] = array("fields"=>JarisCMS\Group\GetListForFields(), "name"=>t("Users Access"), "collapsed"=>true, "collapsible"=>true, "description"=>t("Select the groups that can see this content. Don't select anything to display content to everyone."));
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
            else
            {
                $fields_other[] = array("type"=>"hidden", "name"=>"type", "value"=>$current_type);
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
