<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file The functions to manage content types extra fields.
 */

namespace JarisCMS\Field;

/**
 * Adds a new custom field to a content type.
 * 
 * @param array $fields An array with the values of the field.
 * @param string $type The machine name of the type.
 *
 * @return bool True on success or false on failure.
 */
function AddType($fields, $type)
{
    if($type == "")
    {
        return false;
    }
    
    $path = \JarisCMS\Setting\GetDataDirectory() . "types/fields/$type.php";
    
    //Create directory of fields in case does'nt exist
    if(!file_exists(\JarisCMS\Setting\GetDataDirectory() . "types/fields"))
    {
        \JarisCMS\FileSystem\MakeDir(\JarisCMS\Setting\GetDataDirectory() . "types/fields", 0755, true);
    }
    
    return \JarisCMS\PHPDB\Add($fields, $path);
}

/**
 * Edits a custom field of a content type.
 * 
 * @param integer $id The id of the field.
 * @param array $fields An array with the new values of the field.
 * @param string $type The machine name of the type.
 *
 * @return bool True on success or false on failure.
 */
function EditType($id, $fields, $type)
{    
    if($id == "" && $type == "")
    {
        return false;
    }
    
    $path = GeneratePathFromType($type);
    
    if(!$path)
    {
        return false;    
    }
    
    return \JarisCMS\PHPDB\Edit($id, $fields, $path);
}

/**
 * Deletes a custom field from a content type.
 * 
 * @param integer $id The id of the field.
 * @param string $type The machine name of the type.
 *
 * @return bool True on success or false on failure.
 */
function DeleteType($id, $type)
{
    if($id == "" && $type == "")
    {
        return false;
    }
    
    $path = GeneratePathFromType($type);
    
    if(!$path)
    {
        return false;    
    }
    
    return \JarisCMS\PHPDB\Delete($id, $path);
}

/**
 * Retreive the corrosponding data of a field.
 * 
 * @param integer $id The id of the field.
 * @param string $type The machine name of the type.
 *
 * @return bool True on success or false on failure.
 */
function GetTypeData($id, $type)
{
    if($id == "" && $type == "")
    {
        return false;
    }
    
    $fields = GetFieldsFromType($type);
    
    if(!$fields)
    {
        return false;
    }
    
    return $fields[$id];
}

/**
 * Gets a list of all the custom fields available for a content type.
 * 
 * @param string $type The machine name of the type.
 *
 * @return bool True on success or false on failure.
 */
function GetFieldsFromType($type)
{
    if($type == "")
    {
        return false;
    }
    
    $path = GeneratePathFromType($type);
    
    if(!$path)
    {
        return false;    
    }
    
    $fields = \JarisCMS\PHPDB\Parse($path);
    
    $fields = \JarisCMS\PHPDB\Sort($fields, "position");
        
    return $fields;
}

/**
 * Used to append extra custom fields when submiting content on a content type.
 * 
 * @param string $type The machine name of the type.
 * @param array $current_fields A reference to the variable that holds default data to append custom fields.
 */
function AppendFieldsToType($type, &$current_fields)
{
    $fields = GetFieldsFromType($type);
    
    if($fields)
    {
        foreach($fields as $id=>$field)
        {
            //Skip file uploads since they are handled seperately
            if($field["type"] == "file" || $field["type"] == "image")
                continue;
            
            $value = $_REQUEST[$field["variable_name"]];
            
            if($field["strip_html"])
            {
                $value = \JarisCMS\Search\StripHTMLTags($value);
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
 * Check if file uploads for a given content type are of allowed extensions.
 * 
 * @param string $type The machine name of the type.
 * 
 * @return boolean
 */
function CheckUploadsFromType($type)
{
    $fields = GetFieldsFromType($type);
    
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
                    \JarisCMS\System\AddMessage(t("File size exceeded by") . " " . t($field_data["name"]) . ". " . t("Maximum size permitted is:") . " " . intval($field_data["size"]) . "K", "error");
                    
                    $pass = false;
                    
                    continue;
                }
            }
            
            $file_name = $_FILES[$field_data["variable_name"]]["name"];
            $file_name_parts = explode(".", $file_name);
            $file_extension = trim($file_name_parts[count($file_name_parts)-1]);
            
            $extensions = explode(",", $field_data["extensions"]);
            
            $valid_extension = false;
            
            foreach($extensions as $extension)
            {
                if(trim($extension) == $file_extension)
                {
                    $valid_extension = true;
                    break;
                }
            }
            
            if(!$valid_extension)
            {
                \JarisCMS\System\AddMessage(t("Incorrect file type uploaded for") . " " . t($field_data["name"]) . ". " . t("Supported file formats are:") . " " . $field_data["extensions"], "error");
                $pass = false;
            }
        }
        elseif($field_data["type"] == "image")
        {
            //Skip images not uploaded and not required
            if(trim($_FILES[$field_data["variable_name"]]["name"]) == "" && !$field_data["required"])
                continue;
            
            $image_info = getimagesize($_FILES[$field_data["variable_name"]]["tmp_name"]);
            
            switch($image_info["mime"])
            {
                case "image/jpeg":
                    break;
                case "image/png":
                    break;
                case "image/gif":
                    break;
                default:
                    \JarisCMS\System\AddMessage(t("Incorrect image type uploaded for") . " " . t($field_data["name"]) . ". " . t("Supported image formats are: jpeg, png and gif"), "error");
                    $pass = false;
            }
            
            if(!$pass)
                continue;

            //Resize image if needed
            if($field_data["width"] > 0)
            {
                if($image_info[0] > $field_data["width"])
                {
                    $image = \JarisCMS\Image\Get($_FILES[$field_data["variable_name"]]["tmp_name"], $field_data["width"]);
                    
                    $image_quality = \JarisCMS\Setting\Get("image_compression_quality", "main");
                    
                    switch($image_info["mime"])
                    {
                        case "image/jpeg":
                            imagejpeg($image["binary_data"], $_FILES["image"]["tmp_name"], $image_quality);
                            break;
                        case "image/png":
                            imagepng($image["binary_data"], $_FILES["image"]["tmp_name"]);
                            break;
                        case "image/gif":
                            imagegif($image["binary_data"], $_FILES["image"]["tmp_name"]);
                            break;
                    }
                }
            }
        }
    }
    
    return $pass;
}

function SaveUploadsFromType($type, $page)
{
    $fields = GetFieldsFromType($type);
    
    $page_data = \JarisCMS\Page\GetData($page);
    
    $files = \JarisCMS\File\GetList($page);
    $images = \JarisCMS\Image\GetList($page);
    
    foreach($fields as $id=>$field_data)
    {
        if($field_data["type"] == "file")
        {
            //Skip files not uploaded and not required
            if(trim($_FILES[$field_data["variable_name"]]["name"]) == "" && !$field_data["required"])
                continue;
            
            //Delete previous file
            if(trim($page_data[$field_data["variable_name"]]) != "")
            {
                foreach($files as $file_id=>$file_data)
                {
                    if($file_data["name"] == $page_data[$field_data["variable_name"]])
                    {
                        \JarisCMS\File\Delete($file_id, $page);
                        unset($files[$id]);

                        break;
                    }
                }
            }
            
            $file_name = "";
            
            \JarisCMS\File\Add($_FILES[$field_data["variable_name"]], "", $page, $file_name);
            
            $page_data[$field_data["variable_name"]] = $file_name;
        }
        elseif($field_data["type"] == "image")
        {
            //Skip images not uploaded and not required
            if(trim($_FILES[$field_data["variable_name"]]["name"]) == "" && !$field_data["required"])
                continue;
            
            //Delete previous image
            if(trim($page_data[$field_data["variable_name"]]) != "")
            {
                foreach($images as $image_id=>$image_data)
                {
                    if($image_data["name"] == $page_data[$field_data["variable_name"]])
                    {
                        \JarisCMS\Image\Delete($image_id, $page);
                        unset($images[$id]);

                        break;
                    }
                }
            }
            
            //Store image
            $file_name = "";
            
            \JarisCMS\Image\Add($_FILES[$field_data["variable_name"]], "", $page, $file_name);
            
            $page_data[$field_data["variable_name"]] = $file_name;
        }
    }
    
    \JarisCMS\Page\Edit($page, $page_data);
}

/**
 * Generates an array with the custom fields of a type for the \JarisCMS\Form\Generate function.
 * 
 * @param string $type The machine name of the type.
 * @param array $values Array of the values in the format $values["variable_name"] = value.
 */
function GenerateArrayFromType($type, $values=array(), $mode="add")
{
    if($type == "")
    {
        return false;
    }
    
    $fields = GetFieldsFromType($type);
    
    if(!$fields)
    {
        return false;
    }
    
    $form_fields = array();
    
    foreach($fields as $id=>$field)
    {
        if($field["type"] == "text" || $field["type"] == "password" || $field["type"] == "textarea" || $field["type"] == "uri" || $field["type"] == "uriarea")
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
            
            $form_fields[] = array("type"=>$field["type"], "name"=>$field["variable_name"], "label"=>t($field["name"]) . ":", "id"=>$field["variable_name"], "required"=>$field["required"], "readonly"=>$field["readonly"], "description"=>t($field["description"]) . $description);
            
            if($values[$field["variable_name"]] != "")
            {
                $form_fields[] = array("type"=>"other", "html_code"=>"<div class=\"current-file\"><strong>".t("Current file:")."</strong> <a href=\"".\JarisCMS\URI\PrintURL("file/{$_REQUEST["uri"]}/{$values[$field["variable_name"]]}")."\">{$values[$field["variable_name"]]}</a></div>");
            }
        }
        elseif($field["type"] == "image")
        {
            $description = "";
            if(trim($field["description"]) != "")
            {
                //To add a space after user entered description
                $description .= " ";
            }
            
            $description .= t("Allowed image types: jpeg, png, gif");
            
            $form_fields[] = array("type"=>"file", "name"=>$field["variable_name"], "label"=>t($field["name"]) . ":", "id"=>$field["variable_name"], "required"=>$field["required"], "readonly"=>$field["readonly"], "description"=>t($field["description"]) . $description);
            
            if(trim($values[$field["variable_name"]]) != "")
            {
                $form_fields[] = array("type"=>"other", "html_code"=>"<div class=\"current-image\"><strong>".t("Current image:")."</strong> <div><a href=\"".\JarisCMS\URI\PrintURL("image/{$_REQUEST["uri"]}/{$values[$field["variable_name"]]}")."\"><img src=\"".\JarisCMS\URI\PrintURL("image/{$_REQUEST["uri"]}/{$values[$field["variable_name"]]}", array("w"=>250))."\" /></a></div></div>");
            }
        }
        elseif($field["type"] == "hidden")
        {            
            $form_fields[] = array("type"=>$field["type"], "value"=>$_REQUEST[$field["variable_name"]]?$_REQUEST[$field["variable_name"]]:($values[$field["variable_name"]]?$values[$field["variable_name"]]:$field["default"]), "name"=>$field["variable_name"], "required"=>$field["required"], "readonly"=>$field["readonly"],);
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
                $form_fields[] = array("type"=>$field["type"], "value"=>$select, "checked"=>$_REQUEST[$field["variable_name"]]?$_REQUEST[$field["variable_name"]]:($values[$field["variable_name"]]?$values[$field["variable_name"]]:$field["default"]), "name"=>$field["variable_name"], "label"=>t($field["name"]) . ":", "id"=>$field["variable_name"], "required"=>false, "description"=>t($field["description"]));
            }
        }
        elseif($field["type"] == "other")
        {
            $form_fields[] = array("type"=>$field["type"], "html_code"=>\JarisCMS\System\PHPEval($field["default"]));
        }
    }
    
    return $form_fields;    
    
}

/**
 * Generates the path where a content type fields are stored.
 *
 * @param $type The machine name of the content type.
 * 
 * @return bool True on success or false if no fields exist.
 */
function GeneratePathFromType($type)
{
    if($type == "")
    {
        return false;
    }
    
    $path = \JarisCMS\Setting\GetDataDirectory() . "types/fields/$type.php";
    
    if(!file_exists($path))
    {    
        return false;
    }
    
    return $path;
}
?>
