﻿<?php
/**
 *Copyright 2008, Jefferson Gonzï¿½lez (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content types field add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Create Content Type Field") ?>
    field;

    field: content
        <?php

            JarisCMS\Security\ProtectPage(array("view_types_fields", "add_types_fields"));

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("add-type-fields"))
            {
                $fields["variable_name"] = $_REQUEST["variable_name"];
                $fields["name"] = $_REQUEST["name"];
                $fields["description"] = $_REQUEST["description"];
                $fields["type"] = $_REQUEST["type"];
                $fields["readonly"] = $_REQUEST["readonly"];
                $fields["required"] = $_REQUEST["required"];
                $fields["default"] = $_REQUEST["default"];
                $fields["width"] = $_REQUEST["width"];
                $fields["extensions"] = $_REQUEST["extensions"];
                $fields["size"] = intval($_REQUEST["size"]);
                $fields["values"] = $_REQUEST["values"];
                $fields["captions"] = $_REQUEST["captions"];
                $fields["limit"] = $_REQUEST["limit"];
                $fields["strip_html"] = $_REQUEST["strip_html"];
                $fields["position"] = "0";

                if(JarisCMS\Field\AddType($fields, $_REQUEST["type_name"]))
                {
                    JarisCMS\System\AddMessage(t("The content type field has been successfully created."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/types/fields", array("type"=>$_REQUEST["type_name"]));
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/types/fields", array("type"=>$_REQUEST["type_name"]));
            }

            $parameters["name"] = "add-type-fields";
            $parameters["class"] = "add-type-fields";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/types/fields/add");
            $parameters["method"] = "post";

            $fields[] = array("type"=>"hidden", "name"=>"type_name", "value"=>$_REQUEST["type_name"]);
            $fields[] = array("type"=>"text", "value"=>$_REQUEST["variable_name"], "name"=>"variable_name", "label"=>t("Variable name:"), "id"=>"variable_name", "required"=>true, "description"=>t("The name of the variable used for this field when generating the form code."));
            $fields[] = array("type"=>"text", "value"=>$_REQUEST["name"], "name"=>"name", "label"=>t("Name:"), "id"=>"name", "required"=>true, "description"=>t("A human readable name displayed when the form is generated."));
            $fields[] = array("type"=>"textarea", "value"=>$_REQUEST["description"], "name"=>"description", "label"=>t("Description:"), "id"=>"description", "required"=>true, "description"=>t("A brief description of how the user should fill this field or it's purpose."));

            $types[t("Check box")] = "checkbox";
            $types[t("Color selector")] = "color";
            $types[t("Date picker")] = "date";
            $types[t("File upload")] = "file";
            $types[t("Image upload")] = "image";
            $types[t("Hidden")] = "hidden";
            $types[t("Other")] = "other";
            $types[t("Password")] = "password";
            $types[t("Radio box")] = "radio";
            $types[t("Select")] = "select";
            $types[t("Text")] = "text";
            $types[t("Text area")] = "textarea";
            $types[t("Uri")] = "uri";
			$types[t("Uri area")] = "uriarea";
            
            $fields[] = array("type"=>"select", "value"=>$types, "selected"=>$_REQUEST["type"], "name"=>"type", "label"=>t("Type:"), "id"=>"type", "description"=>t("The type of the form field."));
            
            $fields[] = array("type"=>"text", "value"=>$_REQUEST["limit"], "name"=>"limit", "label"=>t("Input limit:"), "id"=>"limit", "description"=>t("The maximun amount of character the user can insert if this is a text or textarea field. 0 for unlimited."));
            
            $fields[] = array("type"=>"textarea", "value"=>$_REQUEST["default"], "name"=>"default", "label"=>t("Default value:"), "id"=>"default", "description"=>t("The default value for a text, textarea, password, hidden, other or a list like select, radio and checkbox."));
            
            $fields[] = array("type"=>"text", "value"=>$_REQUEST["width"], "name"=>"width", "label"=>t("Image width:"), "id"=>"width", "description"=>t("Maximum width of the image in pixels in case this field is an image upload. 0 for unlimited."));
            
            $fieldset[] = array("fields"=>$fields);
            
            $fields_file[] = array("type"=>"textarea", "value"=>$_REQUEST["extensions"], "name"=>"extensions", "label"=>t("File extensions:"), "id"=>"extensions", "description"=>t("A comma (,) seperated list of extensions allowed for upload in case of file upload. For example: txt, doc, pdf"));
            
            $fields_file[] = array("type"=>"text", "value"=>$_REQUEST["size"], "name"=>"size", "label"=>t("File size:"), "id"=>"size", "description"=>t("The maximum permitted file size in kilobytes. For example: 100k") . " " . t("The maximum file upload size allowed by this server is:") . " " . ini_get("upload_max_filesize"));
            
            $fieldset[] = array("fields"=>$fields_file, "name"=>t("File upload"), "description"=>t("Options used in case the type selected is a file upload."));
            
            $fields_options[] = array("type"=>"checkbox", "checked"=>$_REQUEST["readonly"], "name"=>"readonly", "label"=>t("Read only:"), "id"=>"readonly", "description"=>t("In case the field should be readonly."));
            $fields_options[] = array("type"=>"checkbox", "checked"=>$_REQUEST["required"], "name"=>"required", "label"=>t("Required:"), "id"=>"required", "description"=>t("In case the field should be required."));
            $fields_options[] = array("type"=>"checkbox", "checked"=>$_REQUEST["strip_html"], "name"=>"strip_html", "label"=>t("Strip html:"), "id"=>"strip_html", "description"=>t("To enable stripping of any html tags."));
            
            $fieldset[] = array("fields"=>$fields_options, "name"=>t("Field options"), "description"=>t("Special options for the field."));
            
            $fields_select[] = array("type"=>"textarea", "value"=>$_REQUEST["values"], "name"=>"values", "label"=>t("Values:"), "id"=>"valuess", "description"=>t("A list of values seperated by comma for select, radio and checkbox."));
            $fields_select[] = array("type"=>"textarea", "value"=>$_REQUEST["captions"], "name"=>"captions", "label"=>t("Captions:"), "id"=>"captions", "description"=>t("A list of captions seperated by comma in the same order entered in values in case it is a radio, checkbox or select."));

            $fieldset[] = array("fields"=>$fields_select, "name"=>t("Multiple options"), "description"=>t("Options used in case the type selected is a select, radio or checkbox."));

            $fields_buttons[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
            $fields_buttons[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

            $fieldset[] = array("fields"=>$fields_buttons);

            print JarisCMS\Form\Generate($parameters, $fieldset);
        ?>
    field;

    field: is_system
        1
    field;
row;
