<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the type edit page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Type") ?>
    field;

    field: content
        <?php

            JarisCMS\Security\ProtectPage(array("view_types", "edit_types"));
            
            JarisCMS\System\AddTab(t("Types"), "admin/types");
            
            JarisCMS\System\AddTab(t("Uploads"), "admin/types/uploads", array("type"=>$_REQUEST["type"]));
            
            JarisCMS\System\AddTab(t("Maximum Posts"), "admin/types/posts", array("type"=>$_REQUEST["type"]));

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("edit-type"))
            {
                $fields["name"] = $_REQUEST["name"];
                $fields["description"] = $_REQUEST["description"];
                $fields["categories"] = $_REQUEST["categories"];
                $fields["uri_scheme"] = $_REQUEST["uri_scheme"];
                $fields["input_format"] = $_REQUEST["input_format"];
                $fields["title_label"] = $_REQUEST["title_label"];
                $fields["title_description"] = $_REQUEST["title_description"];
                $fields["content_label"] = $_REQUEST["content_label"];
                $fields["content_description"] = $_REQUEST["content_description"];

                $error = false;

                if($_REQUEST["name"] == "" || $_REQUEST["description"] == "")
                {
                    $error = true;
                    JarisCMS\System\AddMessage(t("You need to provide all the fields"), "error");
                }

                if(!$error)
                {
                    if(JarisCMS\Type\Edit($_REQUEST["type"], $fields))
                    {
                        JarisCMS\System\AddMessage(t("Your changes have been saved."));
                    }
                    else
                    {
                        JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                    }

                    JarisCMS\System\GoToPage("admin/types");
                }
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/types");
            }

            $type_data = JarisCMS\Type\GetData($_REQUEST["type"]);

            $parameters["name"] = "edit-type";
            $parameters["class"] = "edit-type";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/types/edit");
            $parameters["method"] = "post";

            $fields[] = array("type"=>"hidden", "value"=>$_REQUEST["type"], "name"=>"type");
            $fields[] = array("type"=>"text", "readonly"=>true, "value"=>$_REQUEST["type"], "name"=>"machine_name", "label"=>t("Machine name:"), "id"=>"machine-name", "description"=>t("The machine name of the type also used on the content template."));
            $fields[] = array("type"=>"text", "value"=>$type_data["name"], "name"=>"name", "label"=>t("Name:"), "id"=>"name", "required"=>true, "description"=>t("A human readable name like for example: My Type."));
            $fields[] = array("type"=>"text", "value"=>$type_data["description"], "name"=>"description", "label"=>t("Description:"), "id"=>"description", "required"=>true, "description"=>t("A brief description of the type."));

            $fieldset[] = array("fields"=>$fields);
            
            if(count(JarisCMS\Category\GetList()) > 0)
            {
                $fieldset[] = array("name"=>t("Categories"), "fields"=>JarisCMS\Type\GenerateCategoryFieldList($type_data["categories"]), "collapsible"=>true, "description"=>t("The categories a user can select for this type of content."));
            } 
            
            $fields_uri_scheme[] = array("type"=>"text", "name"=>"uri_scheme", "id"=>"uri_scheme", "value"=>$type_data["uri_scheme"]?$type_data["uri_scheme"]:"{user}/{type}/{title}");
            
            $fieldset[] = array("name"=>t("Uri Scheme"), "fields"=>$fields_uri_scheme, "collapsible"=>true, "description"=>t("The scheme used for the auto generation of every path (uri) created under this type. Available placeholders: {user}, {type} and {title}"));
            
            $fields_inputformats = array();
            foreach(JarisCMS\InputFormat\GetAll() as $machine_name=>$fields_formats)
            {
                $fields_inputformats[] = array("type"=>"radio", "checked"=>$machine_name==$type_data["input_format"]?true:false, "name"=>"input_format", "description"=>$fields_formats["description"], "value"=>array($fields_formats["title"]=>$machine_name));
            }            
            $fieldset[] = array("fields"=>$fields_inputformats, "name"=>t("Default Input Format"));
            
            $fields_labels[] = array("type"=>"text", "label"=>t("Title:"), "name"=>"title_label", "id"=>"title_label", "value"=>$type_data["title_label"]?$type_data["title_label"]:"Title:", "description"=>t("The label of the input title."));
            $fields_labels[] = array("type"=>"textarea", "label"=>t("Title description:"), "name"=>"title_description", "id"=>"title_description", "value"=>$type_data["title_description"]?$type_data["title_description"]:"Displayed on the web browser title bar and inside the website.", "description"=>t("The description of the title."));
            $fields_labels[] = array("type"=>"text","label"=>t("Content:"), "name"=>"content_label", "id"=>"content_label", "value"=>$type_data["content_label"]?$type_data["content_label"]:"Content:", "description"=>t("The label of the input content."));
            $fields_labels[] = array("type"=>"textarea", "label"=>t("Content description:"), "name"=>"content_description", "id"=>"content_description", "value"=>$type_data["content_description"]?$type_data["content_description"]:"", "description"=>t("The description of the content."));
            
            $fieldset[] = array("name"=>t("Labels"), "description"=>t("To replace original labels of title and content when user is adding or editing content of this type."), "fields"=>$fields_labels, "collapsible"=>true);

            $fields_submit[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
            $fields_submit[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

            $fieldset[] = array("fields"=>$fields_submit);

            print JarisCMS\Form\Generate($parameters, $fieldset);
        ?>
    field;

    field: is_system
        1
    field;
row;
