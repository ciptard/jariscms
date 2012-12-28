<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file File that groups all necessary includes
 */

//Global variables
include("settings.php");
include("include/globals.php");

//System
include("include/mail.php");
include("include/file.php");
include("include/forms.php");
include("include/login.php");
include("include/image.php");
include("include/theme.php");
include("include/sqlite.php");
include("include/module.php");
include("include/search.php");
include("include/system.php");
include("include/version.php");
include("include/language.php");
include("include/translate.php");
include("include/uri_manager.php");
include("include/data_browser.php");
include("include/data_manager.php");
include("include/theme_manager.php");
include("include/configurations.php");

//Data manipulation functions
include("include/data_managers/page_manager.php");
include("include/data_managers/file_manager.php");
include("include/data_managers/menu_manager.php");
include("include/data_managers/user_manager.php");
include("include/data_managers/types_manager.php");
include("include/data_managers/group_manager.php");
include("include/data_managers/block_manager.php");
include("include/data_managers/image_manager.php");
include("include/data_managers/fields_manager.php");
include("include/data_managers/category_manager.php");
include("include/data_managers/input_formats_manager.php");

//Classes
include("include/classes/jaris_sqlite_search.php");

//Third party libraries
include("include/third_party/phpmailer/class.phpmailer.php");

//Add installed modules include files here
$installed_modules = JarisCMS\Module\GetInstalledNames();

foreach($installed_modules as $machine_name)
{
    $module_directory = "modules/$machine_name/include/";
    if(file_exists($module_directory))
    {
        $dir_handle = opendir($module_directory);
        
        while(($file = readdir($dir_handle)) !== false)
        {
            if(strcmp($file, ".") != 0 && strcmp($file, "..") != 0)
            {
                if(is_file($module_directory . $file))
                {
                    include($module_directory . $file);
                }
            }
        }
    }
}

?>
