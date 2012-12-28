<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions dealing with the translation of content.
 */

namespace JarisCMS\Language;

/**
 * Creates translation for a given page and stores it.
 *
 * @param string $page The uri of the page to translate, example: mysection/mypage
 * @param array $data An array of the translated data in the format:
 *             data = array("title"=>"value", "content"=>value ...)
 * @param string $language_code
 *
 * @return bool False if failed to wrote or true on success.
*/
function TranslatePage($page, $data, $language_code)
{
	$path = dt(\JarisCMS\Page\GeneratePath($page), $language_code, true);
	
    $data["groups"] = serialize($data["groups"]);
	$data["categories"] = serialize($data["categories"]);

	//Edit translation if already exist
	if(file_exists($path))
	{
		\JarisCMS\PHPDB\Edit(0, $data, $path . "/data.php");
	}

	//Create translation if doesnt exist.
	else
	{
		\JarisCMS\FileSystem\MakeDir($path, 0755, true);
		\JarisCMS\FileSystem\MakeDir($path . "/blocks", 0755, true);

		if(!\JarisCMS\PHPDB\Add($data, $path . "/data.php"))
		{
			return false;
		}
	}

	return true;
}

/**
 * Used to move a translation from location when a page uri is changed.
 *
 * @param string $actual_uri The original uri of the page.
 * @param string $new_uri The new uri or path of the page.
 *
 * @return bool True on success false on fail.
 */
function MovePageTranslations($actual_uri, $new_uri)
{
	$languages = \JarisCMS\Language\GetAll();

	//move all tranaslations of the specified page
	foreach($languages as $code=>$name)
	{
		$actual_path = dt(\JarisCMS\Page\GeneratePath($actual_uri), $code, true);
		$new_path = dt(\JarisCMS\Page\GeneratePath($new_uri), $code, true);

		if(file_exists($actual_path))
		{
			if(\JarisCMS\FileSystem\MakeDir($new_path, 0755, true))
			{
				\JarisCMS\FileSystem\MoveDirRecursively($actual_path, $new_path);

				//Clears the page directory to be able to delete it
				\JarisCMS\FileSystem\RemoveDirRecursively($actual_path, true);

				RemoveEmptyDirs($actual_path, $code);
			}
			else
			{
				return false;
			}
		}
	}

	return true;
}

/**
 * Delete all the translations for a page.
 *
 * @param string $page the uri of the page to delete its translations.
 *
 * @return bool True on success false on fail.
 */
function DeletePageTranslations($page)
{
	$languages = \JarisCMS\Language\GetAll();

	//Delete all tranaslations of the specified page
	foreach($languages as $code=>$name)
	{
		$path = dt(\JarisCMS\Page\GeneratePath($page), $code, true);

		if(file_exists($path))
		{
			//Clears the page directory to be able to delete it
			if(!\JarisCMS\FileSystem\RemoveDirRecursively($path, true))
			{
				return false;
			}

			RemoveEmptyDirs($path, $code);
		}
	}

	return true;
}

/**
 * Starts deleting empty directories from the deepest one to its root.
 *
 * @param string $path The path in which the empty directories are going to be deleted.
 * @param string $code The language code.
 */
function RemoveEmptyDirs($path, $code)
{
	$main_dir = \JarisCMS\Setting\GetDataDirectory() . "language/$code/pages/singles/"; //This is the directory that is not going to be deleted

	//Checks if the path belongs to the sections path
	$path = str_replace(\JarisCMS\Setting\GetDataDirectory() . "language/$code/pages/sections/", "", $path, $count);
	if($count > 0)
	{
		$main_dir = \JarisCMS\Setting\GetDataDirectory() . "language/$code/pages/sections/";
	}
	else
	{
		$path = str_replace(\JarisCMS\Setting\GetDataDirectory() . "language/$code/pages/singles/", "", $path, $count);
	}

	$directories = explode("/", $path);
	$directory_count = count($directories);

	for($i=0; $i<$directory_count; $i++){

		$sub_directory = "";
		for($c=0; $c < $directory_count- $i; $c++){
			$sub_directory .= $directories[$c] . "/";
		}

		rmdir($main_dir . $sub_directory);
	}
}
?>