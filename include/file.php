<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Miscelaneous functions to manage files and directories.
 */

namespace JarisCMS\FileSystem;

/**
 * Moves a file to another path and renames it if already a file with the same name
 * exist. Also strips any special characters. @see Rename()
 *
 * @param string $source The file to move.
 * @param string $destination The new path of the file.
 *
 * @return string|bool File name of the file moved with the path stripped or false if failed.
 */
function MoveFile($source, $destination)
{
    //Strip any special characters from filename
    $name = explode("/", $destination);
    $file_name = $name[count($name)-1];
    $extension = "";
    $file_name_no_extension = \JarisCMS\URI\FromText(StripFileExtension($file_name, $extension));
    $name[count($name)-1] = $file_name_no_extension . "." . $extension;
    $destination = implode("/", $name);
    
	$destination = Rename($destination);

	if(!\rename($source, $destination))
	{
		return false;
	}

	$name = explode("/", $destination);

	return $name[count($name)-1];
}

/**
 * Check if a filename or directory already exist and generates a new one with a
 * number appended. For example if /home/test/text.txt exist
 * returns /home/test/text-0.txt
 *
 * @param string $file_name The full file path to check for existence.
 *
 * @return string The file name renamed if exist or the same file name.
 */
function Rename($file_name)
{
	$file_index = 0;

	//Check if the file already exist and appends an index
	//on it to not overwrite  the existing one.
	while(file_exists($file_name))
	{
		$segments = explode("/", $file_name);

		$filename_segments = explode(".", $segments[count($segments)-1]);

		if(count($filename_segments) > 1)
		{
			$ext = "." . $filename_segments[count($filename_segments)-1];
		}
		else
		{
			$ext = "";
		}

		$filename = "";

		for($i=0; $i<count($segments)-1; $i++)
		{
			$filename .= $segments[$i] . "/";
		}

		if(count($filename_segments) == 1)
		{
			$filename .= $segments[count($segments)-1];
		}

		for($i=0; $i<count($filename_segments)-1; $i++)
		{
			$filename .= $filename_segments[$i];

			if($i != count($filename_segments)-2)
			{
				$filename .= ".";
			}
		}

		$temp_destination_check = $filename . "-" . $file_index . $ext;
		if(file_exists($temp_destination_check))
		{
			$file_index++;
		}
		else
		{
			$file_name = $temp_destination_check;
		}
	}

	return $file_name;
}

/**
 * Prints a file to the browser using the file uri scheme.
 *
 * @param string $page The file uri on the format file/pageuri/filename_or_fileid
 */
function PrintFile($page)
{
	$uri = str_replace("file/", "", \JarisCMS\URI\Get());
	$uri = explode("/", $uri);
	unset($uri[count($uri)-1]);
	$uri = implode("/", $uri);
	
	$page_data = \JarisCMS\Page\GetData($uri);
	
	if(\JarisCMS\Page\UserAccess($page_data))
	{
		$file_array = \JarisCMS\URI\GetFileInfo($page);
		$file_data = \JarisCMS\File\GetData($file_array["id"], $file_array["page_uri"]);
	
		//If file doesnt exist go to home page
		//TODO: Replace home page with file not found page.
		if(!isset($file_array["path"]))
		{
			\JarisCMS\System\GoToPage("");
		}
	
		//First reset headers
		header("Pragma: "); 		//This one is set to no-cache so we disable it
		header("Cache-Control: "); 	//also set to no cache
		header("Last-Modified: "); 	//We try to reset to only send one date
		header("Expires: "); 		//We try to reset to only send one expiration date
		header("X-Powered-By: ");	//We remove the php powered by since we want to pass as normal file
		
		//Set headers to enable file caching
		header("Content-Disposition: inline; filename=\"{$file_data['name']}\"");
		header("Content-Type: {$file_data['mime-type']}");
		header("Etag: \"" . md5_file($file_array["path"]) . "\"");
		header("Cache-Control: max-age=1209600");
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($file_array["path"])).'GMT');
		header('Expires: '.gmdate('D, d M Y H:i:s', time() + (14 * 24 * 60 * 60)).'GMT');
		header("Accept-Ranges: bytes");
		header("Content-Lenght: " . filesize($file_array["path"]));
		
		//Print file to browser
		ob_clean();
		flush();
		readfile($file_array["path"]);
		exit;
	}
	else 
	{
		\JarisCMS\Security\ProtectPage();
	}
}

/**
 * Search for files in a directory relative to jaris cms installation.
 *
 * @param string $path Relative path to jaris installation.
 * @param string $pattern A regular expression to match the file to search.
 * @param string $callback Function to manage each file found that needs one argument to
 *                 accept the full path of match found.
 */
function SearchFiles($path, $pattern, $callback)
{
	$directory = opendir($path);

	while(($file = readdir($directory)) !== false)
	{
		$full_path = $path . "/" . $file;

		if(is_file($full_path) && preg_match($pattern, $file))
		{
			$callback($full_path);
		}
		elseif($file != "." && $file != ".." && is_dir($full_path))
		{
			SearchFiles($full_path, $pattern, $callback);
		}
	}

	closedir($directory);
}

/**
 * Search for files in a directory relative to jaris cms installation.
 *
 * @param string $path Relative path to jaris installation.
 * 
 * @return array List of files found.
 */
function GetFiles($path)
{
    $files = array();
	$directory = opendir($path);

	while(($file = readdir($directory)) !== false)
	{
		$full_path = $path . "/" . $file;

		if(is_file($full_path))
		{
			$files[] = $full_path;
		}
		elseif($file != "." && $file != ".." && is_dir($full_path))
		{
			$files = array_merge($files, GetFiles($full_path));
		}
	}

	closedir($directory);
    
    return $files;
}

/**
 * Same as php mkdir() but adds Operating system check and replaces
 * every / by \ on windows.
 *
 * @param string $directory The directory to create.
 * @param integer $mode the permissions granted to the directory.
 * @param bool $recursive Recurse in to the path creating neccesary directories.
 *
 * @return bool true on success false on fail.
 */
function MakeDir($directory, $mode = 0755, $recursive = false)
{
	if("" . strpos(PHP_OS, "WIN") . "" != "")
	{
		$directory = str_replace("/", "\\", $directory);
	}

	return mkdir($directory, $mode, $recursive);
}

/**
 * Moves a directory and its content by renaming it to another directory even
 * if already exist, mergin the content of the source directory to the target
 * directory and replacing files.
 *
 * @param string $source The dirctory to rename.
 * @param string $target The target path of the source directory.
 *
 * @return bool true on success or false on fail.
 */
function MoveDirRecursively($source, $target)
{
	$source_dir = opendir($source);

	while(($item = readdir($source_dir)) !== false)
	{
		$source_full_path = $source . "/" . $item;
		$target_full_path = $target . "/" . $item;

		if($item != "." && $item != "..")
		{
			//Replace any existing file with source one
			if(is_file($source_full_path))
			{
				//Replace existing target file with source file
				if(file_exists($target_full_path))
				{
					//Remove target file before replacing
					if(!unlink($target_full_path))
					{
						return false;
					}
				}

				//Move source file to target path
				if(!\rename($source_full_path, $target_full_path))
				{
					return false;
				}
			}
			else if(is_dir($source_full_path))
			{
				//If directory already exist just replace its content
				if(file_exists($target_full_path))
				{
					MoveDirRecursively($source_full_path, $target_full_path);
				}
				else
				{
					//If directory doesnt exist just move source directory to target path
					if(!\rename($source_full_path, $target_full_path))
					{
						return false;
					}
				}
			}
		}
	}

	closedir($source_dir);

	return true;
}

/**
 * Copy a directory and its content to another directory replacing any file
 * on the target directory if already exist.
 *
 * @param string $source The dirctory to copy.
 * @param string $target The copy destination.
 *
 * @return bool true on success or false on fail.
 */
function CopyDirRecursively($source, $target)
{
	$source_dir = opendir($source);

	//Check if source directory exists
	if(!$source_dir)
	{
		return false;
	}

	//Create target directory in case it doesnt exist
	if(!file_exists($target))
	{
		MakeDir($target, 0755, true);
	}

	while(($item = readdir($source_dir)) !== false)
	{
		$source_full_path = $source . "/" . $item;
		$target_full_path = $target . "/" . $item;

		if($item != "." && $item != "..")
		{
			//copy source files
			if(is_file($source_full_path))
			{
				if(!copy($source_full_path, $target_full_path))
				{
					return false;
				}
			}
			else if(is_dir($source_full_path))
			{
				CopyDirRecursively($source_full_path, $target_full_path);
			}
		}
	}

	closedir($source_dir);

	return true;
}

/**
 * Remove a directory that is not empty by deleting all its content.
 *
 * @param string $directory The directory to delete with all its content.
 * @param string $empty Removes all directory contents keeping only itself.
 *
 * @return bool True on success or false.
 */
function RemoveDirRecursively($directory, $empty=false)
{
	// if the path has a slash at the end we remove it here
	if(substr($directory,-1) == '/')
	{
		$directory = substr($directory,0,-1);
	}

	// if the path is not valid or is not a directory ...
	if(!file_exists($directory) || !is_dir($directory))
	{
		return false;

	// ... if the path is not readable
	}
	elseif(!is_readable($directory))
	{
		return false;
	}
	else
	{
		$handle = opendir($directory);

		while (false !== ($item = readdir($handle)))
		{
			if($item != '.' && $item != '..')
			{
				// we build the new path to delete
				$path = $directory.'/'.$item;

				// if the new path is a directory
				if(is_dir($path))
				{
					RemoveDirRecursively($path);

				// if the new path is a file
				}
				else{
					if(!unlink($path))
					{
						return false;
					}
				}
			}
		}

		closedir($handle);

		if($empty == false)
		{
			if(!rmdir($directory))
			{
				return false;
			}
		}

		return true;
	}
}

/**
 * Outputs any file that resides on the current server
 *
 * @param string $path The file on the current server.
 * @param string $name A name for the file when the download is forced.
 * @param bool $force_download Even is it is a text file is forced to download on the browser.
 * @param bool $try_compression Checks if zip support is available and compress the file.
 */
function PrintAllFiles($path, $name="file", $force_download=false, $try_compression=false)
{
	$file = $path;
		
	//First reset headers
	header("Pragma: "); 		//This one is set to no-cache so we disable it
	header("Cache-Control: "); 	//also set to no cache
	header("Last-Modified: "); 	//We try to reset to only send one date
	header("Expires: "); 		//We try to reset to only send one expiration date
	header("X-Powered-By: ");	//We remove the php powered by since we want to pass as normal file
	
	
	if($try_compression)
	{
		if(class_exists("ZipArchive"))
		{
			$zip_file = StripFileExtension($file) . ".zip";
			
			$zip = new \ZipArchive();
			$zip->open($zip_file, \ZIPARCHIVE::CREATE);
			
			$zip->addFile($path, "$name");
			$zip->close();
			
			$file = $zip_file;
			$name = StripFileExtension($name) . ".zip";
		}
	}
	
	$file_name = $name;
	
	if($name == "file")
	{
		$file_name_parts = explode("/", $path);
		$file_name = $file_name_parts[count($file_name)-1];
	}
	
	//Forces the file to download
	if($force_download)
	{
		header("Content-Description: File Transfer");
   		header("Content-Disposition: attachment; filename=\"$file_name\"");
	}
	else
	{
		header("Content-Disposition: inline; filename=\"$file_name\"");
	}
	
	//Set headers to enable file caching
	header("Content-Type: " . GetMimeType($file));
	header("Etag: \"" . md5_file($file) . "\"");
	header("Cache-Control: max-age=1209600");
	header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($file)).'GMT');
	header('Expires: '.gmdate('D, d M Y H:i:s', time() + (14 * 24 * 60 * 60)).'GMT');
	header("Accept-Ranges: bytes");
	
	//As security measure we parse as php if user is requesting a php file
	if("" . stripos($file, ".php") . "" != "")
	{
		print_php_file($path);
	}
	else
	{
		header("Content-Lenght: " . filesize($file));
		ob_clean();
		flush();
		readfile($file);
	}
	
	if($try_compression && class_exists("ZipArchive"))
	{
		unlink($file);
	}
	
	exit;
}

/**
 * Removes the extension from a file name
 *
 * @param string $filename The name or path of the file
 *
 * @return string The file name with the extension stripped out.
 */
function StripFileExtension($filename, &$extension=null)
{
	$file_array = explode(".", $filename);
    
    $extension = $file_array[count($file_array)-1];
	
	unset($file_array[count($file_array)-1]);
	
	$filename = implode("", $file_array);
	
	return $filename;
}

/**
 * Gets the mime type of a file
 *
 * @param string $path The file on the current server.
 *
 * @return string Original file mime type or application/octet-stream if not possible to retreive data.
 */
function GetMimeType($path)
{
	$fp = fsockopen ($_SERVER["SERVER_NAME"], $_SERVER["SERVER_PORT"]);
	
	if(!$fp)
	{
		return "application/octet-stream";
	}
	
	$header_done=false;
	
	$request = "GET ".$path." HTTP/1.0\r\n";
	$request .= "User-Agent: Mozilla/4.0 (compatible; MSIE 5.5; Windows 98)\r\n";
	$request .= "Host: ".$_SERVER["SERVER_NAME"]."\r\n";
	$request .= "Connection: Close\r\n\r\n";
	$return = '';
	
	fputs ($fp, $request);
	
	$line = fgets ($fp, 128);
	$header["status"] = $line;
	
	while (!feof($fp))
	{
		$line = fgets ( $fp, 128 );
		if($header_done)
		{ 
			$content .= $line;
		}
		else
		{
			if($line == "\r\n")
			{ 
				$header_done=true;
			}
			else
			{
				$data = explode(": ",$line);
				$header[$data[0]] = $data[1];
			}
		}
	}
	
	fclose ($fp); 
	
	return $header["Content-Type"];
}
?>
