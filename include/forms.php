<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Contains forms functions.
 */

namespace JarisCMS\Form;

 /**
  * To check if an email address is genuine.
  *
  * @param string $email The email to check.
  *
  * @return bool True on success false on failure.
  */
function CheckEmail($email){

    if(preg_match('/^[_A-z0-9-]+((\.|\+)[_A-z0-9-]+)*@[A-z0-9-]+(\.[A-z0-9-]+)*(\.[A-z]{2,4})$/',$email))
    {
        //If the function is available we also check the dns record for mx entries
        if(function_exists("checkdnsrr"))
        {
            list($name, $domain) = explode('@',$email);
        
            if(!checkdnsrr($domain,'MX'))
            {
                return false;
            }
        }
        
        return true;
    } 
    
    return false;
}

/**
 * Verifies if a username is valid and contains only letters, numbers, dots and dashes.
 *
 * @param string $username The username to check.
 * 
 * @return bool True if valid false otherwise.
 */
function CheckUserName($username)
{
    $result = preg_replace("/\w+/", "", $username);
    
    if($result != "")
    {
        return false;
    }
    
    return true;
}

/**
 * Verifies if a input string is valid number and contains only numbers and dots.
 *
 * @param string $input The string to check.
 * @param string $number_type The type of number could be float or integer.
 * 
 * @return bool True if valid false otherwise.
 */
function CheckNumber($input, $number_type="float")
{
    $result = "";
    
    if($number_type == "integer")
    {
        $result = preg_replace("/[\d]+/", "", $input);
    }
    else
    {
        $result = preg_replace("/[\d\.]+/", "", $input);
    }
    
    if($result != "")
    {
        return false;
    }
    
    return true;
}
 
 /**
  * To check if all required fields on a generated form where filled up.
  * Prints messages of all required empty fields.
  * 
  * @param array $fieldsets Fieldsets to check.
  * 
  * @return bool true if a required field is empty or false if ok.
  */
 function CheckFields($form_name)
 {     
     $required = false;
    if(is_array($_SESSION["required_fields"][$form_name]))
    {
         foreach($_SESSION["required_fields"][$form_name] as $fields)
         {
             if($fields["type"] == "text" || $fields["type"] == "textarea" || $fields["type"] == "password" ||
               $fields["type"] == "uri" || $fields["type"] == "uriarea")
             {
                 if(!isset($_REQUEST[$fields["name"]]) || $_REQUEST[$fields["name"]] == "")
                 {
                     $required = true;
                 }
             }
             elseif($fields["type"] == "checkbox" || $fields["type"] == "radio" || $fields["type"] == "select")
             {
                 if(!isset($_REQUEST[$fields["name"]]) || $_REQUEST[$fields["name"]] == "")
                 {
                     $required = true;
                 }
             }
             elseif($fields["type"] == "file")
             {
                 if((!isset($_FILES[$fields["name"]]) || $_FILES[$fields["name"]]["tmp_name"] == "") &&
                   (!isset($_REQUEST[$fields["name"]]["names"]))
                )
                 {
                     $required = true;
                 }
             }
         }
    }
    else
    {
        \JarisCMS\System\GoToPage("access-denied");
    }
    
    DisableUploading();
    
    if(is_array($_SESSION["file_upload_fields"][$form_name]))
    {
        foreach($_SESSION["file_upload_fields"][$form_name] as $field_name=>$multiple)
        {
            ProcessUploads($field_name, $multiple);
        }
    }
     
     if($required)
     {
         \JarisCMS\System\AddMessage(t("You need to provide all the required fields, the ones marked with asterik."), "error");
     }
     
     unset($_SESSION["required_fields"][$form_name]);
    
    unset($_SESSION["file_upload_fields"][$form_name]);
    
    $not_validated = false;    
    if(is_array($_SESSION["validation_fields"][$form_name]))
    {
         foreach($_SESSION["validation_fields"][$form_name] as $fields)
         {
            if($_REQUEST[$fields["name"]] != $fields["value"])
            {
                 if($fields["type"] == "validate_sum")
                 {
                     \JarisCMS\System\AddMessage(t("The sum you entered is incorrect."), "error");
                 }
                
                $not_validated = true;
            }
         }
    }
    
    unset($_SESSION["validation_fields"][$form_name]);
    
    if($not_validated)
    {
        return true;
    }
     
     return $required;
 }
 
/**
 * Enable file uploading to upload.php for the current session
 */
function EnableUploading()
{
    $_SESSION["can_upload_file"] = true;
}

/**
 * Disable file uploading to upload.php for the current session
 */
function DisableUploading()
{
    unset($_SESSION["can_upload_file"]);
}

/**
 * Check if file uploading into upload.php is possible for the current session
 */
function CanUpload()
{
    if(isset($_SESSION["can_upload_file"]))
        return true;
    
    return false;
}

/**
 * Add files uploaded with jquery.fileupload to PHP $_FILES array for normal processing
 * @param string $field_name The name of file input field of the form.
 * @param bool $multiple_uploads Indicate if the field has multiple uploads enabled.
 */
function ProcessUploads($field_name, $multiple_uploads=false)
{
    if($multiple_uploads)
    {
        foreach($_REQUEST[$field_name]["names"] as $index=>$value)
        {
            $_FILES[$field_name]["name"][] = $_REQUEST[$field_name]["names"][$index];
            $_FILES[$field_name]["tmp_name"][] = GetUploadPath($_REQUEST[$field_name]["names"][$index]);
            $_FILES[$field_name]["type"][] = $_REQUEST[$field_name]["types"][$index];
        }
    }
    else
    {
        $first_file = true;
        
        foreach($_REQUEST[$field_name]["names"] as $index=>$value)
        {
            //Save first file uploaded only.
            if($first_file)
            {
                $_FILES[$field_name]["name"] = $_REQUEST[$field_name]["names"][$index];
                $_FILES[$field_name]["tmp_name"] = GetUploadPath($_REQUEST[$field_name]["names"][$index]);
                $_FILES[$field_name]["type"] = $_REQUEST[$field_name]["types"][$index];
                
                $first_file = false;
                
                continue;
            }
            
            //In case some one uploaded more than 1 file for a field not marked as
            //multiple the rest of the files are deleted.
            unlink(GetUploadPath($_REQUEST[$field_name]["names"][$index]));
        }
    }
}

/**
 * Get file upload path from current session and then remove it from session.
 * @param string $file_name The name of the file
 * @return string Path to file
 */
function GetUploadPath($file_name)
{
    $file_path = $_SESSION["uploaded_files"][$file_name];
    
    unset($_SESSION["uploaded_files"][$file_name]);
    
    return $file_path;
}

/**
 * Delete all files uploaded by current user.
 * Useful to keep upload dir clean by running it each time the user logs in.
 */
function DeleteAllUploads()
{
    $upload_dir = str_replace(
        "data.php", 
        "uploads/", 
        \JarisCMS\User\GeneratePath(
            \JarisCMS\Security\GetCurrentUser(), 
            \JarisCMS\Security\GetCurrentUserGroup()
        )
    );
    
    if(is_dir($upload_dir))
    {
        foreach(\JarisCMS\FileSystem\GetFiles($upload_dir) as $file)
        {
            unlink($file);
        }
    }
}

/**
 * Function to create the code of html form.
 *
 * @param array $parameters An array in the format array["parameter_name"] = "value"
 *        for example: parameters["method"] = "post"
 * @param array $fieldsets The needed data to create the form in the format:
 *        $fieldset[] = array(
 *        "name"="value", //Optional value if used a <fieldset> with <legend> is generated
 *          "collapsible"=>true or false //Optional value to specify if fieldset should have collapsible class
 *        "fields"[] = array(
 *              "type"=>"text, hidden, file, password, submit, reset, select, textarea, radio, checkbox, other",
 *             "id"=>"value",
 *            "name"=>"value",
 *             "class"=>"value" //Optional appended to current class
 *            "label"=>"value", //Optional
 *            "value"=>"value" or for selects, checkbox and radio array("label", "value"), //Optional
 *            "size"=>"value", //Optional
 *            "description"=>"value" //Optional
 *            "readonly"=>true or false //Optional for password or text
 *            "multiple"=>true or false value used on a select
 *            "code"=>"example (width="100%")" //Optional parameters passed to field tags
 *        )
 *        )
 *
 * @return string The html code for a form.
 */
function Generate($parameters, $fieldsets)
{        
    //Call Generate hook before running function
    \JarisCMS\Module\Hook("Form", "Generate", $parameters, $fieldsets);
    
    $_SESSION["required_fields"][$parameters["name"]] = array();
    
    //Check if a field of file type exists
    foreach($fieldsets as $fieldset)
    {
        foreach($fieldset["fields"] as $field)
        {
            if($field["type"] == "file")
            {
                $parameters["enctype"] = "multipart/form-data";
                break;
            }    
        }
    }
    
    // Store scripts code that give dynamic functionality to controls to 
    // place them on the bottom of form since they conflict with collapse
    // functionality.
    $scripts = "";

    $form = "<form ";
    foreach($parameters as $name=>$value)
    {
        $form .= "$name=\"$value\" ";
    }
    $form .= ">\n";

    foreach($fieldsets as $fieldset)
    {
        if($fieldset["name"])
        {
            $collapsible = "";
            $legend = "<legend>{$fieldset['name']}</legend>\n";
            if($fieldset["collapsible"] && $fieldset["collapsed"])
            {
                $collapsible = "class=\"collapsible collapsed\"";
                $legend = "<legend><a class=\"expand\" href=\"javascript:void(0)\">{$fieldset['name']}</a></legend>";
            }
            else
            {
                $collapsible = "class=\"collapsible\"";
                $legend = "<legend><a class=\"collapse\" href=\"javascript:void(0)\">{$fieldset['name']}</a></legend>";
            }

            $form .= "<fieldset $collapsible>\n";
            $form .= $legend;
        }

        foreach($fieldset["fields"] as $field)
        {
            $field["id"] = $parameters["name"] . "-" . $field["id"];
            
            //Convert special characters to html
            if(is_string($field["value"]))
            {
                $field["value"] = htmlspecialchars($field["value"]);
            }
            
            //print label
            if($field["label"])
            {
                // Dont display label for single checkboxe since this
                // should be added to a fields set
                if($field["type"] != "checkbox" || ($field["type"] == "checkbox" && is_array($field["value"])))
                {
                    $required = "";
                    if($field["required"])
                    {
                        //Register field as required on session variable required_fields
                        $_SESSION["required_fields"][$parameters["name"]][] = array("type"=>$field["type"], "name"=>str_replace("[]", "", $field["name"]));
                        $required = "<span class=\"required\"> *</span>";
                    }

                    $form .= "<div class=\"caption\">";
                    $form .= "<label for=\"{$field['id']}\"><span>{$field['label']}</span>$required</label>";
                    $form .= "</div>\n";
                }
            }

            if($field['class'])
            {
                $field['class'] = "-" . $field['class'];
            }

            //print field
            if($field["type"] == "hidden")
            {
                $form .= "<input type=\"{$field['type']}\" name=\"{$field['name']}\" value=\"{$field['value']}\" />";
            }
            elseif($field["type"] == "text" || $field["type"] == "password")
            {
                $readonly = null;
                if($field["readonly"])
                {
                    $readonly = "readonly=\"readonly\"";
                }

                $form .= "<div class=\"field\">";
                $form .= "<input {$field['code']} $readonly id=\"{$field['id']}\" class=\"form-{$field['type']}{$field['class']}\" type=\"{$field['type']}\" name=\"{$field['name']}\" value=\"{$field['value']}\" size=\"{$field['size']}\" />";
                $form .= "</div>\n";
                
                if($field["limit"] && ($field["type"] == "text" || $field["type"] == "password"))
                {
                    \JarisCMS\System\AddScript("scripts/optional/jquery.limit.js");
                    $field["description"] .= " <span class=\"form-chars-left\" id=\"{$field["id"]}-limit\">{$field['limit']}</span>&nbsp;" . "<span class=\"form-chars-left-label\">" . t("characters left") . "</span>";
                    $scripts .= "<script>$(\"#{$field["id"]}\").limit('{$field['limit']}', '#{$field["id"]}-limit')</script>"; 
                }
            }
            elseif($field["type"] == "file")
            {
                EnableUploading();
                
                $readonly = null;
                if($field["readonly"])
                {
                    $readonly = "readonly=\"readonly\"";
                }
                
                $multiple = null;
                $single_upload = "true";
                if($field["multiple"])
                {
                    $multiple = "multiple";
                    $single_upload = "false";
                    $_SESSION["file_upload_fields"][$parameters["name"]][$field['name']] = true;
                }
                else
                {
                    $_SESSION["file_upload_fields"][$parameters["name"]][$field['name']] = false;
                }
                
                $description_field = "false";
                if($field["description_field"])
                {
                    $description_field = "true";
                }
                
                $url = "data-url=\"".\JarisCMS\URI\PrintURL("upload.php")."\"";

                $form .= "<div class=\"field\">";
                $form .= "<input {$field['code']} $readonly $multiple $url id=\"{$field['id']}\" class=\"form-{$field['type']}{$field['class']}\" type=\"{$field['type']}\" name=\"{$field['name']}\" value=\"{$field['value']}\" size=\"{$field['size']}\" />";
                $form .= "</div>\n";
                
                \JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.js");
                \JarisCMS\System\AddScript("scripts/fileupload/jquery.iframe-transport.js");
                \JarisCMS\System\AddScript("scripts/fileupload/jquery.fileupload.js");
                \JarisCMS\System\AddScript("scripts/fileupload/jquery.fileupload.wrapper.js");
                
                $scripts .= '
                <script>
                $(document).ready(function(){
                    $("#'.$field['id'].'").fileuploadwrapper({
                        showDescriptionField: '.$description_field.',
                        acceptFileTypes: "'.$field['valid_types'].'",
                        singleUpload: '.$single_upload.',
                        incorrectFileTypeMessage: "'.t("Incorrect file type selected. The type should be:").'"
                    });
                });
                </script>
                ';
            }
            elseif($field["type"] == "color")
            {
                \JarisCMS\System\AddScript("scripts/jscolor/jscolor.js");
                
                $readonly = null;
                if($field["readonly"])
                {
                    $readonly = "readonly=\"readonly\"";
                }

                $form .= "<div class=\"field\">";
                $form .= "<input {$field['code']} $readonly id=\"{$field['id']}\" class=\"form-{$field['type']}{$field['class']}\" type=\"text\" name=\"{$field['name']}\" value=\"{$field['value']}\" size=\"{$field['size']}\" />";
                $form .= "</div>\n";
                
                $scripts .= "<script type=\"text/javascript\">";
                $scripts .= "var color_picker = new jscolor.color(document.getElementById('{$field['id']}'), {});";
                $scripts .= "color_picker.fromString('{$field['value']}');";
                $scripts .= "</script>";
            }
            elseif($field["type"] == "uri")
            {
                \JarisCMS\System\AddScript("scripts/autocomplete/jquery.autocomplete.js");
                \JarisCMS\System\AddStyle("scripts/autocomplete/jquery.autocomplete.css");
                
                $readonly = null;
                if($field["readonly"])
                {
                    $readonly = "readonly=\"readonly\"";
                }

                $form .= "<div class=\"field\">";
                $form .= "<input {$field['code']} $readonly id=\"{$field['id']}\" class=\"form-text{$field['class']}\" type=\"text\" name=\"{$field['name']}\" value=\"{$field['value']}\" size=\"{$field['size']}\" />";
                $form .= "</div>\n";
                
                $scripts .= "<script>";
                $scripts .= "$(document).ready(function(){";
                $scripts .= "$('#{$field['id']}').autocomplete({";
                    $scripts .= "serviceUrl:'".\JarisCMS\URI\PrintURL("uris.php")."',";
                    $scripts .= "minChars:1,";
                    $scripts .= "maxHeight:400,";
                    $scripts .= "zIndex: 9999";
                    $scripts .= "});";
                $scripts .= "});";
                $scripts .= "</script>";
            }
            elseif($field["type"] == "uriarea")
            {
                \JarisCMS\System\AddScript("scripts/autocomplete/jquery.autocomplete.js");
                \JarisCMS\System\AddStyle("scripts/autocomplete/jquery.autocomplete.css");
                
                $readonly = null;
                if($field["readonly"])
                {
                    $readonly = "readonly=\"readonly\"";
                }

                $form .= "<div class=\"field\">\n";
                $form .= "<textarea $readonly {$field['code']} id=\"{$field['id']}\" class=\"form-textarea{$field['class']}\" name=\"{$field['name']}\" >\n";
                $form .= $field["value"];
                $form .= "</textarea>\n";
                $form .= "</div>\n";
                
                $scripts .= "<script>";
                $scripts .= "$(document).ready(function(){";
                $scripts .= "$('#{$field['id']}').autocomplete({";
                    $scripts .= "serviceUrl:'".\JarisCMS\URI\PrintURL("uris.php")."',";
                    $scripts .= "minChars:1,";
                    $scripts .= "delimiter: /(,|;)\s*/,";
                    $scripts .= "maxHeight:400,";
                    $scripts .= "zIndex: 9999";
                    $scripts .= "});";
                $scripts .= "});";
                $scripts .= "</script>";
            }
            elseif($field["type"] == "date")
            {
                \JarisCMS\System\AddScript("scripts/jdpicker/jquery.jdpicker.js");
                \JarisCMS\System\AddStyle("scripts/jdpicker/jdpicker.css");
                
                $readonly = null;
                if($field["readonly"])
                {
                    $readonly = "readonly=\"readonly\"";
                }

                $form .= "<div class=\"field\">";
                $form .= "<input {$field['code']} $readonly id=\"{$field['id']}\" class=\"form-{$field['type']}{$field['class']}\" type=\"text\" name=\"{$field['name']}\" value=\"{$field['value']}\" size=\"{$field['size']}\" />";
                $form .= "</div>\n";
                
                $date_format = "FF dd YYYY";
                
                if($field["format"])
                {
                    $date_format = $field["format"];
                }
                
                $scripts .= "<script type=\"text/javascript\">\n";
                $scripts .= "\$(document).ready(function(){\n";
                $scripts .= "$('#{$field['id']}').jdPicker({";
                $scripts .= "month_names: [\"" . t("January") . "\", \"" . t("February") . "\", \"" . t("March") . "\", \"" . t("April") . "\", \"" . t("May") . "\", \"" . t("June") . "\", \"" . t("July") . "\", \"" . t("August") . "\", \"" . t("September") . "\", \"" . t("October") . "\", \"" . t("November") . "\", \"" . t("December") . "\"],\n";
                $scripts .= "short_month_names: [\"" . t("Jan") . "\", \"" . t("Feb") . "\", \"" . t("Mar") . "\", \"" . t("Apr") . "\", \"" . t("May") . "\", \"" . t("Jun") . "\", \"" . t("Jul") . "\", \"" . t("Aug") . "\", \"" . t("Sep") . "\", \"" . t("Oct") . "\", \"" . t("Nov") . "\", \"" . t("Dec") . "\"],\n";
                $scripts .= "short_day_names: [\"" . t("SU") . "\", \"" . t("MO") . "\", \"" . t("TU") . "\", \"" . t("WE") . "\", \"" . t("TH") . "\", \"" . t("FR") . "\", \"" . t("SA") . "\"],\n";
                $scripts .= "error_out_of_range: \"" . t("Selected date is out of range") . "\",\n";
                $scripts .= "date_format: \"$date_format\"\n";
                $scripts .= "});";
                $scripts .= "});\n";
                $scripts .= "</script>\n";
            }
            elseif($field["type"] == "radio")
            {
                $form .= "<div class=\"field\">";
                foreach($field["value"] as $label=>$value)
                {
                    if($field["horizontal_list"])
                    {
                        $form .= "<div>";
                    }
                    
                    $checked = "";
                    if($field["checked"] == $value)
                    {
                        $checked = "checked=\"checked\"";
                    }
                    $value = htmlspecialchars($value);
                    $form .= "<input $checked id=\"$value\" class=\"form-{$field['type']}{$field['class']}\" type=\"{$field['type']}\" name=\"{$field['name']}\" value=\"$value\" /> ";
                    $form .= "<label for=\"$value\"><span>$label</span></label>\n";
                    
                    if($field["horizontal_list"])
                    {
                        $form .= "</div>\n";
                    }
                }
                $form .= "</div>\n";
            }
            elseif($field["type"] == "checkbox")
            {
                $form .= "<div class=\"field\">";
                if(is_array($field["value"]))
                {
                    foreach($field["value"] as $label=>$value)
                    {
                        if($field["horizontal_list"])
                        {
                            $form .= "<div>";
                        }

                        $checked = "";
                        if(is_array($field["checked"]))
                        {
                            if(in_array($value, $field["checked"]))
                            {
                                $checked = "checked=\"checked\"";
                            }
                        }
                        
                        $value = htmlspecialchars($value);
                        $form .= "<input $checked id=\"$value\" class=\"form-{$field['type']}{$field['class']}\" type=\"{$field['type']}\" name=\"{$field['name']}[]\" value=\"$value\" /> ";
                        $form .= "<label for=\"$value\"><span>$label</span></label>\n";

                        if($field["horizontal_list"])
                        {
                            $form .= "</div>\n";
                        }
                    }
                }
                else
                {
                    $checked = "";
                    if($field["checked"] == true)
                    {
                        $checked = "checked=\"checked\"";
                    }

                    $value = "";
                    if(trim($field["value"]) != "")
                    {
                        $value = "value=\"{$field['value']}\"";
                    }

                    $form .= "<label for=\"{$field['id']}\"><span>{$field['label']}</span></label> ";
                    $form .= "<input $checked $value id=\"{$field['id']}\" class=\"form-{$field['type']}{$field['class']}\" type=\"{$field['type']}\" name=\"{$field['name']}\" /> \n";
                }
                $form .= "</div>\n";
            }
            elseif($field["type"] == "select")
            {
                $multiple = "";
                if($field["multiple"])
                {
                    $multiple = "multiple=\"multiple\"";
                }

                $form .= "<div class=\"field\">\n";
                $form .= "<select {$field['code']} $multiple id=\"{$field['id']}\" class=\"form-{$field['type']}{$field['class']}\" name=\"{$field['name']}\" >\n";
                foreach($field["value"] as $label=>$value)
                {
                    //For compatibility with jaris realty
                    if($label == "optgroup")
                    {
                        foreach($value as $options)
                        {
                            $form .= "<optgroup label=\"{$options['label']}\">";

                            foreach($options["values"] as $option_label=>$option_value)
                            {
                                $selected = "";
                                if($field["selected"] == $option_value)
                                {
                                    $selected = "selected=\"selected\"";
                                }
                                $form .= "<option $selected value=\"$option_value\">$option_label</option>\n";
                            }
                            $form .= "</optgroup>";
                        }
                    }//Compatibility up to here
                    else
                    {
                        $selected = "";
                        if($field["multiple"] || is_array($field["selected"]))
                        {
                            if(is_array($field["selected"]))
                            {
                                foreach($field["selected"] as $selected_value)
                                {
                                    if("" . $selected_value . "" == "" . $value . "")
                                    {
                                        $selected = "selected=\"selected\"";
                                    }
                                }
                            }
                            else if("" . $field["selected"] . "" == "" . $value . "")
                            {
                                $selected = "selected=\"selected\"";
                            }
                        }
                        else if("" . $field["selected"] . "" == "" . $value . "")
                        {
                            $selected = "selected=\"selected\"";
                        }
                        $value = htmlspecialchars($value);
                        $form .= "<option $selected value=\"$value\">$label</option>\n";
                    }
                }
                $form .= "</select>\n";
                $form .= "</div>\n";
            }
            elseif($field["type"] == "textarea")
            {
                $readonly = null;
                if($field["readonly"])
                {
                    $readonly = "readonly=\"readonly\"";
                }
                

                $form .= "<div class=\"field\">\n";
                $form .= "<textarea $readonly {$field['code']} id=\"{$field['id']}\" class=\"form-{$field['type']}{$field['class']}\" name=\"{$field['name']}\" >\n";
                $form .= $field["value"];
                $form .= "</textarea>\n";
                $form .= "</div>\n";
                
                if($field["limit"])
                {
                    \JarisCMS\System\AddScript("scripts/optional/jquery.limit.js");
                    $field["description"] .= " <span class=\"form-chars-left\" id=\"{$field["id"]}-limit\">{$field['limit']}</span>&nbsp;" . "<span class=\"form-chars-left-label\">" . t("characters left") . "</span>";
                    $scripts .= "<script>$(\"#{$field["id"]}\").limit('{$field['limit']}', '#{$field["id"]}-limit')</script>"; 
                }
            }
            elseif($field["type"] == "other")
            {
                $form .= $field["html_code"];
            }
            elseif($field["type"] == "validate_sum")
            {
                $num1 = rand(1, 10);
                $num2 = rand(1, 20);
                $result = $num1 + $num2;
                
                $_SESSION["validation_fields"][$parameters["name"]][$field["name"]] = array("type"=>$field["type"], "name"=>$field["name"], "value"=>$result);
                
                $form .= "<div class=\"field\">";
                $form .= "<input {$field['code']} id=\"{$field['id']}\" class=\"form-{$field['class']}\" type=\"text\" name=\"{$field['name']}\" size=\"{$field['size']}\" />";
                $form .= "</div>\n";
                
                $field["description"] .= "<span class=\"form-validate-sum\" >" . t("Enter the sum of") . " <strong>$num1</strong> + <strong>$num2</strong></span>"; 
            }
            elseif($field["type"] == "submit" || $field["type"] == "reset")
            {
                $form .= "<input {$field['code']} id=\"{$field['name']}\" class=\"form-{$field['type']}{$field['class']}\" type=\"{$field['type']}\" name=\"{$field['name']}\" value=\"{$field['value']}\" size=\"{$field['size']}\" /> ";
            }

            //Print description of field
            if($field["description"])
            {
                $form .= "<div class=\"description\">\n";
                $form .= "<span>{$field['description']}</span>\n";
                $form .= "</div>\n";
            }
        }

        if($fieldset["name"])
        {
            if($fieldset["description"])
            {
                $form .= "<p class=\"fieldset-description\">{$fieldset['description']}</p>\n";
            }

            $form .= "</fieldset>\n";
        }
    }

    $form .= "</form>\n";
    
    $form .= $scripts;

    return $form;
}
?>
