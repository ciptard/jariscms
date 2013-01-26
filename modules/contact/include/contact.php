<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file The functions to manage contact form fields.
 */

namespace JarisCMS\Module\ContactForms;

use JarisCMS\Page;
use JarisCMS\PHPDB;
use JarisCMS\Search;
use JarisCMS\System;

/**
 *Adds a new custom field to a contact form.
 *
 *@param $fields An array with the values of the field.
 *@param $uri The path of the contact form page.
 *
 *@return True on success or false on failure.
 */
function AddField($field, $uri)
{
    if(trim($uri) == "")
    {
        return false;
    }

    $path = Page\GeneratePath($uri) . "/contact-fields.php";

    return PHPDB\Add($field, $path);
}

/**
 *Edits a field of a contact form page.
 *
 *@param $id The id of the field.
 *@param $field An array with the new values of the field.
 *@param $uri The contact form page path.
 *
 *@return True on success or false on failure.
 */
function EditField($id, $field, $uri)
{
    if(trim($id) == "" && trim($uri) == "")
    {
        return false;
    }

    $path = GenerateFieldsPath($uri);

    if(!$path)
    {
        return false;
    }

    return PHPDB\Edit($id, $field, $path);
}

/**
 *Deletes a field from a contact form.
 *
 *@param $id The id of the field.
 *@param $uri The contact forms page path.
 *
 *@return True on success or false on failure.
 */
function DeleteField($id, $uri)
{
    if(trim($id) == "" && trim($uri) == "")
    {
        return false;
    }

    $path = GenerateFieldsPath($uri);

    if(!$path)
    {
        return false;
    }

    return PHPDB\Delete($id, $path);
}

/**
 *Retreive the corrosponding data of a field.
 *
 *@param $id The id of the field.
 *@param $uri The contact forms page path.
 *
 *@return True on success or false on failure.
 */
function GetFieldData($id, $uri)
{
    if(trim($id) == "" && trim($uri) == "")
    {
        return false;
    }

    $fields = GetFields($uri);

    if(!$fields)
    {
        return false;
    }

    return $fields[$id];
}

/**
 *Gets a list of all the fields available for a contact form page.
 *
 *@param $uri The contact form page path.
 *
 *@return True on success or false on failure.
 */
function GetFields($uri)
{
    if(trim($uri) == "")
    {
        return false;
    }

    $path = GenerateFieldsPath($uri);

    if(!$path)
    {
        return false;
    }

    $fields = PHPDB\Parse($path);

    $fields = PHPDB\Sort($fields, "position");

    return $fields;
}

/**
 *Used to append fields to a contact form page.
 *
 *@param $uri The contact form page path.
 *@param $current_fields a reference to the variable that holds default data to append custom fields.
 */
function AppendFields($uri, &$current_fields)
{
    $fields = GetFields($uri);

    if($fields)
    {
        foreach($fields as $id=>$field)
        {
            //Skip file uploads since they are handled seperately
            if($field["type"] == "file")
                continue;

            $value = "";

            //Concatenate values for multiple checkbox
            if(is_array($_REQUEST[$field["variable_name"]]))
            {
                foreach($_REQUEST[$field["variable_name"]] as $option)
                {
                    $value .= $option . ", ";
                }

                $value = rtrim($value, ",");
            }
            else
            {
                $value .= $_REQUEST[$field["variable_name"]];
            }

            if($field["strip_html"])
            {
                $value = Search\StripHTMLTags($value);
            }

            if($field["limit"] > 0)
            {
                $value = substr($value, 0, $field["limit"]);
            }

            $current_fields[$field["variable_name"]] = $value;
        }
    }
}

/**
 * Check if file uploads sent as attachments are of allowed extensions and file size.
 *
 * @param string $uri The uri of the contact form page.
 *
 * @return boolean
 */
function AttachmentsValid($uri)
{
    $fields = GetFields($uri);

    $pass = true;

    foreach($fields as $id=>$field_data)
    {
        if($field_data["type"] == "file")
        {
            //Skip files not uploaded and not required
            if(trim($_FILES[$field_data["variable_name"]]["name"]) == "" && !$field_data["required"])
                continue;

            //Check file size didnt exceeded the maximum allowed
            if($field_data["size"] > 0)
            {
                if((filesize($_FILES[$field_data["variable_name"]]["tmp_name"])/1024) > (intval($field_data["size"])+1))
                {
                    System\AddMessage(t("File size exceeded by") . " " . t($field_data["name"]) . ". " . t("Maximum size permitted is:") . " " . intval($field_data["size"]) . "K", "error");

                    $pass = false;

                    continue;
                }
            }

            $file_name = $_FILES[$field_data["variable_name"]]["name"];
            $file_name_parts = explode(".", $file_name);
            $file_extension = trim($file_name_parts[count($file_name_parts)-1]);

            $valid_extension = false;

            if(trim($field_data["extensions"]) != "")
            {
                $extensions = explode(",", $field_data["extensions"]);

                foreach($extensions as $extension)
                {
                    if(trim($extension) == $file_extension)
                    {
                        $valid_extension = true;
                        break;
                    }
                }
            }
            else
            {
                $valid_extension = true;
            }

            if(!$valid_extension)
            {
                System\AddMessage(t("Incorrect file type uploaded for") . " " . t($field_data["name"]) . ". " . t("Supported file formats are:") . " " . $field_data["extensions"], "error");
                $pass = false;
            }
        }
    }

    return $pass;
}

/**
 * Gets array of files that are going to be sent as attachments.
 *
 * @param string $uri Uri of the contact form page.
 *
 * @return array Array ready to use by Email\Send on the format array("file name"=>"file path")
 */
function GetAttachments($uri)
{
    $fields = GetFields($uri);

    $attachments = array();

    foreach($fields as $id=>$field_data)
    {
        if($field_data["type"] == "file")
        {
            //Skip files not uploaded and not required
            if(trim($_FILES[$field_data["variable_name"]]["name"]) == "" && !$field_data["required"])
                continue;

            $attachments[$_FILES[$field_data["variable_name"]]["name"]] = $_FILES[$field_data["variable_name"]]["tmp_name"];
        }
    }

    return $attachments;
}

/**
 *Generates an array with the fields of a contact form for the JarisCMS\Form\Generate function..
 *
 *@param $uri The machine name of the $uri.
 *@param $values Array of the values in the format $values["variable_name"] = value.
 */
function GenerateFormFields($uri, $values=array())
{
    if(trim($uri) == "")
    {
        return false;
    }

    $fields = GetFields($uri);

    if(!$fields)
    {
        return false;
    }

    $form_fields = array();

    foreach($fields as $id=>$field)
    {
        if($field["type"] == "text" || $field["type"] == "password" || $field["type"] == "textarea")
        {
            if($field["limit"] > 0)
            {
                $form_fields[] = array("type"=>$field["type"], "limit"=>$field["limit"], "value"=>$_REQUEST[$field["variable_name"]]?$_REQUEST[$field["variable_name"]]:($values[$field["variable_name"]]?$values[$field["variable_name"]]:$field["default"]), "name"=>$field["variable_name"], "label"=>t($field["name"]) . ":", "id"=>$field["variable_name"], "required"=>$field["required"], "readonly"=>$field["readonly"], "description"=>t($field["description"]));
            }
            else
            {
                $form_fields[] = array("type"=>$field["type"], "value"=>$_REQUEST[$field["variable_name"]]?$_REQUEST[$field["variable_name"]]:($values[$field["variable_name"]]?$values[$field["variable_name"]]:$field["default"]), "name"=>$field["variable_name"], "label"=>t($field["name"]) . ":", "id"=>$field["variable_name"], "required"=>$field["required"], "readonly"=>$field["readonly"], "description"=>t($field["description"]));
            }
        }
        elseif($field["type"] == "color" || $field["type"] == "date")
        {
            $form_fields[] = array("type"=>$field["type"], "value"=>$_REQUEST[$field["variable_name"]]?$_REQUEST[$field["variable_name"]]:($values[$field["variable_name"]]?$values[$field["variable_name"]]:$field["default"]), "name"=>$field["variable_name"], "label"=>t($field["name"]) . ":", "id"=>$field["variable_name"], "required"=>$field["required"], "readonly"=>$field["readonly"], "description"=>t($field["description"]));
        }
        elseif($field["type"] == "file")
        {
            $description = "";
            if(trim($field["description"]) != "")
            {
                //To add a space after user entered description
                $description .= " ";
            }

            $description .= t("Allowed file types:") . " ";

            if(trim($field["extensions"]) != "")
            {
                $description .= $field["extensions"];
            }
            else
            {
                //If no extension was entered by the user just display all
                $description .= t("all");
            }

            $description .= " ";
            $description .= t("Maximum allowed size is:") . " ";

            if($field["size"] > 0)
            {
                $description .= intval($field["size"]) . "K";
            }
            else
            {
                $description .= ini_get("upload_max_filesize");
            }

            $form_fields[] = array("type"=>$field["type"], "valid_types"=>trim($field['extensions']), "name"=>$field["variable_name"], "label"=>t($field["name"]) . ":", "id"=>$field["variable_name"], "required"=>$field["required"], "readonly"=>$field["readonly"], "description"=>t($field["description"]) . $description);
        }
        elseif($field["type"] == "hidden")
        {
            $form_fields[] = array("type"=>$field["type"], "value"=>$_REQUEST[$field["variable_name"]]?$_REQUEST[$field["variable_name"]]:($values[$field["variable_name"]]?$values[$field["variable_name"]]:$field["default"]), "name"=>$field["variable_name"], "required"=>$field["required"], "readonly"=>$field["readonly"]);
        }
        elseif($field["type"] == "select")
        {
            $select = array();

            $select_values = explode(",", $field["values"]);
            $select_captions = explode(",", $field["captions"]);


            for($i=0; $i<count($select_values); $i++)
            {
                $select[trim(t($select_captions[$i]))] = trim($select_values[$i]);
            }

            if(count($select) > 0)
            {
                $form_fields[] = array("type"=>$field["type"], "value"=>$select, "selected"=>$_REQUEST[$field["variable_name"]]?$_REQUEST[$field["variable_name"]]:($values[$field["variable_name"]]?$values[$field["variable_name"]]:$field["default"]), "name"=>$field["variable_name"], "label"=>t($field["name"]) . ":", "id"=>$field["variable_name"], "required"=>$field["required"], "readonly"=>$field["readonly"], "description"=>t($field["description"]));
            }
        }
        elseif($field["type"] == "radio")
        {
            $select = array();

            $select_values = explode(",", $field["values"]);
            $select_captions = explode(",", $field["captions"]);


            for($i=0; $i<count($select_values); $i++)
            {
                $select[trim(t($select_captions[$i]))] = trim($select_values[$i]);
            }

            if(count($select) > 0)
            {
                $form_fields[] = array("type"=>$field["type"], "value"=>$select, "checked"=>$_REQUEST[$field["variable_name"]]?$_REQUEST[$field["variable_name"]]:($values[$field["variable_name"]]?$values[$field["variable_name"]]:$field["default"]), "name"=>$field["variable_name"], "label"=>t($field["name"]) . ":", "id"=>$field["variable_name"], "required"=>$field["required"], "readonly"=>$field["readonly"], "description"=>t($field["description"]));
            }
        }
        elseif($field["type"] == "checkbox")
        {
            $select = array();

            $select_values = explode(",", $field["values"]);
            $select_captions = explode(",", $field["captions"]);


            for($i=0; $i<count($select_values); $i++)
            {
                $select[trim(t($select_captions[$i]))] = trim($select_values[$i]);
            }

            if(count($select) > 0)
            {
                $form_fields[] = array("type"=>$field["type"], "value"=>$select, "checked"=>$_REQUEST[$field["variable_name"]]?$_REQUEST[$field["variable_name"]]:($values[$field["variable_name"]]?$values[$field["variable_name"]]:$field["default"]), "name"=>$field["variable_name"], "label"=>t($field["name"]) . ":", "id"=>$field["variable_name"], "required"=>$field["required"], "readonly"=>$field["readonly"], "description"=>t($field["description"]));
            }
        }
        elseif($field["type"] == "other")
        {
            $form_fields[] = array("type"=>$field["type"], "html_code"=>System\PHPEval($field["default"]));
        }
    }

    return $form_fields;

}

/**
 *Generates the path where a contact form fields are stored.
 *
 *@param $uri The path to the contact form page.
 *
 *@return True on success or false if no fields exist.
 */
function GenerateFieldsPath($uri)
{
    if(trim($uri) == "")
    {
        return false;
    }

    $path = Page\GeneratePath($uri) . "/contact-fields.php";

    if(!file_exists($path))
    {
        return false;
    }

    return $path;
}
?>