<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions to manage the menu system.
 *
 *@note This files uses some unix only shell commands, making the cms
 *      only linux compatible, this may change on the future to make it
 *      compatible with windows OS.
 */

namespace JarisCMS\Menu;

/**
 * Creates the data file for a menu.
 *
 * @param string $menu_name The name to give to the menu with [a-z](-) characters only.
 *
 * @return bool True on success or false on fail.
 */
function Create($menu_name)
{
	$menu_file = GeneratePath($menu_name);

	if(file_exists($menu_file))
	{
		return \JarisCMS\System\GetErrorMessage("menu_exist");
	}
	
	//Create an empty menu file and supress invalid array warning
	if(@ !\JarisCMS\PHPDB\Write(null, $menu_file))
	{
		return \JarisCMS\System\GetErrorMessage("write_error_data");
	}

	return "true";
}

/**
 * Deletes a menu file.
 *
 * @param string $menu_name The name of the file to delete.
 *
 * @return bool True on success or false on fail.
 */
function Delete($menu_name)
{
	$menu_file = GeneratePath($menu_name);

	if(!unlink($menu_file))
	{
		return false;
	}

	return true;
}

/**
 * Renames a menu file
 *
 * @param string $actual_name The actual menu file name.
 * @param string $new_name The new name given to the file.
 *
 * @return bool "true" string on success or error message.
 */
function Rename($actual_name, $new_name)
{
	$actual_path = GeneratePath($actual_name);

	$new_path = GeneratePath($new_name);

	if(file_exists($new_path))
	{
		return \JarisCMS\System\GetErrorMessage("menu_exist");
	}

	if(!rename($actual_path, $new_path))
	{
		return \JarisCMS\System\GetErrorMessage("write_error_data");
	}

	return "true";
}

/**
 * Gets all the menu files available on the system.
 *
 * @return array The name of all existing menu files.
 */
function GetList()
{
	$menu_dir = opendir(\JarisCMS\Setting\GetDataDirectory() . "menus");

	$menus = array();
	while(($menu = readdir($menu_dir)) !==  false)
	{
		if(filetype(\JarisCMS\Setting\GetDataDirectory() . "menus/" . $menu) == "file")
		{
			$menus[] = str_replace(".php", "", $menu);
		}
	}

	closedir($menu_dir);

	return $menus;
}

/**
 * Adds a new menu item to a menu file.
 *
 * @param string $menu_name Where the new menu item is going to be added.
 * @param array $fields An array with the needed fields to write to the block.
 *
 * @return bool True on success or false on fail.
 */
function AddItem($menu_name, $fields)
{
	$menu_data_path = GeneratePath($menu_name);

	return \JarisCMS\PHPDB\Add($fields, $menu_data_path);
}

/**
 * Deletes an existing menu item from a menu file.
 *
 * @param integer $id Unique identifier of the menu item.
 * @param string $menu_name The menu that contains the item.
 *
 * @return bool True on success false on fail.
 */
function DeleteItem($id, $menu_name)
{
	$menu_data_path = GeneratePath($menu_name);

	return \JarisCMS\PHPDB\Delete($id, $menu_data_path);
}

/**
 * Edits or changes the data of an existing menu item from a menu file.
 *
 * @param integer $id Unique identifier of the menu.
 * @param string $menu_name The menu were the item resides
 * @param array $new_data An array of the fields that will substitue the old values.
 *
 * @return true on success false on fail.
 */
function EditItem($id, $menu_name, $new_data)
{
	$menu_data_path = GeneratePath($menu_name);

	return \JarisCMS\PHPDB\Edit($id, $new_data, $menu_data_path);
}

/**
 * Get an array with data of a specific menu item.
 *
 * @param integer $id Unique identifier of the menu item.
 * @param string $menu_name The menu where the item resides.
 *
 * @return array An array with all the fields of the menu.
 */
function GetItemData($id, $menu_name)
{
	$menu_data_path = GeneratePath($menu_name);

	$menu = \JarisCMS\PHPDB\Parse($menu_data_path);

	return $menu[$id];
}

/**
 * Gets the full list of menu items from a file.
 *
 * @param string $menu_name The menu where the menu items reside.
 * 
 * @return array List of menu items.
 */
function GetItemList($menu_name)
{
    static $menu_array;
    
    if(!$menu_array[$menu_name])
    {
    	$menu_data_path = GeneratePath($menu_name);
    
    	$menu_array[$menu_name] = \JarisCMS\PHPDB\Parse($menu_data_path);
    }

	return $menu_array[$menu_name];
}

/**
 * Recursive function that returns the sub menu items of a menu item.
 * 
 * @param string $menu_name the name of the menu.
 * @param integer|string $parent_id The id of the parent item.
 * 
 * @return array The parent item with its sub items and also the sub 
 *        items of the sub items in another array. For example:
 * 		  $parent_item = array(..., menu_item_values, ..., "sub_items"=>array())
 */
function GetSubItems($menu_name, $parent_id="root")
{
	$menu_items = GetItemList($menu_name);
	
	$menu = array();
	foreach($menu_items as $id=>$items)
	{
		if("" . $items["parent"] . "" == "" . $parent_id . "")
		{
			//get the sub items of this item
			$sub_items["sub_items"] = \JarisCMS\PHPDB\Sort(GetSubItems($menu_name, $id), "order");
			
			if(count($sub_items["sub_items"]) > 0)
			{
				$items += $sub_items;
			}
			
			$menu[$id] = $items;
		}
	}
	
	return $menu;
}

/**
 * Gets the machine name of the primary menu.
 * 
 * @return string Name of primary menu.
 */
function GetPrimaryName()
{
	$name = \JarisCMS\Setting\Get("primary_menu", "main");
	
	if($name)
	{
		return $name;
	}
	
	return "primary";
}

/**
 * Gets the machine name of the secondary menu.
 * 
 * @return string Name of secondary menu.
 */
function GetSecondaryName()
{
	$name = \JarisCMS\Setting\Get("secondary_menu", "main");
	
	if($name)
	{
		return $name;
	}
	
	return "secondary";
}

/**
 * Generates the data path where the menu resides.
 *
 * @param string $menu The name of the menu file.
 *
 * @return string path to menu file.
 */
function GeneratePath($menu)
{
	$menu_path = \JarisCMS\Setting\GetDataDirectory() . "menus/";

	$menu_path .= $menu . ".php";

	return $menu_path;
}

?>