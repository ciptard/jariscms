<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions to retrieve and save configurations values replacing for example
 *      the main settings.php values.
 */

namespace JarisCMS\Setting;

/**
 * Stores a configuration option on a database file and creates it if doesnt exist.
 *
 * @param string $name Configuration name.
 * @param string $value Configuration value.
 * @param string $table Name of database configuration file stored on data/settings.
 *
 * @return bool true on success false if failed to write.
 */
function Save($name, $value, $table)
{
    $settings_file = GetDataDirectory() . "settings/$table.php";

    $fields["name"] = $name;
    $fields["value"] = $value;

    $current_settings = \JarisCMS\PHPDB\Parse($settings_file);
    
    \JarisCMS\PHPDB\Lock($settings_file);

    $setting_exists = false;
    $setting_id = 0;

    if($current_settings)
    {
        foreach($current_settings as $id=>$setting)
        {
            if(trim($setting["name"]) == $name)
            {
                $setting_exists = true;
                $setting_id = $id;
                break;
            }
        }
    }
    
    \JarisCMS\PHPDB\Unlock($settings_file);
    
    if($setting_exists)
    {
        if(!\JarisCMS\PHPDB\Edit($setting_id, $fields, $settings_file))
        {
            return false;
        }
    }
    else
    {
        if(!\JarisCMS\PHPDB\Add($fields, $settings_file))
        {
            return false;
        }
    }

    return true;
}

/**
 * Gets a configuration value from a database file.
 *
 * @param string $name Configuration to retrieve.
 * @param string $table Database configuration file name stored on data/settings.
 *
 * @return string|null Configuration value or null if doesnt exist.
 */
function Get($name, $table)
{
    static $tables_array;
    
    if(!$tables_array[$table])
    {
        $settings_file = GetDataDirectory() . "settings/$table.php";
    
        $tables_array[$table] = \JarisCMS\PHPDB\Parse($settings_file);
    }
    
    $value = null;

    if($tables_array[$table])
    {
        foreach($tables_array[$table] as $setting)
        {
            if($setting["name"] == $name)
            {
                $value = $setting["value"];
                break;
            }
        }
    }

    return $value;
}

/**
 * Gets all the configurations values from a database file.
 *
 * @param string $table Configurations database file name stored on data/settings
 *
 * @return array All configurations in the format
 *         configurations[name] = value.
 */
function GetAll($table)
{
    $settings_file = GetDataDirectory() . "settings/$table.php";

    $settings_data = \JarisCMS\PHPDB\Parse($settings_file);

    $settings = null;

    if($settings_data)
    {
        foreach($settings_data as $setting)
        {
            $settings[trim($setting["name"])] = trim($setting["value"]);
        }
    }

    return $settings;
}

/**
 * Checks if settings.php values should be override by data base settings file
 * main stored on data/settings/main.php.
 */
function Override()
{
    global $title, $base_url, $slogan, $footer_message, $theme, $theme_path, $language, $clean_urls, $user_profiles;

    if($settings = GetAll("main"))
    {
        if($settings["override"])
        {
            $title = $settings["title"]?$settings["title"]:$title;
            
            if($settings["timezone"])
            {
                date_default_timezone_set($settings["timezone"]);
            } 
            
            if($settings["auto_detect_base_url"] || trim($settings["base_url"]) == "")
            {
                $paths = explode("/", $_SERVER["SCRIPT_NAME"]);
                unset($paths[count($paths) - 1]); //Remove index.php
                $path = implode("/", $paths);
                
                $base_url = "http://" . $_SERVER["HTTP_HOST"];
                $base_url .= $path;
            }
            else
            {
                $base_url = $settings["base_url"]?$settings["base_url"]:$base_url;
            }
            
            $user_profiles = $settings["user_profiles"]?$settings["user_profiles"]:$user_profiles;
            $slogan = $settings["slogan"]?$settings["slogan"]:$slogan;
            $footer_message = $settings["footer_message"]?$settings["footer_message"]:$footer_message;
            $theme = $settings["theme"]?$settings["theme"]:$theme;
            $language = $settings["language"]?$settings["language"]:$language;
            $clean_urls = $settings["clean_urls"];

            $theme_path = $base_url . "/themes/" . $theme;
        }
    }
}

/**
 * Gets the data directory for the current domain or use default if not available.
 */
function GetDataDirectory()
{
    static $dir;
    
    if(!$dir)
    {
        //For being able to run scripts from command line
        if(!isset($_SERVER["HTTP_HOST"]))
        {
            //Check if http host was passed on the command line
            if(isset($_REQUEST["HTTP_HOST"]))
            {
                $_SERVER["HTTP_HOST"] = $_REQUEST["HTTP_HOST"];
            }
            
            //if not http_host passed then return default
            else
            {
                $dir = "data/default/";
                return $dir;
            }
        }
        
        $host = preg_replace("/^www\./", "", $_SERVER["HTTP_HOST"]);
        
        if(file_exists("data/" . $host))
        {
            $dir = "data/" . $host . "/";
        }
        else
        {
            $dir = "data/default/";
        }
    }
    
    return $dir;
}

?>