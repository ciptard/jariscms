<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file The functions to manage images for a page
 */

namespace JarisCMS\Image;

/**
 * Adds a new image record to a image file.
 *
 * @param array $file_array An array with the needed fields to write to the block in the
 *               format returned by the php $_FILES["file"] array.
 * @param string $description A description of the image to store.
 * @param string $page The page where the image reside.
 * @param string $file_name Reference to the file name assigned to the image.
 *
 * @return string "true" string on success or message error when failed.
 */
function Add($file_array, $description, $page = "", &$file_name=null)
{
    $image_data_path = GeneratePath($page);

    if($file_array["type"] == "image/png" ||
       $file_array["type"] == "image/jpeg" ||
       $file_array["type"] == "image/pjpeg" ||
       $file_array["type"] == "image/gif"
      )
      {
          //Create image directory in case is not present
        $path = str_replace("images.php", "images", $image_data_path);
        if(!file_exists($path))
        {
            \JarisCMS\FileSystem\MakeDir($path, 0755, true);
        }

        $destination = $path . "/" . $file_array["name"];

        $file_name = \JarisCMS\FileSystem\MoveFile($file_array["tmp_name"], $destination);

        //
        if(!$file_name)
        {
            return \JarisCMS\System\GetErrorMessage("write_error_data");
        }

        $fields["name"] = $file_name;
        $fields["description"] = $description;
        $fields["order"] = 0;

        if(\JarisCMS\PHPDB\Add($fields, $image_data_path))
        {
            return "true";
        }
        else
        {
            return \JarisCMS\System\GetErrorMessage("write_error_data");
        }
      }
      else
      {
          return \JarisCMS\System\GetErrorMessage("image_file_type");
      }
}

/**
 * Deletes an existing image record from a image.php file.
 *
 * @param integer $id Unique identifier of the image.
 * @param string $page The page uri where the image reside.
 *
 * @return bool True on success or false if failed.
 */
function Delete($id, $page)
{
    $image_data_path = GeneratePath($page);

    $image_data = GetData($id, $page);

    //For not having problems clean any \n\t and many others
    $image_data["name"] = trim($image_data["name"]);

    $image_file_path = str_replace("images.php", "images/{$image_data['name']}", $image_data_path);

    //Remove Original Image
    if(!unlink($image_file_path))
    {
        //If this doesnt return false the everything should go right since it has
        //the permissions to delete the file
        return false;
    }

    //Remove cached images
    $image_name = str_replace("/", "-", $page);
    \JarisCMS\FileSystem\SearchFiles(\JarisCMS\Setting\GetDataDirectory() . "image-cache", "/image-$image_name-$id.*/", "unlink");

    //Remove the image record from the image.php data file
    \JarisCMS\PHPDB\Delete($id, $image_data_path);

    return true;
}

/**
 * Edits or changes the data of an existing image from a image.php file.
 *
 * @param integer $id Unique identifier of the image.
 * @param array $new_data An array of the fields that will substitue the old values.
 * @param string $page The page uri where the image reside.
 *
 * @return bool True on success false on fail.
 */
function Edit($id, $new_data, $page)
{
    $image_data_path = GeneratePath($page);

    return \JarisCMS\PHPDB\Edit($id, $new_data, $image_data_path);
}

/**
 * Get an array with data of a specific image.
 *
 * @param integer $id Unique identifier of the image.
 * @param string $page The page uri where the image reside.
 *
 * @return array An array with all the fields of the image.
 */
function GetData($id, $page)
{
    $image_data_path = GeneratePath($page);

    $images = \JarisCMS\PHPDB\Parse($image_data_path);

    return $images[$id];
}

/**
 * Gets the full list of images from the image.php file of a page.
 *
 * @param string $page The page where the image.php file reside.
 *
 * @return array Array of images or null if empty.
 */
function GetList($page)
{
    $image_data_path = GeneratePath($page);

    $images = \JarisCMS\PHPDB\Parse($image_data_path);

    if($images == false)
    {
        return null;
    }
    else
    {
        return \JarisCMS\PHPDB\Sort($images, "order");
    }
}

/**
 * Generates the data path where the image resides.
 *
 * @param string $page The page uri to translate to a valid image.php data path.
 *
 * @return string Path to image file example: data/pages/singles/home/images.php
 */
function GeneratePath($path)
{
    $image_data_path = \JarisCMS\Page\GeneratePath($path) . "/images.php";

    return $image_data_path;
}

?>