<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions to get images from pages, since there location is not accessed
 *      directly as resize them, etc.
 */

namespace JarisCMS\Image;

/**
 * Prepares and print an image file to a browser.
 *
 * @param string $image_path The full path to the image to display.
 */
function Show($image_path)
{
	$uri = str_replace("image/", "", \JarisCMS\URI\Get());
	$uri = explode("/", $uri);
	unset($uri[count($uri)-1]);
	$uri = implode("/", $uri);
	
	$page_data = \JarisCMS\Page\GetData($uri);
	
	if(\JarisCMS\Page\UserAccess($page_data))
	{
		//Try to get image from cache
		$cache_name = GetCachePath($image_path);
		if(file_exists($cache_name))
		{
			PrintFromCache($cache_name);
		}
		//else process image and store it to cache
		else
		{
			$image = Get($image_path, $_REQUEST['w'], $_REQUEST['h'], $_REQUEST["ar"], $_REQUEST["bg"]);
			Display($image);
		}
	}
	else 
	{
		\JarisCMS\Security\ProtectPage();
	}
}

/**
 * Gets the cache path of an image.
 * 
 * @param string $original_image The path of the original image.
 * 
 * @return string Path to image cache or original if not found.
 */
function GetCachePath($original_image=null)
{
	global $page;

	if(isset($_REQUEST["w"]))
	{
		$size = "-" . $_REQUEST["w"];
	}

	if(isset($_REQUEST["h"]))
	{
		$size .= "x" . $_REQUEST["h"];
	}
    
    if(isset($_REQUEST["ar"]) && $_REQUEST["ar"] == "1")
    {
        $size .= "-ar"; 
    }
    
    if(isset($_REQUEST["bg"]) && $_REQUEST["bg"] != "")
    {
        $size .= "-" . $_REQUEST["bg"];
    }

    //Return resized image path in cache
	if($size)
	{
		$image_page_uri = str_replace("/", "-", $page);

		$cache_path = \JarisCMS\Setting\GetDataDirectory() . "image-cache/$image_page_uri$size";
	}
    
    //Returns original image path
	else
	{
		$cache_path = $original_image;
	}

	return $cache_path;
}

/**
 * Gets an imaga binary data to work with it.
 *
 * @param string $path The path of the image to work on.
 * @param integer $width The width in which the image will be displayed.
 * @param integer $hieght The height in which the image will be displayed.
 * @param bool $aspect_ratio Flag to keep the original aspect ratio.
 * @param string $background_color Hex color value for the background of the image.
 *
 * @return array Image mime type and image in form of binary data.
 */
function Get($path, $width, $height=0, $aspect_ratio=false, $background_color="ffffff")
{
	$image_info = getimagesize($path);

	switch($image_info['mime'])
	{
		case "image/jpeg":
			$original_image = imagecreatefromjpeg($path);
			break;
		case "image/png":
			$original_image = imagecreatefrompng($path);
			break;
		case "image/gif":
			$original_image = imagecreatefromgif($path);
			break;
	}

	$image_data["mime"] = $image_info["mime"];
	$image_data["path"] = $path;
    
    if($width > 0 && $height > 0 && $aspect_ratio && $background_color)
	{
		$image_data["binary_data"] = imagecreatetruecolor($width, $height);
        
        $rgb_array = HTMLHexToRGB($background_color);
        $bg_color = imagecolorallocate($image_data["binary_data"], $rgb_array["r"], $rgb_array["g"], $rgb_array["b"]);
        imagefill($image_data["binary_data"], 0, 0, $bg_color);
        
        $current_width = $image_info[0];
        $current_height = $image_info[1];
        
        //Calculate size to keep aspect ratio
        $aspect_ratio = $current_width / $current_height;
        $new_width = $height * $aspect_ratio;
        $new_height = $height;
        
        //Coordinates to center the image
        $x = ($width - $new_width) / 2;
        $y = 0; 
        
        //Scale by height if width is greater than the wanted result
        if($new_width > $width)
        {
        	$aspect_ratio = $current_height / $current_width;
        	$new_width = $width;
       		$new_height = $height * $aspect_ratio;
       		
       		//Coordinates to center the image
		    $x = 0;
		    $y = ($height - $new_height) / 2; 
        }
        
        imagecopyresampled($image_data["binary_data"], $original_image, $x, $y, 0, 0, $new_width, $new_height, $current_width, $current_height);
	}
    else if($width > 0 && $height > 0 && $aspect_ratio)
	{
		$image_data["binary_data"] =  imagecreatetruecolor($width, $height);
        
        //Default to white background 
        $bg_color = imagecolorallocate($image_data["binary_data"], 0xff, 0xff, 0xff);
        imagefill($image_data["binary_data"], 0, 0, $bg_color);
        
        $current_width = $image_info[0];
        $current_height = $image_info[1];
        
        //Calculate size to keep aspect ratio
        $aspect_ratio = $current_width / $current_height;
        $new_width = $height * $aspect_ratio;
        $new_height = $height;
        
        //Coordinates to center the image
        $x = ($width - $new_width) / 2;
        $y = 0; 
        
        //Scale by height if width is greater than the wanted result
        if($new_width > $width)
        {
        	$aspect_ratio = $current_height / $current_width;
        	$new_width = $width;
       		$new_height = $height * $aspect_ratio;
       		
       		//Coordinates to center the image
		    $x = 0;
		    $y = ($height - $new_height) / 2; 
        }
        
        imagecopyresampled($image_data["binary_data"], $original_image, $x, $y, 0, 0, $new_width, $new_height, $current_width, $current_height);
	}
	else if($width > 0 && $height > 0)
	{
		$image_data["binary_data"] =  imagecreatetruecolor($width, $height);
		MakeTransparent($image_data["binary_data"], $image_data["mime"]);

		imagecopyresampled($image_data["binary_data"], $original_image, 0, 0, 0, 0, $width, $height, $image_info[0], $image_info[1]);
	}
	else if($width > 0)
	{
		$new_height = ($width / $image_info[0]) * $image_info[1];
		$image_data["binary_data"] =  imagecreatetruecolor($width, $new_height);
		MakeTransparent($image_data["binary_data"], $image_data["mime"]);

		imagecopyresampled($image_data["binary_data"], $original_image, 0, 0, 0, 0, $width, $new_height, $image_info[0], $image_info[1]);
	}
	else
	{
		$image_data["binary_data"] =  imagecreatetruecolor($image_info[0], $image_info[1]);
		MakeTransparent($image_data["binary_data"], $image_data["mime"]);

		imagecopyresampled($image_data["binary_data"], $original_image, 0, 0, 0, 0, $image_info[0], $image_info[1], $image_info[0], $image_info[1]);
	}

	imagedestroy($original_image);

	return $image_data;
}

/**
 * Makes an image resource transparent if gif or png.
 *
 * @param resource &$image Reference to the resource image.
 * @param string $mime To check if png of gif.
 */
function MakeTransparent(&$image, $mime)
{
	switch($mime)
	{
		case "image/png":
		case "image/gif":

		imagealphablending($image, false);
	 	imagesavealpha($image, true);
		$transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
		imagefill($image, 0, 0, $transparent);

		break;
	}
}

/**
 * Sends an image to the browser.
 *
 * @param array $image Array returned from Get() function to display it.
 */
function Display($image)
{
	//First reset headers
	header("Pragma: "); 		//This one is set to no-cache so we disable it
	header("Cache-Control: "); 	//also set to no cache
	header("Last-Modified: "); 	//We try to reset to only send one date
	header("Expires: "); 		//We try to reset to only send one expiration date
	header("X-Powered-By: ");	//We remove the php powered by since we want to pass as normal file
	
	header("Etag: \"" . md5_file($image["path"]) . "\"");
	header("Cache-Control: max-age=1209600");
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($image["path"])).'GMT');
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + (14 * 24 * 60 * 60)).'GMT');
	header("Accept-Ranges: bytes");
	header("Content-Lenght: " . filesize($image["path"]));
	
	switch($image["mime"])
	{
		case "image/jpeg":
			header("Content-Type: image/jpeg");
            
            //Save to image cache
			imagejpeg($image["binary_data"], GetCachePath(), \JarisCMS\Setting\Get("image_compression_quality", "main"));
            
            //Output image
			imagejpeg($image["binary_data"], null, \JarisCMS\Setting\Get("image_compression_quality", "main"));
			break;
            
		case "image/png":
			header("Content-Type: image/png");
            
            //Save to image cache
			imagepng($image["binary_data"], GetCachePath());
            
            //Output image
			imagepng($image["binary_data"]);
			break;
            
		case "image/gif":
			header("Content-Type: image/gif");
            
            //Save to image cache
			imagegif($image["binary_data"], GetCachePath());
            
            //Output image
			imagegif($image["binary_data"]);
			break;
	}

	imagedestroy($image["binary_data"]);
	exit;
}

/**
 * Prints to browser or any http client an image stored on the cache.
 * 
 * @param string $path The current file path of the image to print.
 */
function PrintFromCache($path)
{
	$image_info = getimagesize($path);

	//First reset headers
	header("Pragma: "); 		//This one is set to no-cache so we disable it
	header("Cache-Control: "); 	//also set to no cache
	header("Last-Modified: "); 	//We try to reset to only send one date
	header("Expires: "); 		//We try to reset to only send one expiration date
	header("X-Powered-By: ");	//We remove the php powered by since we want to pass as normal file
	
	//Set headers to enable image caching
	header("Content-Type: {$image_info['mime']}");
	header("Etag: \"" . md5_file($path) . "\"");
	header("Cache-Control: max-age=1209600");
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($path)).'GMT');
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + (14 * 24 * 60 * 60)).'GMT');
	header("Accept-Ranges: bytes");
	header("Content-Lenght: " . filesize($path));
	
	ob_clean();
	flush();
	readfile($path);
	exit;
}

/**
 * Prints the picture of a user.
 *
 * @param $page the symbolic path where the picture resides.
 */
function PrintAvatar($page)
{
	$picture_data = \JarisCMS\URI\GetAvatarInfo($page);

	$image = null;
	if($size = \JarisCMS\Setting\Get("user_picture_size", "main"))
	{
		$size = strtolower($size);
		$size = explode("x", $size);

		$image = Get($picture_data["path"], $size[0], $size[1]);
	}
	else
	{
		$image = Get($picture_data["path"], 150, 150);
	}

	Display($image);
}


/**
 * Converts a string hex like ffffff to rgb format for use on image functions.
 * 
 * @param string $value The string to convert to rgb.
 * 
 * @return array An array in the format $rgb["r"], $rgb["g"], $rgb["b"]
 */
function HTMLHexToRGB($value)
{
    $rgb["r"] = hexdec($value{0} . $value{1});
    $rgb["g"] = hexdec($value{2} . $value{3});
    $rgb["b"] = hexdec($value{4} . $value{5});
    
    return $rgb;
}

/**
 * Removes all the content of the image-cache directory.
 *
 * @return bool true on success or false on fail. 
 */
function ClearCache()
{
	$image_cache_directory = \JarisCMS\Setting\GetDataDirectory() . "image-cache";
	
	return \JarisCMS\FileSystem\RemoveDirRecursively($image_cache_directory, true);
}
?>