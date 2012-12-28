<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file The functions to manage page files
 */

namespace JarisCMS\File;

/**
 * Adds a new file record to a page.
 *
 * @param array $file_array An array with the needed fields to write to the block.
 * @param string $description A brief description of the file.
 * @param string $page The page where the file reside.
 * @param string $file_name Returns the file name.
 *
 * @return string "true" string on success or error message.
 */
function Add($file_array, $description, $page = "", &$file_name=null)
{
	//TODO: Check file mime type before adding the file.
	$file_data_path = GeneratePath($page);

	//Create file directory in case is not present
	$path = str_replace("files.php", "files", $file_data_path);
	if(!file_exists($path))
	{
		\JarisCMS\FileSystem\MakeDir($path, 0755, true);
	}

	$destination = $path . "/" . $file_array["name"];

	$file_name = \JarisCMS\FileSystem\MoveFile($file_array["tmp_name"], $destination);

	if(!$file_name)
	{
		return \JarisCMS\System\GetErrorMessage("write_error_data");
	}

	$fields["name"] = $file_name;
	$fields["description"] = $description;
	$fields["mime-type"] = $file_array["type"];

	\JarisCMS\PHPDB\Add($fields, $file_data_path);

	return "true";
}

/**
 * Deletes an existing file record from a file.php file.
 *
 * @param integer $id Unique identifier of the file.
 * @param string $page The page uri where the file reside.
 *
 * @return bool True on success false on fail.
 */
function Delete($id, $page)
{
	$file_data_path = GeneratePath($page);

	$file_data = GetData($id, $page);

	//For not having problems clean any \n\t and many others
	$file_data["name"] = trim($file_data["name"]);

	$file_file_path = str_replace("files.php", "files/{$file_data['name']}", $file_data_path);

	//Remove file
	if(!unlink($file_file_path))
	{
		return false;
	}

	//Remove file record from files.php data file
	\JarisCMS\PHPDB\Delete($id, $file_data_path);

	return true;
}

/**
 * Edits or changes the data of an existing file from a file.php file.
 *
 * @param integer $id Unique identifier of the file.
 * @param array $new_data An array of the fields that will substitue the old values.
 * @param string $page The page uri where the file reside.
 *
 * @return bool True on success false on fail.
 */
function Edit($id, $new_data, $page)
{
	$file_data_path = GeneratePath($page);

	return \JarisCMS\PHPDB\Edit($id, $new_data, $file_data_path);
}

/**
 *Get an array with data of a specific file.
 *
 *@param integer $id Unique identifier of the file.
 *@param string $page The page uri where the file reside.
 *
 *@return array All the fields of the file.
 */
function GetData($id, $page)
{
	$file_data_path = GeneratePath($page);

	$files = \JarisCMS\PHPDB\Parse($file_data_path);

	return $files[$id];
}

/**
 * Gets the full list of files from the file.php file of a page.
 *
 * @param string $page The page where the file.php file reside.
 */
function GetList($page)
{
	$file_data_path = GeneratePath($page);

	$files = \JarisCMS\PHPDB\Parse($file_data_path);

	if($files == false)
	{
		return null;
	}
	else
	{
		return $files;
	}
}

/**
 *Generates the data path where the file database resides.
 *
 *@param $page The page uri to translate to a valid file.php data path.
 */
function GeneratePath($page)
{
	$file_data_path = \JarisCMS\Page\GeneratePath($page) . "/files.php";

	return $file_data_path;
}

?>
