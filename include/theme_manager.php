<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions to manage themes.
 */

namespace JarisCMS\Theme;

/**
 * Scans all the available themes on the themes folder.
 *
 * @return Array with all the themes in the format:
 *         themes[path] = array(name, description, version, author, email, website)
 *         or themes[path] = null if no info file found.
 */
function GetAll()
{
    $theme_dir = "themes/";
    $dir_handle = opendir($theme_dir);

    $themes = null;

    while(($file = readdir($dir_handle)) !== false)
    {
        //just add directories
		if(
            strcmp($file, ".") != 0 && 
            strcmp($file, "..") != 0 &&
            is_dir($theme_dir . $file)
        )
		{
			$info_file = $theme_dir . $file . "/info.php";

			if(file_exists($info_file))
			{
				include($info_file);
				$themes[$file] = $theme;
			}
		}
    }

    return $themes;
}

/**
 * Gets the list of enabled themes.
 * @return array
 */
function GetEnabled()
{
    $themes = unserialize(\JarisCMS\Setting\Get("themes_enabled", "main"));
    
    \JarisCMS\Module\Hook("Theme", "GetEnabled", $themes);
    
    return $themes;
}

/**
 * Gets the info of a specific theme
 *
 * @param string $path The name of the theme folder inside the themes main folder.
 *
 * @return array Theme information in the format:
 *         info = array(name, description, version, author, email, website)
 */
function GetInfo($path)
{
    $themes = GetAll();

    return $themes[$path];
}

/**
 * Dummy function that return the global $theme variable as it should be the
 * default one.
 */
function GetDefault()
{
    global $theme;

    return $theme;
}

/*
 * @todo Check if the logged user has permissions to choose the theme and change it.
 */
function GetFromUser()
{

}

?>
