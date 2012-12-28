<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file The functions to manage content input formats
 */

namespace JarisCMS\InputFormat;

/**
 * Adds a new content input format.
 *
 * @param string $name The machine readable name of the input format.
 * @param array $fields An array with the needed fields to write to the input format.
 *
 * @return string "true" string on success error message on fail.
 */
function Add($name, $fields)
{
	$input_format_data_path = GetPath($name);

	//Create input_formats directory in case is not present
	$path = str_replace("$name.php", "", $input_format_data_path);
	if(!file_exists($path))
	{
		\JarisCMS\FileSystem\MakeDir($path, 0755, true);
	}

	//Check if input format already exist.
	if(file_exists($input_format_data_path))
	{
		return \JarisCMS\System\GetErrorMessage("input_format_exist");
	}
    
    //Call Add hook before creating the category
	\JarisCMS\Module\Hook("InputFormat", "Add", $name, $fields);

	if(!\JarisCMS\PHPDB\Add($fields, $input_format_data_path))
	{
		return \JarisCMS\System\GetErrorMessage("write_error_data");
	}

	return "true";
}

/**
 * Deletes an existing content input format.
 *
 * @param string $name Machine name of the input format.
 *
 * @return bool "true" string on success error message on fail.
 */
function Delete($name)
{
	$input_format_data_path = GetPath($name);

	if(!unlink($input_format_data_path))
	{
		return \JarisCMS\System\GetErrorMessage("write_error_data");
	}

	return "true";
}

/**
 * Edits or changes the data of an existing input format.
 *
 * @param string $name The machine name of the input format.
 * @param array $fields Array with all the new values of the input format.
 *
 * @return bool True on success false or fail.
 */
function Edit($name, $fields)
{
	$input_format_data_path = GetPath($name);
    
    //Call Add hook before creating the category
	\JarisCMS\Module\Hook("InputFormat", "Edit", $name, $fields);

	return \JarisCMS\PHPDB\Edit(0, $fields, $input_format_data_path);
}

/**
 * Get an array with data of a specific content input format.
 *
 * @param string $name Machine name of the input format.
 *
 * @return array An array with all the fields of the input format.
 */
function GetData($name)
{
	$input_format_data_path = GetPath($name);

	$input_format = \JarisCMS\PHPDB\Parse($input_format_data_path);

	return $input_format[0];
}

/**
 * Gets the list of available content input formats.
 *
 * @return array Array with all input formats in the format input_format["machine name"] =
 *		  array(
 *			"name"=>"string",
 *			"description"=>"string",
 *          "parse_url"=> bool,
 *          "ParseLineBreaks"=>bool
 *		  )
 *        or null if no input format found.
 */
function GetList()
{
    if(!file_exists(\JarisCMS\Setting\GetDataDirectory() . "types/input_formats"))
    {
        \JarisCMS\FileSystem\MakeDir(\JarisCMS\Setting\GetDataDirectory() . "types/input_formats");
    }
    
	$dir = opendir(\JarisCMS\Setting\GetDataDirectory() . "types/input_formats");

	$input_formats = array();

    if(file_exists(\JarisCMS\Setting\GetDataDirectory() . "types/input_formats"))
    {
    	while(($file = readdir($dir)) !== false)
    	{
    		if($file != "." && $file != ".." && !is_dir(\JarisCMS\Setting\GetDataDirectory() . "types/input_formats/$file"))
    		{
    			$machine_name = str_replace(".php", "", $file);
                $input_formats[$machine_name] = GetData($machine_name);
    		}
    	}
    }

	closedir($dir);

	return $input_formats;
}

/**
 * Links parser.
 * 
 * @param string $text The input used to parse links.
 * 
 * @return string Text with links turned to html.
 */
function ParseLinks($text)
{
    $pattern = "/[^\"]https?:\/\/(\w*:\w*@)?[-\w.]+(:\d+)?(\/([\w\/_.]*(\?\S+)?)?)?[^\"]/";
    preg_match_all($pattern, $text, $matches);
    
    foreach($matches[0] as $match)
    {
        $match = trim($match);
        $html = "<a target=\"_blank\" href=\"$match\">$match</a>";
        $text = str_replace($match, $html, $text);
    }
    
    return $text;
}

/**
 * Emails parser.
 * 
 * @param string $text The input used to parse emails.
 * 
 * @return string Text with emails turned to html.
 */
function ParseEmails($text)
{
    $pattern = "/(\w+\.)*\w+@(\w+\.)+[A-Za-z]+/";
    preg_match_all($pattern, $text, $matches);
    
    foreach($matches[0] as $match)
    {
        $html = "<a href=\"mailto:$match\">$match</a>";
        $text = str_replace($match, $html, $text);
    }
    
    return $text;
}

/**
 * Line breaks parser.
 * 
 * @param string $text The input used to parse line breaks.
 * 
 * @return string Text with \n turned to <br />.
 */
function ParseLineBreaks($text)
{
    return nl2br($text);
}

/**
 * For retrieving all the system input formats to process data.
 *
 * @return array Array in the format $input_formats["machine_name"] = array("title", "description")
 */
function GetAll()
{
	$input_formats["full_html"] = array("title"=>t("Full HTML"), "description"=>t("Supports all html tags"));
 	$input_formats["php_code"] = array("title"=>t("PHP Code"), "description"=>t("For executing php code with no filtering."));
    
    $input_formats_array = GetList();
    
    foreach($input_formats_array as $machine_name=>$data)
    {
        $input_formats[$machine_name] = array("title"=>t($data["name"]), "description"=>t($data["description"]));
    }

 	return $input_formats;
}

/**
 * For filtering data given a specific input format.
 *
 * @param string $data The data to filter.
 * @param string $input_format The format in wich to filter the data, full_html or php_code.
 *
 * @return string The filtered data.
 */
function FilterData($data, $input_format)
{
    static $input_formats_array;
    
	switch($input_format)
	{
		case "full_html":
			return $data;
		case "php_code":
			return \JarisCMS\System\PHPEval($data);
		default:
            if(!$input_formats_array[$input_format])
            {
                $input_formats_array[$input_format] = GetData($input_format);
            }
            
            $data = \JarisCMS\Search\StripHTMLTags($data, $input_formats_array[$input_format]["allowed_tags"]);
            
            if($input_formats_array[$input_format]["parse_url"])
            {
                $data = ParseLinks($data);
            }
            
            if($input_formats_array[$input_format]["parse_email"])
            {
                $data = ParseEmails($data);
            }
            
            if($input_formats_array[$input_format]["ParseLineBreaks"])
            {
                $data = ParseLineBreaks($data);
            }
            
			return $data;
	}
}

/**
 * Generates the data path where content input format information resides.
 *
 * @param string $name The machine name of the content input format.
 * 
 * @return string Path to input format file.
 */
function GetPath($name)
{
    if(!file_exists(\JarisCMS\Setting\GetDataDirectory() . "types/input_formats"))
    {
        \JarisCMS\FileSystem\MakeDir(\JarisCMS\Setting\GetDataDirectory() . "types/input_formats");
    }
    
	$input_format_path = \JarisCMS\Setting\GetDataDirectory() . "types/input_formats/$name.php";

	return $input_format_path;
}
?>