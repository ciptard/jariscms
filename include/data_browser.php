<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions to browse the database system pages file structure.
 */

namespace JarisCMS\FileSystem;

/**
 * Gets all the directories inside of a path
 *
 * @param string $directory Main path to scan for directories.
 *
 * @return array Directories found in the format:
 *         directories[] = array("main"=>"main_path", "dir"=>"directory_name")
 */
function GetDirectoriesFromPath($directory)
{
    $main_dir = $directory . "/";
    $dir_handle = opendir($main_dir);

    while(($file = readdir($dir_handle)) !== false)
    {
        //just add directories inside the file
        if(strcmp($file, ".") != 0 && strcmp($file, "..") != 0)
        {
            if(is_dir($main_dir . $file))
            {
                $dir_array[] = array("main"=>$main_dir, "dir"=>$file);
            }
        }
    }

    return \JarisCMS\PHPDB\Sort($dir_array, "dir");
}

/**
 * Generates an array suitable to create a page navigation menus.
 *
 * @param array $directories Directories to classify. @see GetDirectoriesFromPath()
 * @param string $main_dir The main directory where data resides, for example: data/pages
 *
 * @return array page navigation in the format array[] = array("type"=>"page, alphabet or section",
 *         "path"=>"path to destination relative to data/pages/"
 */
function GenerateNavigationList($directories, $main_dir)
{
    foreach($directories as $directory)
    {
        $full_path = $directory["main"] . $directory["dir"];
        $relative_path = str_replace($main_dir . "/", "", $full_path);

        if(file_exists($full_path . "/data.php"))
        {
            $navigation[] = array("type"=>"page", "path"=>$relative_path, "current"=>$directory["dir"]);
        }
        elseif(strlen($directory["dir"]) < 3)
        {
            $navigation[] = array("type"=>"alphabet", "path"=>$relative_path, "current"=>$directory["dir"]);
        }
        else
        {
            $navigation[] = array("type"=>"section", "path"=>$relative_path, "current"=>$directory["dir"]);
        }
    }

    return $navigation;
}

/**
 * Transform a page relative path to its uri.
 *
 * @param string $relative_path Path to trasform for example:
 *        sections/admin/b/bl/blocks = admin/blocks or
 *        singles/a/ac/access-denied = access-denied
 *
 * @return string Uri of the page.
 */
function GetURIFromPath($relative_path)
{
    $uri = "";

    $fragments = explode("/", $relative_path);

    $fragments_count = count($fragments);

    //Remove 2 letters folder.
    $fragments[$fragments_count - 2] = "";

    //Remove 1 letter folder.
    $fragments[$fragments_count - 3] = "";

    for($i=1; $i<$fragments_count; $i++)
    {
        if($fragments[$i])
        {
            $uri .= $fragments[$i] . "/";
        }
    }

    //remove last trailing slash
    $uri = rtrim($uri , "/");

    return $uri;
}
?>
