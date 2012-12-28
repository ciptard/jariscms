<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file The functions to manage content types
 */

namespace JarisCMS\Type;

/**
 * Adds a new content type.
 *
 * @param string $name The machine readable name of the type.
 * @param array $fields An array with the needed fields to write to the type.
 *
 * @return string "true" string on success error message on fail.
 */
function Add($name, $fields)
{
	$type_data_path = GeneratePath($name);

	//Create page type directory in case is not present
	$path = str_replace("$name.php", "", $type_data_path);
	if(!file_exists($path))
	{
		\JarisCMS\FileSystem\MakeDir($path, 0755, true);
	}

	//Check if type already exist.
	if(file_exists($type_data_path))
	{
		return \JarisCMS\System\GetErrorMessage("type_exist");
	}
    
    //Call add type hook before creating the category
	\JarisCMS\Module\Hook("Type", "Add", $name, $fields);
    
    $fields["categories"] = serialize($fields["categories"]);
    $fields["uploads"] = serialize($fields["uploads"]);
	$fields["posts"] = serialize($fields["posts"]);

	if(!\JarisCMS\PHPDB\Add($fields, $type_data_path))
	{
		return \JarisCMS\System\GetErrorMessage("write_error_data");
	}

	return "true";
}

/**
 * Deletes an existing content type.
 *
 * @param string $name Machine name of the type.
 *
 * @return string "true" string on success error message on fail.
 */
function Delete($name)
{
	$type_data_path = GeneratePath($name);

	//Check that user is not deleting the systema type pages
	if($name == "pages")
	{
		return \JarisCMS\System\GetErrorMessage("delete_system_type");
	}

	if(!unlink($type_data_path))
	{
		return \JarisCMS\System\GetErrorMessage("write_error_data");
	}

	return "true";
}

/**
 * Edits or changes the data of an existing content type.
 *
 * @param string $name The machine name of the type.
 * @param array $fields Array with all the new values of the type.
 *
 * @return bool true on success false on fail.
 */
function Edit($name, $fields)
{
	$type_data_path = GeneratePath($name);
    
    //Call type edit hook before creating the category
	\JarisCMS\Module\Hook("Type", "Edit", $name, $fields);
    
    $fields["categories"] = serialize($fields["categories"]);
    $fields["uploads"] = serialize($fields["uploads"]);
	$fields["posts"] = serialize($fields["posts"]);

	return \JarisCMS\PHPDB\Edit(0, $fields, $type_data_path);
}

/**
 * Get an array with data of a specific content type.
 *
 * @param string $name Machine name of the type.
 *
 * @return array An array with all the fields of the type.
 */
function GetData($name)
{
	$type_data_path = GeneratePath($name);

	$type = \JarisCMS\PHPDB\Parse($type_data_path);
    
    $type[0]["categories"] = unserialize($type[0]["categories"]);
    $type[0]["uploads"] = unserialize($type[0]["uploads"]);
	$type[0]["posts"] = unserialize($type[0]["posts"]);

	return $type[0];
}

/**
 * Gets the list of available content types.
 * 
 * @param string $user_group Optional machine name of a group to only retrieves types
 * where it have permissions.
 * 
 * @param string $username Optional username to only retrieve types which max posts
 * hasnt been reached.
 *
 * @return array All types in the format types["machine name"] =
 *		  array(
 *			"name"=>"string",
 *			"description"=>"string"
 *		  )
 *        or null if no type found.
 */
function GetList($user_group=null, $username=false)
{
	$dir = opendir(\JarisCMS\Setting\GetDataDirectory() . "types");

	$types = null;

	while(($file = readdir($dir)) !== false)
	{
		if($file != "." && $file != ".." && !is_dir(\JarisCMS\Setting\GetDataDirectory() . "types/$file"))
		{
			$machine_name = str_replace(".php", "", $file);
            
            if($user_group)
            {
                if(\JarisCMS\Group\GetTypePermission($machine_name, $user_group, $username))
                {
                    $types[$machine_name] = GetData($machine_name);
                }
            }
            else
            {
                $types[$machine_name] = GetData($machine_name);
            }
            
		}
	}

	closedir($dir);

	return $types;
}

/**
 * To check if a user reached the maximum amount of posts 
 * for a given type.
 * 
 * @param type $type
 * @param type $username
 * 
 * @return bool True if user reached max post allowed false otherwise
 */
function UserReachedMaxPost($type, $username)
{	
	if(\JarisCMS\SQLite\DBExists("search_engine") && !\JarisCMS\Security\IsAdminLogged())
	{
		$type_data = GetData($type);
		$user_data = \JarisCMS\User\GetData($username);
	
		if($type_data["posts"][$user_data["group"]] > 0)
		{
			$db = \JarisCMS\SQLite\Open("search_engine");
			$result = \JarisCMS\SQLite\Query("select count(uri) as total_posts from uris where author='$username' and type='$type'", $db);
			$data = \JarisCMS\SQLite\FetchArray($result);
			\JarisCMS\SQLite\Close($db);

			if($data["total_posts"] >= $type_data["posts"][$user_data["group"]])
			{
				return true;
			}
		}
	}
	
	return false;
}

/**
 * Generates array of checkbox form fields for each category.
 *
 * @param array $selected The array of selected categories.
 * 
 * @return array A series of fields that can
 * 		  be used when generating a form.
 */
function GenerateCategoryFieldList($selected=null)
{
	$fields = array();
	
	$categories_list = \JarisCMS\Category\GetList();
	
	foreach($categories_list as $machine_name=>$category_data)
	{
		$checked = false;
		if($selected)
		{
			foreach($selected as $value)
			{
				if($value == $machine_name)
				{
					$checked = true;
					break;
				}
			}
		}
		
		$fields[] = array("type"=>"checkbox", "checked"=>$checked, "label"=>t($category_data["name"]), "name"=>"categories[]", "id"=>"types", "description"=>t($category_data["description"]), "value"=>$machine_name);
        $fields[] = array("type"=>"other", "html_code"=>"<br />");
	}
	
	return $fields;
}

/**
 * Generates array of checkbox form fields for each content type.
 *
 * @param array $selected The array of selected types.
 * 
 * @return array A series of fields that can
 * 		   be used when generating a form.
 */
function GenerateContentFieldList($selected=null)
{
	$fields = array();
	
	$types_list = GetList();
	
	foreach($types_list as $machine_name=>$type_data)
	{
		$checked = false;
		if($selected)
		{
			foreach($selected as $value)
			{
				if($value == $machine_name)
				{
					$checked = true;
					break;
				}
			}
		}
		
		$fields[] = array("type"=>"checkbox", "checked"=>$checked, "label"=>t($type_data["name"]), "name"=>"types[]", "id"=>"types", "description"=>t($type_data["description"]), "value"=>$machine_name);
        $fields[] = array("type"=>"other", "html_code"=>"<br />");
	}
	
	return $fields;
}

/**
 * Get a type default input format
 * 
 * @param string $name The machine name of the type.
 * 
 * @return string The type default input format or full_html if no input format assigned.
 */
function GetDefaultInputFormat($name)
{
    $type = GetData($name);
    
    if(!$type["input_format"])
    {
        return "full_html";
    }
    
    return $type["input_format"];
}

/**
 * Function to retrieve the title or content labels and descriptions.
 *
 * @param string $type The machine name of the type.
 * @param string $label One of the following values: 
 * title_label, title_description, content_label, content_description
 *
 * @return string The corresponding label or description value already translated.
 */
function GetLabel($type, $label)
{
    $type_data = GetData($type);
    
    if(trim($type_data[$label]) != "")
    {
        return t($type_data[$label]);
    }
    
    switch($label)
    {
        case "title_label":
            return t("Title:");
        case "title_description":
            return t("Displayed on the web browser title bar and inside the website.");
        case "content_label":
            return t("Content:");
        case "content_description":
            return "";
    }
    
    return "";
}

/**
 * Generates the data path where content type information resides.
 *
 * @param $name The machine name of the content type.
 * 
 * @return string $name The path of the type file.
 */
function GeneratePath($name)
{
	$type_path = \JarisCMS\Setting\GetDataDirectory() . "types/$name.php";

	return $type_path;
}
?>