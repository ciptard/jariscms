<?php
/**
 * Copyright 2008, Jefferson González (JegoYalu.com)
 * This file is part of Jaris CMS and licensed under the GPL,
 * check the license.txt file for version and details or visit
 * http://www.gnu.org/licenses/gpl.html.
 * 
 * @file The functions to manage blocks
 */

namespace JarisCMS\Block;

/**
 * Adds a new block to a block file.
 *
 * @param array $fields An array with the needed fields to write to the block.
 * @param string $position The position of the block, valid values: header, left, right, footer, center.
 * @param string $page The page where the block reside, leave empty for global blocks.
 *
 * @return bool True on success false on fail.
 */
function Add($fields, $position, $page = "")
{
    $block_data_path = GeneratePath($position, $page);

    //Create page block directory in case is not present
    $path = str_replace("$position.php", "", $block_data_path);
    if(!file_exists($path))
    {
        \JarisCMS\FileSystem\MakeDir($path, 0755, true);
    }
    
    $fields["groups"] = serialize($fields["groups"]);
    $fields["themes"] = serialize($fields["themes"]);

    return \JarisCMS\PHPDB\Add($fields, $block_data_path);
}

/**
 * Deletes an existing block from a file.
 *
 * @param integer $id Unique identifier of the block.
 * @param string $position The position of the block, valid values: header, left, right, footer, center.
 * @param string $page The page where the block reside, leave empty for global blocks.
 *
 * @return bool true on success false on fail.
 */
function Delete($id, $position, $page = "")
{
    $block_data_path = GeneratePath($position, $page);

    return \JarisCMS\PHPDB\Delete($id,$block_data_path);
}

/**
 * Deletes an existing block by matching a value in a field.
 *
 * @param string $field_name The name of the field to match.
 * @param string $value The value to match with the field.
 *
 * @return bool True on success false on fail.
 */
function DeleteByField($field_name, $value, $page="")
{
    $block_positions[] = "header";
    $block_positions[] = "left";
    $block_positions[] = "right";
    $block_positions[] = "center";
    $block_positions[] = "footer";
    $block_positions[] = "none";
    
    foreach($block_positions as $position)
    {
        $blocks = GetList($position, $page);
        
        foreach($blocks as $id=>$fields)
        {
            if($fields[$field_name] == $value)
            {
                if(!Delete($id, $position, $page))
                {
                    return false;
                }
            }
        }
    }
    
    return true;
}

/**
 * Edits or changes the data of an existing block from a file.
 *
 * @param integer $id Unique identifier of the block.
 * @param string $position The position of the block, valid values: header, left, right, footer, center.
 * @param array $new_data An array of the fields that will substitue the old values.
 * @param string $page The page where the block reside, leave empty for global blocks.
 *
 * @return bool True on success false on fail.
 */
function Edit($id, $position, $new_data, $page = "")
{
    $block_data_path = GeneratePath($position, $page);
    
    $new_data["groups"] = serialize($new_data["groups"]);
    $new_data["themes"] = serialize($new_data["themes"]);

    return \JarisCMS\PHPDB\Edit($id, $new_data, $block_data_path);
}

/**
 * Edits an existing block by matching a value in a field.
 *
 * @param string $field_name The name of the field to match.
 * @param string $value The value to match with the field.
 * @param array $new_data The new fields to write in block.
 * @param string $page The page where the block resides, leave empty for global blocks.
 *
 * @return bool True on success or false on fail.
 */
function EditByField($field_name, $value, $new_data, $page="")
{
    $block_positions[] = "header";
    $block_positions[] = "left";
    $block_positions[] = "right";
    $block_positions[] = "center";
    $block_positions[] = "footer";
    $block_positions[] = "none";
    
    foreach($block_positions as $position)
    {
        $blocks = GetList($position, $page);
        
        foreach($blocks as $id=>$fields)
        {
            if($fields[$field_name] == $value)
            {
                if(!Edit($id, $position, $new_data, $page))
                {
                    return false;
                }
            }
        }
    }
    
    return true;
}

/**
 * Get an array with data of a specific block.
 *
 * @param integer $id Unique identifier of the block.
 * @param string $position The position of the block, valid values: header, left, right, footer, center.
 * @param string $page The page where the block reside, leave empty for global blocks.
 *
 * @return array An array with all the fields of the block.
 */
function GetData($id, $position, $page = "")
{
    $block_data_path = GeneratePath($position, $page);

    $blocks = \JarisCMS\PHPDB\Parse($block_data_path);
    
    $blocks[$id]["groups"] = unserialize($blocks[$id]["groups"]);
    $blocks[$id]["themes"] = unserialize($blocks[$id]["themes"]);

    return $blocks[$id];
}

/**
 * Gets an existing block data by matching a value in a field.
 *
 * @param string $field_name The name of the field to match.
 * @param string $value The value to match with the field.
 * @param string $page If a page block.
 *
 * @return bool True on success false on fail.
 */
function GetDataByField($field_name, $value, $page="")
{
    $block_positions[] = "header";
    $block_positions[] = "left";
    $block_positions[] = "right";
    $block_positions[] = "center";
    $block_positions[] = "footer";
    $block_positions[] = "none";
    
    foreach($block_positions as $position)
    {
        $blocks = GetList($position, $page);
        
        foreach($blocks as $id=>$fields)
        {
            if($fields[$field_name] == $value)
            {
                return GetData($id, $position, $page);
            }
        }
    }
    
    return null;
}

/**
 * Gets the full list of blocks from a file.
 *
 * @param string $position The position of the block, valid values: header, left, right, footer, center
 * @param string $page The page where the block reside, leave empty for global blocks.
 *
 * @return array|null Blocks or null if no blocks available.
 */
function GetList($position, $page = "")
{
    $block_data_path = GeneratePath($position, $page);

    $blocks = \JarisCMS\PHPDB\Parse($block_data_path);

    if($blocks == false)
    {
        return null;
    }
    else
    {
        return $blocks;
    }
}

/**
 * Moves a blocks from one position to another.
 *
 * @param $id Unique identifier of the block.
 * @param $current_position The position of the block, valid values: header, left, right, footer, center.
 * @param $new_position The new position of where to move the block.
 * @param $page The page where the block reside, leave empty for global blocks.
 *
 * @return bool True on success false on fail.
 */
function Move($id, $current_position, $new_position, $page = "")
{
    $block_data_path = GeneratePath($current_position, $page);

    $current_block_data = \JarisCMS\PHPDB\GetData($id, $block_data_path);

    $new_block_data_path = GeneratePath($new_position, $page);

    \JarisCMS\PHPDB\Add($current_block_data, $new_block_data_path);

    return \JarisCMS\PHPDB\Delete($id, $block_data_path);
}

/**
 * Checks if the current user group has access to the block.
 *
 * @param array $block Data array of the block to check.
 *
 * @return bool True if has access or false if not.
 */
function UserGroupHasAccessTo($block)
{    
    $current_group = \JarisCMS\Security\GetCurrentUserGroup();
    
    //If administrator not selected any group return true or admin logged.
    if(!$block["groups"] || \JarisCMS\Security\IsAdminLogged())
    {
        return true;
    }
    
    foreach($block["groups"] as $machine_name)
    {
        if($machine_name == $current_group)
        {
            return true;
        }
    }
    
    return false;
}

/**
 * Checks if a block can be displayed on a given page uri.
 *
 * @param array $block Data array of the block to check.
 * @param string $page The uri of the page to check blocks access
 *
 * @return bool True if has access or false if not.
 */
function PageHasAccess($block, $page)
{
    $pages = explode(",", $block["pages"]);
    
    if($block["display_rule"] == "all_except_listed")
    {
        foreach($pages as $page_check)
        {
            $page_check = trim($page_check);
            
            //Check if no pages listed and display in all pages.
            if($page_check == "")
            {
                return true;
            }
            
            $page_check = str_replace(array("/", "/*"), array("\\/", "/.*"), $page_check);
            $page_check = "/^$page_check\$/";
            
            if(preg_match($page_check, $page))
            {
                return false;
            }
        }
    }
    else if($block["display_rule"] == "just_listed")
    {
        foreach($pages as $page_check)
        {
            $page_check = trim($page_check);
            $page_check = str_replace(array("/", "*"), array("\\/", ".*"), $page_check);
            $page_check = "/^$page_check\$/";
            
            if(preg_match($page_check, $page))
            {
                return true;
            }
        }
        
        return false;
    }
    
    return true;
}

/**
 * Set the specific settings of a page blocks post settings.
 *
 * @param array $settings The settings to save.
 * @param string $page The uri of the page to set the specific post settings.
 *
 * @return bool True on success false if fail.
 */
function SetPagePostSettings($settings, $page)
{
    $settings_path = \JarisCMS\Page\GeneratePath($page) . "/blocks/post_settings.php";
    
    $settings_data[0] = $settings;
    
    //Create blocks directory if not exists
    if(!file_exists(\JarisCMS\Page\GeneratePath($page) . "/blocks"))
    {
        \JarisCMS\FileSystem\MakeDir(\JarisCMS\Page\GeneratePath($page) . "/blocks");
    }
    
    return \JarisCMS\PHPDB\Write($settings_data, $settings_path);
}

/**
 * Gets the specific settings of a page blocks post settings.
 *
 * @param string $page The uri of the page to get the specific post settings.
 *
 * @return array All the post settings.
 */
function GetPagePostSettings($page)
{
    $settings_path = \JarisCMS\Page\GeneratePath($page) . "/blocks/post_settings.php";
    
    $settings = array();
    if(file_exists($settings_path))
    {
        $settings = \JarisCMS\PHPDB\Parse($settings_path);    
    }
    else
    {
        $fields["display_title"] = false;
        $fields["display_image"] = false;
        $fields["thumbnail_width"] = "125";
        $fields["maximum_words"] = 20;
        $fields["display_view_more"] = true;
        
        $settings[0] = $fields;
    }
    
    return  $settings[0];
}

/**
 * Generates the content for a block that display a summary of full page content.
 *
 * @param string $uri The uri of the block to display a summary.
 * @param string $page_uri The uri of the page where the content block resides.
 *
 * @return array Block post data that can be added to actual block data array.
 */
function GeneratePostContent($uri, $page_uri=null)
{
    $settings = GetPagePostSettings($page_uri);
    
    $page_data = \JarisCMS\Page\GetData($uri, \JarisCMS\Language\GetCurrent());
    $content = $page_data["content"];
    $image = "";
    $image_path = "";
    $post_title = "";
    $post_title_plain = "";
    $view_more = "";
    $view_url = "";
    
    $content = \JarisCMS\InputFormat\FilterData($page_data["content"], $page_data["input_format"]);
    $content = \JarisCMS\System\PrintContentPreview($content, $settings["maximum_words"], true);
    
    if($settings["display_image"])
    {
        $images = \JarisCMS\PHPDB\Sort(\JarisCMS\Image\GetList($uri), "order");
        
        foreach($images as $id=>$fields)
        {
            $image_options["w"] = $settings["thumbnail_width"];
            
            if($settings["thumbnail_height"])
            {
                $image_options["h"] = $settings["thumbnail_height"];
            }
            
            if($settings["keep_aspect_ratio"])
            {
                $image_options["ar"] = "1";
            }
            
            if($settings["thumbnail_background_color"])
            {
                $image_options["bg"] = $settings["thumbnail_background_color"];
            }
            
            $image = "<a title=\"{$fields['description']}\" href=\"" . \JarisCMS\URI\PrintURL("$uri") . "\">" . "<img alt=\"{$fields['description']}\" src=\"" . \JarisCMS\URI\PrintURL("image/$uri/{$fields["name"]}", $image_options) . "\" />" . "</a>";
            $image_path = \JarisCMS\URI\PrintURL("image/$uri/$id");
            
            break;            
        }
    }
    
    if($settings["display_title"])
    {        
        $post_title = "<a title=\"{$page_data['title']}\" href=\"" . \JarisCMS\URI\PrintURL("$uri") . "\">" . $page_data["title"] . "</a>";
        $post_title_plain = $page_data["title"];
    }
    
    if($settings["display_view_more"])
    {    
        $view_more = "<a title=\"{$page_data['title']}\" href=\"" . \JarisCMS\URI\PrintURL("$uri") . "\">" . t("view more") . "</a>";
        $view_url = \JarisCMS\URI\PrintURL("$uri");
    }
    
    $fields["content"] = $content;
    $fields["image"] = $image;
    $fields["image_path"] = $image_path;
    $fields["post_title"] = $post_title;
    $fields["post_title_plain"] = $post_title_plain;
    $fields["view_more"] = $view_more;
    $fields["view_url"] = $view_url;

    return $fields;
}

/**
 * Move blocks to correct positions depending on theme.
 * @param array $header
 * @param array $left
 * @param array $right
 * @param array $center
 * @param array $footer
 */
function MoveByTheme(&$header, &$left, &$right, &$center, &$footer)
{
    global $theme;
    
    $all_blocks = array();
    $all_blocks["header"] = $header;
    $all_blocks["left"] = $left;
    $all_blocks["right"] = $right;
    $all_blocks["center"] = $center;
    $all_blocks["footer"] = $footer;
    
    foreach($all_blocks as $position=>$blocks)
    {
        foreach($blocks as $block_id=>$block_data)
        {
            if(isset($block_data["themes"]))
            {
                $themes_conf = unserialize($block_data["themes"]);
                
                if(is_array($themes_conf))
                {
                    if(isset($themes_conf[$theme]))
                    {
                        if($themes_conf[$theme] != "" && $themes_conf[$theme] != $position)
                        {
                            $block_data["original_position"] = $position;
                            $block_data["original_id"] = $block_id;
                            
                            switch($themes_conf[$theme])
                            {
                                case "header":
                                    $header[] = $block_data;
                                    break;
                                case "left":
                                    $left[] = $block_data;
                                    break;
                                case "right":
                                    $right[] = $block_data;
                                    break;
                                case "center":
                                    $center[] = $block_data;
                                    break;
                                case "footer":
                                    $footer[] = $block_data;
                                    break;
                                case "none":
                                    if($position == "header")
                                        unset($header[$block_id]);
                                    elseif($position == "left")
                                        unset($left[$block_id]);
                                    elseif($position == "right")
                                        unset($right[$block_id]);
                                    elseif($position == "center")
                                        unset($center[$block_id]);
                                    elseif($position == "footer")
                                        unset($footer[$block_id]);
                                    break;
                            }
                            
                            if($themes_conf[$theme] != "none")
                            {
                                if($position == "header")
                                    unset($header[$block_id]);
                                elseif($position == "left")
                                    unset($left[$block_id]);
                                elseif($position == "right")
                                    unset($right[$block_id]);
                                elseif($position == "center")
                                    unset($center[$block_id]);
                                elseif($position == "footer")
                                    unset($footer[$block_id]);
                            }
                        }
                    }
                }
            }
        }
    }
}

/**
 * Generates an array of select fields for each theme so the user can select
 * on which position to display a block per theme.
 * @param array $selected
 * @return array
 */
function GetThemeFields($selected=null)
{
	$fields = array();
	
	$themes_list = \JarisCMS\Theme\GetEnabled();
	
    $index=0;
    
	foreach($themes_list as $theme_path)
	{
		$theme_info = \JarisCMS\Theme\GetInfo($theme_path);
        
        $positions = array();
        $positions[t("Default")] = "";
        $positions[t("Header")] = "header";
        $positions[t("Left")] = "left";
        $positions[t("Right")] = "right";
        $positions[t("Center")] = "center";
        $positions[t("Footer")] = "footer";
        $positions[t("None")] = "none";
		
        $index++;
        
        if(is_array($selected))
        {
            if(isset($selected[$theme_path]))
            {
                $fields[] = array(
                    "type"=>"select", 
                    "selected"=>$selected[$theme_path], 
                    "label"=>t($theme_info["name"]), 
                    "name"=>"themes[$theme_path]", 
                    "id"=>"themes-$index", 
                    "value"=>$positions
                );
                
                continue;
            }
        }
		
        $fields[] = array(
            "type"=>"select", 
            "selected"=>isset($_REQUEST["themes"][$theme_path])?$_REQUEST["themes"][$theme_path]:"", 
            "label"=>t($theme_info["name"]), 
            "name"=>"themes[$theme_path]", 
            "id"=>"themes-$index", 
            "value"=>$positions
        );

	}
	
	return $fields;
}

/**
 * Generates the data path where the block resides.
 *
 * @param $position The position of the block, valid values: header, left, right, footer, center.
 * @param $page The page where the block reside, leave empty for global blocks.
 *
 * @return string The path of the blocks file example data/blocks/left.php
 */
function GeneratePath($position, $page = "")
{
    $block_path = "";

    if($page)
    {
        //Page block
        $block_path = \JarisCMS\Page\GeneratePath($page) . "/";
    }
    else
    {
        //Global block
        $block_path = \JarisCMS\Setting\GetDataDirectory();
    }

    $block_path .= "blocks/" . $position . ".php";

    return $block_path;
}
?>
