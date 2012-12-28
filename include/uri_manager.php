<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions related to the translation of uri's to appropiate system's
 *      path and others.
 */

namespace JarisCMS\URI;

/**
 * Verifies the $_REQUEST['p'] used to change to diffrent pages
 * 
 * @return string home page if $_REQUEST['p'] is null or the $_REQUEST['p'] value.
 */
function Get()
{
    static $page;
    
    if($page == "")
    {
    	//Default home page.
    	$page = "home";
    	
    	//Try to get home page set on site settings
    	if($home_page = \JarisCMS\Setting\Get("home_page", "main"))
    	{
    		$page = $home_page;
    	}
    	
        if(isset($_REQUEST['p']))
        {
            if($_REQUEST['p'] != "")
            {
                $page = $_REQUEST['p'];
            }
        }
    }

    return $page;
}

/**
 * Checks an uri type.
 *
 * @param string $uri The uri to check for its type.
 *
 * @return string One of these values: page, user_picture, image, file, category.
 */
function GetType($uri)
{
	global $user_profiles;
	
	$sections = explode("/", $uri);

	if($sections[0] == "image" && $sections[1] == "user")
	{
		return "user_picture";
	}
	elseif($sections[0] == "image")
	{
		return "image";
	}
	elseif($sections[0] == "file")
	{
		return "file";
	}
    elseif($sections[0] == "category")
	{
		return "category";
	}
	elseif(count($sections) == 2 && $sections[0] == "user" && $user_profiles)
	{
		return "user_profile";
	}
	else
	{
		return "page";
	}
}

/**
 * Generates a path to the data of an uri.
 *
 * @param string $page The uri to convert to a valid data path.
 *
 * @return string The path to the uri data.
 */
function GetDataPath($page)
{
	$data_file = \JarisCMS\Page\GeneratePath($page) . "/data.php";

	return $data_file;
}

/**
 * Translates an image uri to its real data path.
 *
 * @param string $path The uri of the image to translate in form of image/page/imageid.
 *
 * @return string The full path to the image file or "" if not found.
 */
function GetImagePath($path)
{
	$data_file = \JarisCMS\Setting\GetDataDirectory() . "pages/";
	$sections = explode("/", $path);
	$image_id = $sections[count($sections) - 1];
	$sections_available = count($sections) - 2;

	if(count($sections) > 3)
	{
		$data_file .= "sections/";

		for($i=1; $i<$sections_available; ++$i)
		{
			$data_file .= $sections[$i] . "/";
		}

		$data_file .= substr($sections[$sections_available],0,1) . "/" .
		substr($sections[$sections_available],0,2) . "/" .
		$sections[$sections_available] . "/images.php";
	}
	else
	{
		$data_file .= "singles/";
		$data_file .= substr($sections[1],0,1) . "/" . substr($sections[1],0,2) . "/" . $sections[1] . "/images.php";
	}

	$images = \JarisCMS\PHPDB\Parse($data_file);

	//Search for the image id and return its path
	foreach($images as $row => $fields)
	{
        //Return by image name
		if(strcmp($image_id, trim($fields['name'])) == "0")
		{
			return str_replace("images.php", "images/" . trim($fields['name']), $data_file);
		}
        
        //Return by image id
		else if(strcmp($row, $image_id) == "0")
		{
			return str_replace("images.php", "images/" . trim($fields['name']), $data_file);
		}
	}

	//Image not found if the end was reached
	return "";
}

/**
 * Translates a file uri to some useful info.
 *
 * @param string $path The uri of the file to translate in form of file/page/filename_or_id.
 *
 * @return array List in the format array(path, id, page_uri)
 *         or null if not found.
 */
function GetFileInfo($path)
{
	$path = str_replace("file/", "", $path);

	$sections = explode("/", $path);
	$file_id = $sections[count($sections) - 1];
	unset($sections[count($sections) - 1]);
	$path = implode("/", $sections);

	$data_file = \JarisCMS\File\GeneratePath($path);

	$files = \JarisCMS\PHPDB\Parse($data_file);

	$file_path = "";
	//Search for the file id and return its path
	foreach($files as $row => $fields)
	{
		$found = false;

		if(strcmp($file_id, trim($fields['name'])) == "0")
		{
			$file_path = str_replace("files.php", "files/" . trim($fields['name']), $data_file);
			$found = true;
		}
		else if(strcmp($row, $file_id) == "0")
		{
			$file_path = str_replace("files.php", "files/" . trim($fields['name']), $data_file);
			$found = true;
		}

		if($found)
		{
			$file_array["path"] = $file_path;
			$file_array["id"] = $row;
			$file_array["page_uri"] = $path;

			return $file_array;
		}
	}

	//File not found if the end was reached
	return null;
}

/**
 * Translates a user picture uri to some useful info.
 *
 * @param string $path The uri of the user picture to translate in form of image/user/username.
 *
 * @return array Data in the format array(username, path)
 */
function GetAvatarInfo($path)
{
	$sections = explode("/", $path);

	$uri_data["username"] = $sections[2];
	$uri_data["path"] = \JarisCMS\User\GetAvatarPath($uri_data["username"]);

	return $uri_data;
}

/**
 * This functions print the correct url based on clean_url or simple ones, as check
 * if the uri paramenter is a full address like http://jegoyalu.com and just return it.
 *
 * @param string $uri The page address that we want to print of full http address.
 * @param array $arguments The variables that we are going to pass to the page in
 *                         the format variables["name"] = "value"
 *
 * @return string A formatted url.
 *         Example of clean url: mydomain.com/page?argument=value.
 *         Without clean url mydomain.com/?p=page&argument=value
 */
function PrintURL($uri, $arguments = null)
{
	global $base_url, $clean_urls;

	$url = "";

	if("" . strpos($uri, "http://") . "" != "" || "" . strpos($uri, "https://") . "" != "")
	{
		$url = $uri;
	}
	else if(file_exists($uri))
	{
		$url = $base_url . "/" . $uri;
        
        if(count($arguments) > 0)
		{
			$formated_arguments = "?";

			foreach($arguments as $argument=>$value)
			{
                if("" . $value . "" != "")
                {
				    $formated_arguments .= $argument . "=" . rawurlencode($value) . "&";
                }
			}

			$formated_arguments = rtrim($formated_arguments, "&");

			$url .= $formated_arguments;
		}
	}
	else
	{

		$url = "$base_url/";

		$url .= $clean_urls?$uri:"?p=$uri";

		if(count($arguments) > 0)
		{
			$formated_arguments = $clean_urls?"?":"&";

			foreach($arguments as $argument=>$value)
			{
                if("" . $value . "" != "")
                {
				    $formated_arguments .= $argument . "=" . rawurlencode($value) . "&";
                }
			}

			$formated_arguments = rtrim($formated_arguments, "&");

			$url .= $formated_arguments;
		}
	}

	return $url;
}

/**
 * Convertes any given string into a ready to use uri.
 *
 * @param string $string The string to convert to uri.
 * @param bool $allow_slashes If true, does not strip outs slashes (/).
 * 
 * @return string uri ready to use
 */
function FromText($string, $allow_slashes=true)
{   
    $uri = str_ireplace(
        array("á", "é", "í", "ó", "ú", "ä", "ë", "ï", "ö", "ü", "ñ",
        "Á", "É", "Í", "Ó", "Ú", "Ä", "Ë", "Ï", "Ö", "Ü", "Ñ"), 
        array("a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "n",
        "a", "e", "i", "o", "u", "a", "e", "i", "o", "u", "n"), 
        $string
    );
    
    $uri = trim($uri);
    
    $uri= strtolower($uri);
    
   // only take alphanumerical characters, but keep the spaces and dashes
	if(!$allow_slashes)
		$uri= preg_replace('/[^a-zA-Z0-9 -]/', '', $uri );
	
	// only take alphanumerical characters, but keep the spaces, dashes and slashes
	else
		$uri= preg_replace('/[^a-zA-Z0-9 -\/]/', '', $uri );
    
    $uri= str_replace(' ', '-', $uri);
    
    $uri = preg_replace('/([-]+)/', '-', $uri);
    
    return $uri;
}

/**
 * Generates an uri for a given content type
 * 
 * @param string $type Machine name of the type.
 * @param string $title The title of the content.
 * @param string $user The username of the user that is creating the content.
 * 
 * @return string Valid uri for system content creation.
 */
function GenerateForType($type, $title, $user)
{
    $type_data = \JarisCMS\Type\GetData($type);
    
    $type = FromText($type);
    $user = FromText($user);
    $title = FromText($title);
    
    if(!$type_data["uri_scheme"])
    {
        return $user . "/" . $type . "/" . $title;
    }
    
    $uri_scheme = $type_data["uri_scheme"];
    
    $uri_scheme = str_replace(array("{user}", "{type}", "{title}"), array($user, $type, $title), $uri_scheme);
    
    return $uri_scheme;
}
?>