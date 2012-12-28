<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Contains some misc but important system functions.
 */

namespace JarisCMS\System;

/**
 * Generates a data array for page not found.
 *
 * @return array Page not found data.
 */
function MakePageNotFound()
{
    GetHTTPStatusHeader(404);
    
    $page[0]["title"] = t("Page not found");
    $page[0]["content"] = t("The page you was searching doesn't exists.");

    if($page_not_found = \JarisCMS\Setting\Get("page_not_found", "main"))
    {
        if($page_data = \JarisCMS\Page\GetData($page_not_found, \JarisCMS\Language\GetCurrent()))
        {
            $page[0] = $page_data;
        }
    }
    
    $tabs = array();
    
    if(\JarisCMS\Security\HasUserPermissions(array("view_content", "add_content")))
    {
        $tabs[t("Create Page")] = array("uri"=>"admin/pages/types", "arguments"=>array("uri"=>\JarisCMS\URI\Get()));
    }

    //Call MakePageNotFound modules hook before returning data
    \JarisCMS\Module\Hook("System", "MakePageNotFound", $page, $tabs);
    
    foreach($tabs as $title=>$data)
    {
        if(!isset($data["arguments"]))
        {
            AddTab($title, $data["uri"]);
        }
        else
        {
            AddTab($title, $data["uri"], $data["arguments"]);
        }
    }

    return $page;
}

/**
 * Returns a header with the indicated http error status code.
 * 
 * @param integer $code The code number to return in the header.
 */
function GetHTTPStatusHeader($code)
{
    switch($code)
    {
        case 400:
            header("HTTP/1.1 400 Bad Request", true);
            break;
        case 401:
             header("HTTP/1.1 401 Unauthorized", true);
             break;
        case 403:
            header("HTTP/1.1 403 Forbidden", true);
            break;
        case 404:
            header("HTTP/1.1 404 Not Found", true);
            break;
        case 500:
             header("HTTP/1.1 500 Internal Server Error", true);
             break;
            
         case 200:
         default:
             header( "HTTP/1.1 200 OK", true);
    }
}

/**
 * Adds a new style to the generated page.
 *
 * @return string $path relative path of the css file
 */
function AddStyle($path, $arguments=null)
{
    global $additional_styles;

    $current_url = \JarisCMS\URI\PrintURL($path, $arguments);

    //check is file is not already added
    foreach($additional_styles as $url)
    {
        if($url == $current_url)
        {
            $aldready_in_array = true;
            break;
        }
    }
    
    if(!$aldready_in_array)
    {
        $additional_styles[] = $current_url;
    }
}

/**
 * Gets all the css files available on the system.
 *
 * @return array List with the full path to files example:
 *         files[0] = "http://localhost/styles/system.css"
 */
function GetStyles()
{
    global $additional_styles;
    
    $styles_dir = "styles/";
    $dir_handle = opendir($styles_dir);

    $styles = array();

    while(($file = readdir($dir_handle)) !== false)
    {
        if(strcmp($file, ".") != 0 && strcmp($file, "..") != 0)
        {
            $style_file = $styles_dir . $file;

            if(is_file($style_file))
            {
                $styles[] = \JarisCMS\URI\PrintURL($style_file);
            }
        }
    }
    
    sort($styles);
    
    foreach($additional_styles as $url)
    {
        $styles[] = $url;
    }

    //Call GetStyles modules hook before returning data
    \JarisCMS\Module\Hook("System", "GetStyles", $styles);

    return $styles;
}

/**
 * Adds a new script to the generated page.
 *
 * @return string $path Relative path of the java script file.
 */
function AddScript($path, $arguments=null)
{
    global $additional_scripts;

    $current_url = \JarisCMS\URI\PrintURL($path, $arguments);
    
    $aldready_in_array = false;
        
    //check is file is not already added
    foreach($additional_scripts as $url)
    {
        if($url == $current_url)
        {
            $aldready_in_array = true;
            break;
        }
    }
    
    if(!$aldready_in_array)
    {
        $additional_scripts[] = $current_url;
    }
}

/**
 * Gets all the java script files available on the system
 *
 * @return array List with the full path to files example:
 *         files[0] = "http://localhost/scripts/system.js"
 */
function GetScripts()
{
    global $additional_scripts;
    
    $scripts_dir = "scripts/";
    $dir_handle = opendir($scripts_dir);

    $scripts = array();

    while(($file = readdir($dir_handle)) !== false)
    {
        if(strcmp($file, ".") != 0 && strcmp($file, "..") != 0)
        {
            $scripts_file = $scripts_dir . $file;

            if(is_file($scripts_file))
            {
                $scripts[] = \JarisCMS\URI\PrintURL($scripts_file);
            }
        }
    }

    sort($scripts);
    
    foreach($additional_scripts as $url)
    {
        $scripts[] = $url;
    }

    //Call GetScripts modules hook before returning data
    \JarisCMS\Module\Hook("System", "GetScripts", $scripts);

    return $scripts;
}

/**
 * Gets the current page meta tags or the default system ones
 * stored on the main settings file.
 *
 * @return string Meta tags html code for insertion on an html page.
 */
function GetPageMetaTags()
{
    $page_data = \JarisCMS\Page\GetData(\JarisCMS\URI\Get(), \JarisCMS\Language\GetCurrent());
    
    $page_data["description"] = \JarisCMS\Search\StripHTMLTags($page_data["description"]);
    $page_data["keywords"] = \JarisCMS\Search\StripHTMLTags($page_data["keywords"]);

    $meta_tags = false;

    $meta_tags = "<meta name=\"generator\" content=\"" . t("JarisCMS - Copyright JegoYalu.com. All rights reserved.") . "\" />\n";

    //Get description
    if($page_data["description"])
    {
        $meta_tags .= "<meta name=\"description\" content=\"{$page_data['description']}\" />\n";
    }

    //Get keywords
    if($page_data["keywords"])
    {
        $meta_tags .= "<meta name=\"keywords\" content=\"{$page_data['keywords']}\" />\n";
    }

    //Call get meta tags modules hook before returning data
    \JarisCMS\Module\Hook("System", "GetPageMetaTags", $meta_tags);

    return $meta_tags;
}

/**
 * Gets an array with a list of directories with sections marked as system ones.
 * Useful to know in what pages to block certain actions as editing task.
 *
 * @param string $check_path used to make a check with a given path to see if it is a
 *        system page or not.
 *
 * @return array|bool List of system sections or true, false if check_path is
 *         specified.
 */
function MakePagesBlacklist($check_path=null)
{
    $list = array();
    
    $list[] = \JarisCMS\Setting\GetDataDirectory() . "pages/sections/admin";
    $list[] = \JarisCMS\Setting\GetDataDirectory() . "pages/singles/s/se/search";
    $list[] = \JarisCMS\Setting\GetDataDirectory() . "pages/singles/a/ac/access-denied";
    $list[] = \JarisCMS\Setting\GetDataDirectory() . "pages/singles/h/h/home";
    $list[] = \JarisCMS\Setting\GetDataDirectory() . "pages/singles/u/us/user";

    //Call MakePagesBlacklist hook before returning data
    \JarisCMS\Module\Hook("System", "MakePagesBlacklist", $list);

    if($check_path)
    {
        foreach($list as $value)
        {
            $path = strtolower($check_path);
            $value = strtolower($value);

            if(strstr($path, $value))
            {
                return true;
            }
        }

        return false;
    }

    return $list;
}

/**
 * Check if a url exists
 * 
 * @param string $url The url to check.
 * 
 * @return bool True if exist otherwise false.
 */
function URLExists($url)
{
    $url = @parse_url($url);

    if (!$url)
    {
        return false;
    }

    $url = array_map('trim', $url);
    $url['port'] = (!isset($url['port'])) ? 80 : (int)$url['port'];
    $path = (isset($url['path'])) ? $url['path'] : '';

    if ($path == '')
    {
        $path = '/';
    }

    $path .= (isset($url['query'])) ? "?$url[query]" : '';

    if (PHP_VERSION >= 5)
    {
        $headers = get_headers("$url[scheme]://$url[host]:$url[port]$path");
    }
    else
    {
        $fp = fsockopen($url['host'], $url['port'], $errno, $errstr, 30);

        if (!$fp)
        {
            return false;
        }
        
        fputs($fp, "HEAD $path HTTP/1.1\r\nHost: $url[host]\r\n\r\n");
        $headers = fread($fp, 4096);
        fclose($fp);
    }
    
    $headers = (is_array($headers)) ? implode("\n", $headers) : $headers;
    
    return (bool)preg_match('#^HTTP/.*\s+[(200|301|302)]+\s#i', $headers);
}

/**
 * Check if jaris cms is currently installed and if not redirect to install page
 */
function CheckInstall()
{
    global $base_url;

    if($base_url == "http://localhost" && !file_exists(\JarisCMS\Setting\GetDataDirectory() . "settings/main.php"))
    {
        header("Location: http://" . $_SERVER["SERVER_NAME"] . str_replace("index.php", "install/install.php", $_SERVER["PHP_SELF"]));
        exit;
    }
}

/**
 * Checks if the site status is offline and redirect user to the offline status message page.
 */
function CheckOffline()
{
    $status = \JarisCMS\Setting\Get("site_status", "main");
    
    if(\JarisCMS\URI\Get() != "admin/user" && \JarisCMS\URI\Get() != "offline" && !\JarisCMS\Security\IsAdminLogged() && !$status)
    {
        GoToPage("offline");
    }
}

/**
 * Function that stores all neccesary error messages.
 *
 * @param string $type The type of error message to retrieve.
 *
 * @return string An error message already translated if available.
 */
function GetErrorMessage($type)
{
    switch($type)
    {
        case "write_error_data":
            return t("Check your write permissions on the data directory.");

        case "write_error_language":
            return t("Check your write permissions on the language directory.");

        case "translations_not_moved":
            return t("Translations could not be repositioned with the new uri. Check your write permissions on the language directory.");

        case "translations_not_deleted":
            return t("Translations could not be deleted. Check your write permissions on the language directory.");

        case "image_file_type":
            return t("The file type must be JPEG, PNG or GIF.");

        case "group_exist":
            return t("The group machine name is already in use.");

        case "delete_system_group":
            return t("This is a system group and can not be deleted.");

        case "edit_system_group":
            return t("This is a system group and its machine name can not be modified.");

        case "menu_exist":
            return t("The menu machine name is already in use.");

        case "type_exist":
            return t("The type machine name is already in use.");
            
        case "input_format_exist":
            return t("The input format machine name is already in use.");
            
        case "category_exist":
            return t("The category machine name is already in use.");

        case "delete_system_type":
            return t("This is a system type and can not be deleted.");

        case "user_exist":
            return t("The username is already in use.");
            
        case "user_not_exist":
            return t("Theres no user that match your criteria on the system.");

        default:
            return t("Operation could not be completed.");
    }
}

/**
 * Checks if the current page is a core system one.
 *
 * @param string $uri Optional parameter to indicate a specific page to check.
 *
 * @return bool True if system page false if not.
 */
function IsSystemPage($uri=false)
{
    $page = \JarisCMS\URI\Get();

    if($uri)
    {
        $page = $uri;
    }

    $data_path = \JarisCMS\Page\GeneratePath($page);

    $data = \JarisCMS\PHPDB\Parse($data_path . "/data.php");

    $is_system_page = trim($data[0]["is_system"]);

    //Call IsSystemPage hook before returning data
    \JarisCMS\Module\Hook("System", "IsSystemPage", $page, $is_system_page);

    unset($data);

    return $is_system_page;
}

/**
 * Stops php script execution and redirects to a new page.
 *
 * @param string $uri The page we are going to redirect.
 * @param array $arguments Arguments to pass to the url in te format $arguments["name"] = "value"
 */
function GoToPage($uri, $arguments = null)
{
    header("Location: " . \JarisCMS\URI\PrintURL($uri, $arguments));
    ob_clean();
    exit;
}

/**
 * Queues a tab to the array of tabs that is going to be displayed on the page
 * and can be accessed on the page template using the $tabs variable
 *
 * @param string $name The text used for user render.
 * @param string $uri The url of the tab when the user clicks it.
 * @param array $arguments The arguments to pass to the url.
 * @param integer $row poisition where rows will appear.
 *
 */
function AddTab($name, $uri, $arguments = null, $row=0)
{
    global $tabs_list;
    $tabs_list[$row][$name] = array("uri"=>$uri, "arguments"=>$arguments);
}


/**
 * Queues a message to the array of messages that is going to be displayed on
 * the page that can be accessed on the page template using the $messages var.
 *
 * @param string $message The text to display to on the page.
 * @param string $type Type of message can be: normal or error.
 *
 */
function AddMessage($message, $type = "normal")
{
    $_SESSION["messages"][] = array("text"=>$message, "type"=>$type);
}

/**
 * Generates Edit tab for pages when administrator is logged in.
 */
function AddEditTab()
{
    global $page_data;

    //Do not add edit tab to page not found
    $data_path = \JarisCMS\Page\GeneratePath(\JarisCMS\URI\Get()) . "/data.php";
    if(!file_exists($data_path))
    {
        return;
    }

    if(\JarisCMS\Group\GetPermission("edit_content", \JarisCMS\Security\GetCurrentUserGroup()) && !IsSystemPage())
    {
        $uri = \JarisCMS\URI\Get();
        if(\JarisCMS\Page\IsOwner($uri))
        {
            AddTab(t("Edit"), "admin/pages/edit", array("uri"=>$uri));
            AddTab(t("View"), $uri);
        }
    }
}

/**
 * Parses a string as actual php code using the eval function
 *
 * @param string $text The string to be parsed.
 *
 * @return string The evaluated output captured by ob_get_contents function.
 */
function PHPEval($text)
{
    //Prepares the text to be evaluated
    $text = trim($text, "\n\r\t\0\x0B ");

    ob_start();
        eval('?>' . $text);
        $content = ob_get_contents();
    ob_end_clean();
    
    return $content;
}

/**
 * Override the php default error reporting system.
 */
function InitErrorCatch()
{
    //Disabled for performance test
    //error_reporting(E_ALL ^ E_NOTICE);
    //set_error_handler("ErrorCatchHook");
}

/**
 * Catch the php errors and dysplay them as an error message
 */
function ErrorCatchHook($errno, $errmsg, $filename, $linenum, $vars)
{
    $errortype[E_ERROR]    = t('Error');
    $errortype[E_WARNING] = t('Warning');
     $errortype[E_PARSE] = t('Parsing Error');
    $errortype[E_NOTICE] = t('Notice');
    $errortype[E_CORE_ERROR] = t('Core Error');
    $errortype[E_CORE_WARNING] = t('Core Warning');
    $errortype[E_COMPILE_ERROR] = t('Compile Error');
     $errortype[E_COMPILE_WARNING] = t('Compile Warning');
      $errortype[E_USER_ERROR] = t('User Error');
    $errortype[E_USER_WARNING] = t('User Warning');
    $errortype[E_USER_NOTICE] = t('User Notice');
    $errortype[E_STRICT] = t('Runtime Notice');
     $errortype[E_RECOVERABLE_ERROR] = t('Catchable Fatal Error');          

    if($errno != E_NOTICE && $errno != E_WARNING && $errno != E_STRICT)
    {
        AddMessage("<b>" . $errortype[$errno] . "</b> - $errmsg" .
        "  " . t("in") . " $filename " . t("on line") . " $linenum", "error");
     }

    /* Don't execute PHP internal error handler */
    return true;
}

function GenerateAdminPageSection()
{
    $group = \JarisCMS\Security\GetCurrentUserGroup();
    $sections = array();

    //Content
    if(\JarisCMS\Group\GetPermission("add_content", $group))
    {
        $content[] = array("title"=>t("Add"), "url"=>\JarisCMS\URI\PrintURL("admin/pages/types"), "description"=>t("Create new content."));
    }
    
    if(\JarisCMS\Group\GetPermission("view_content", $group))
    {
        $content[] = array("title"=>t("Navigate"), "url"=>\JarisCMS\URI\PrintURL("admin/pages"), "description"=>t("View and edit existing content."));
    }

    if(\JarisCMS\Group\GetPermission("add_types", $group))
    {
        $content[] = array("title"=>t("Add Type"), "url"=>\JarisCMS\URI\PrintURL("admin/types/add"), "description"=>t("Create new content type."));
    }

    if(\JarisCMS\Group\GetPermission("view_types", $group))
    {
        $content[] = array("title"=>t("Manage Types"), "url"=>\JarisCMS\URI\PrintURL("admin/types"), "description"=>t("View and edit existing content types."));
    }
    
    if(\JarisCMS\Group\GetPermission("add_input_formats", $group))
    {
        $content[] = array("title"=>t("Add Input Format"), "url"=>\JarisCMS\URI\PrintURL("admin/input-formats/add"), "description"=>t("Create new content input format."));
    }

    if(\JarisCMS\Group\GetPermission("view_input_formats", $group))
    {
        $content[] = array("title"=>t("Manage Input Formats"), "url"=>\JarisCMS\URI\PrintURL("admin/input-formats"), "description"=>t("View and edit existing content input formats."));
    }
    
    if(\JarisCMS\Group\GetPermission("add_categories", $group))
    {
        $content[] = array("title"=>t("Add Category"), "url"=>\JarisCMS\URI\PrintURL("admin/categories/add"), "description"=>t("Create new content categories."));
    }

    if(\JarisCMS\Group\GetPermission("view_categories", $group))
    {
        $content[] = array("title"=>t("Manage Categories"), "url"=>\JarisCMS\URI\PrintURL("admin/categories"), "description"=>t("View and edit existing content categories."));
    }

    if($content)
    {
        $sections[] = array("class"=>"content", "title"=>t("Content"), "sub_sections"=>$content);
    }

    //Blocks
    if(\JarisCMS\Group\GetPermission("add_blocks", $group))
    {
        $blocks[] = array("title"=>t("Add"), "url"=>\JarisCMS\URI\PrintURL("admin/blocks/add"), "description"=>t("Create new blocks."));
    }
    
    if(\JarisCMS\Group\GetPermission("view_blocks", $group))
    {
        $blocks[] = array("title"=>t("Manage"), "url"=>\JarisCMS\URI\PrintURL("admin/blocks"), "description"=>t("View and edit existing blocks."));
    }

    if($blocks)
    {
        $sections[] = array("class"=>"blocks", "title"=>t("Blocks"), "sub_sections"=>$blocks);
    }

    //Menus
    if(\JarisCMS\Group\GetPermission("add_menus", $group))
    {
        $menus[] = array("title"=>t("Add"), "url"=>\JarisCMS\URI\PrintURL("admin/menus/add"), "description"=>t("Create new menu."));
    }
    
    if(\JarisCMS\Group\GetPermission("view_menus", $group))
    {
        $menus[] = array("title"=>t("Manage"), "url"=>\JarisCMS\URI\PrintURL("admin/menus"), "description"=>t("View and edit existing menus and its menu items."));
    }

    if($menus)
    {
        $sections[] = array("class"=>"menus", "title"=>t("Menus"), "sub_sections"=>$menus);
    }

    //Users
    if(\JarisCMS\Group\GetPermission("add_users", $group))
    {
        $users[] = array("title"=>t("Add"), "url"=>\JarisCMS\URI\PrintURL("admin/users/add"), "description"=>t("Create new user."));
    }
    
    if(\JarisCMS\Group\GetPermission("view_users", $group))
    {
        $users[] = array("title"=>t("Manage"), "url"=>\JarisCMS\URI\PrintURL("admin/users"), "description"=>t("View and edit existing users."));
    }
    
    if($users)
    {
        $sections[] = array("class"=>"users", "title"=>t("Users"), "sub_sections"=>$users);
    }
        
    //Groups
    if(\JarisCMS\Group\GetPermission("add_groups", $group))
    {
        $groups[] = array("title"=>t("Add"), "url"=>\JarisCMS\URI\PrintURL("admin/groups/add"), "description"=>t("Create new group."));
    }

    if(\JarisCMS\Group\GetPermission("view_groups", $group))
    {
        $groups[] = array("title"=>t("Manage"), "url"=>\JarisCMS\URI\PrintURL("admin/groups"), "description"=>t("View and edit existing groups."));
    }

    if($groups)
    {
        $sections[] = array("class"=>"groups", "title"=>t("Groups"), "sub_sections"=>$groups);
    }

    //Settings
    if(\JarisCMS\Group\GetPermission("edit_settings", $group))
    {
        $settings[] = array("title"=>t("Manage"), "url"=>\JarisCMS\URI\PrintURL("admin/settings"), "description"=>t("Modify site settings."));
    }
    
    if(\JarisCMS\Group\GetPermission("edit_settings", $group))
    {
        $settings[] = array("title"=>t("Search Engine"), "url"=>\JarisCMS\URI\PrintURL("admin/settings/search"), "description"=>t("Change the settings of the search page."));
    }

    if(\JarisCMS\Group\GetPermission("select_theme", $group))
    {
        $settings[] = array("title"=>t("Theme"), "url"=>\JarisCMS\URI\PrintURL("admin/themes"), "description"=>t("View and choose site theme."));
    }
    
    if(\JarisCMS\Group\GetPermission("edit_settings", $group))
    {
        $settings[] = array("title"=>t("About JarisCMS"), "url"=>\JarisCMS\URI\PrintURL("admin/settings/about"), "description"=>t("View current jaris version and developer information."));
    }

    if($settings)
    {
        $sections[] = array("class"=>"settings", "title"=>t("Settings"), "sub_sections"=>$settings);
    }

    //Languages
    if(\JarisCMS\Group\GetPermission("add_languages", $group))
    {
        $language[] = array("title"=>t("Add"), "url"=>\JarisCMS\URI\PrintURL("admin/languages/add"), "description"=>t("Add another language to the system."));
    }
    
    if(\JarisCMS\Group\GetPermission("view_languages", $group))
    {
        $language[] = array("title"=>t("Manage"), "url"=>\JarisCMS\URI\PrintURL("admin/languages"), "description"=>t("Manage available languages on the system."));
    }

    if($language)
    {
        $sections[] = array("class"=>"languages", "title"=>t("Languages"), "sub_sections"=>$language);
    }

    //Modules
    if(\JarisCMS\Group\GetPermission("view_modules", $group))
    {
        $modules[] = array("title"=>t("Manage"), "url"=>\JarisCMS\URI\PrintURL("admin/modules"), "description"=>t("Install or uninstall modules to the system."));

        $sections[] = array("class"=>"modules", "title"=>t("Modules"), "sub_sections"=>$modules);
    }
    
    return $sections;
}

/**
 * Function that generates/prints the html for the administration page.
 *
 * @param array $sections In the format sections[] =
 *                 array(
 *                        "class"=>"css class",
 *                        "title"=>"string",
 *                        "sub_sections"[]=>
 *                            array(
 *                                "title"=>"string",
 *                                "description"=>"string",
 *                                "url"=>"string"
 *                            )
 *                    )
 */
function GenerateAdminPage($sections)
{
    //Call GenerateAdminPage hook before generating sections
    \JarisCMS\Module\Hook("System", "GenerateAdminPage", $sections);
    
    if(count($sections) <= 0)
    {
        AddMessage("No task assigned to you on the control center.");
        GoToPage("admin/user");
    }
    
    $html = "<div class=\"administration-list\">\n";

    foreach($sections as $section_details)
    {
        $html .= "<div class=\"section section-{$section_details['class']}\">\n";
        $html .= "<h2 class=\"section-title\">{$section_details['title']}</h2>\n";
        $html .= "<div class=\"section-content\">\n";

        if(count($section_details["sub_sections"]) > 0)
        {
            foreach($section_details["sub_sections"] as $fields)
            {
                $html .= "<div class=\"subsection-title\">\n";
                $html .= "<a href=\"{$fields['url']}\">{$fields['title']}</a>\n";
                $html .= "</div>\n";
    
                $html .= "<div class=\"description\">\n";
                $html .= "{$fields['description']}\n";
                $html .= "</div>\n";
            }
        }
        
        $html .= "</div>\n";
        $html .= "</div>\n";
    }

    $html .= "</div>\n";

    print $html;
}

/**
 * Checks what browser the visitor is using.
 * 
 * @return string Value could be ie, firefox, chrome, safari, opera or other.
 */
function GetUserBrowser()
{
    if("" . stristr($_SERVER['HTTP_USER_AGENT'], "MSIE") . "" != "")
    {
        return "ie";
    }
    else if("" . stristr($_SERVER['HTTP_USER_AGENT'], "Firefox") . "" != "")
    {
        return "firefox";
    }
    else if("" . stristr($_SERVER['HTTP_USER_AGENT'], "Chrome") . "" != "")
    {
        return "chrome";
    }
    else if("" . stristr($_SERVER['HTTP_USER_AGENT'], "Safari") . "" != "")
    {
        return "safari";
    }
    else if("" . stristr($_SERVER['HTTP_USER_AGENT'], "Opera") . "" != "")
    {
        return "opera";
    }
    else
    {
        return "other";
    }
}

/**
 * Prints a generaic navigation bar for any kind of results
 * 
 * @param integer $total_count The total amount of results.
 * @param integer $page The actual page number displaying results.
 * @param string $uri The uri used on navigation bar links.
 * @param string $module Optional module name to generate uri.
 * @param integer $amount Optional amount of results to display per page, Default: 30
 * @param array $arguments Optional arguments to pass to the navigation links.
 */
function PrintGenericNavigation($total_count, $page, $uri, $module="", $amount=30, $arguments=array())
{
    $page_count = 0;
    $remainder_pages = 0;

    if($total_count <= $amount)
    {
        $page_count = 1;
    }
    else
    {
        $page_count = floor($total_count / $amount);
        $remainder_pages = $total_count % $amount;

        if($remainder_pages > 0)
        {
            $page_count++;
        }
    }

    //In case someone is trying a page out of range or not print if only one page
    if($page > $page_count || $page < 0 || $page_count == 1)
    {
        return false;
    }
    
    print "<div class=\"search-results\">\n";
    print "<div class=\"navigation\">\n";
    if($page != 1)
    {
        $arguments["page"] = $page - 1;
        $previous_page = \JarisCMS\URI\PrintURL(\JarisCMS\Module\GetPageURI($uri, $module), $arguments);
        $previous_text = t("Previous");
        print "<a class=\"previous\" href=\"$previous_page\">$previous_text</a>";
    }

    $start_page = $page;
    $end_page = $page + 10;

    for($start_page; $start_page < $end_page && $start_page <= $page_count; $start_page++)
    {
        $text = t($start_page);

        if($start_page > $page || $start_page < $page)
        {
            $arguments["page"] = $start_page;
            $url = \JarisCMS\URI\PrintURL(\JarisCMS\Module\GetPageURI($uri, $module), $arguments);
            print "<a class=\"page\" href=\"$url\">$text</a>";
        }
        else
        {
            print "<a class=\"current-page page\">$text</a>";
        }
    }

    if($page < $page_count)
    {
        $arguments["page"] = $page + 1;
        $next_page = \JarisCMS\URI\PrintURL(\JarisCMS\Module\GetPageURI($uri, $module), $arguments);
        $next_text = t("Next");
        print "<a class=\"next\" href=\"$next_page\">$next_text</a>";
    }
    print "</div>\n";
    print "</div>\n";
}

/**
 * To generate a breadcrumb using the available path sections on a uri.
 * 
 * @param string $separator The sections separator.
 * 
 * @return string|bool Breadcrumb html or false if a path section doesn't exists.
 */
function PrintBreadcrumb($separator = "&gt;")
{
    $paths = explode("/", \JarisCMS\URI\Get());
    
    $breadcrumb = "";
    
    $loop_count = 1;
    $paths_count = count($paths); 
    $paths_implode = "";
    $found_sections = 0;
    
    if($paths_count > 1)
    {
        foreach($paths as $path)
        {
            $page_data = \JarisCMS\Page\GetData($paths_implode . $path, \JarisCMS\Language\GetCurrent());
            
            if(is_array($page_data))
            {
                if($loop_count < $paths_count)
                {
                    $breadcrumb .= "<a href=\"" . \JarisCMS\URI\PrintURL($paths_implode . $path) . "\">" . PHPEval($page_data['title']) . "</a> &gt; ";
                }
                else
                {
                    $breadcrumb .= "<span class=\"current\">" . PHPEval($page_data['title']) . "</span>";
                }
                
                $found_sections++;
            }
            
            $paths_implode .= $path . "/";
            $loop_count++;
        }
    }
    
    \JarisCMS\Module\Hook("System", "PrintBreadcrumb", $breadcrumb, $found_sections);
    
    if($found_sections <= 1)
    {
        return false;
    }
    else
    {
        AddHiddenURLParams($_GET);
    }
    
    return $breadcrumb;
}

/**
 * Helper function for breadcrumbs to store current url parameters.
 * 
 * @param array $parameters Array of parameters array("parameter_name"=>"value")
 */
function AddHiddenURLParams($parameters)
{
    if(is_array($parameters) && count($parameters) > 0)
    {
        foreach($parameters as $name=>$value)
        {
            if($name != "p")
            {
                $_SESSION["hidden_parameters"][$name] = $value;
            }
        }
    }
}

/**
 * Breadcrumbs function assitant that should be called on jariscms initialization to
 * append hidden url parameters to $_REQUEST variable.
 */
function AppendHiddenParams()
{
    //Only execute if current breadcrumb generation is valid
    if(PrintBreadcrumb())
    {
        if(isset($_SESSION["hidden_parameters"]))
        {
            foreach($_SESSION["hidden_parameters"] as $name=>$value)
            {
                $_REQUEST[$name] = $value;
            } 
        }
        
        unset($_SESSION["hidden_parameters"]);
    }
}

/**
 * Function that returns a dates of the month array ready for selects on generate form functions.
 */
function GetDatesArray()
{
    $dates = array();
    
    for($i=1; $i<=31; $i++)
    {
        $dates[$i] = $i;
    }
    
    return $dates;
}

/**
 * Function that returns a months array ready for selects on generate form functions.
 */
function GetMonthsArray()
{
    $months[t("January")] = 1;
    $months[t("February")] = 2;
    $months[t("March")] = 3;
    $months[t("April")] = 4;
    $months[t("May")] = 5;
    $months[t("June")] = 6;
    $months[t("July")] = 7;
    $months[t("August")] = 8;
    $months[t("September")] = 9;
    $months[t("October")] = 10;
    $months[t("November")] = 11;
    $months[t("December")] = 12;
    
    return $months;
}

/**
 * Function that returns a years array ready for selects on generate form functions.
 */
function GetYearsArray()
{
    $current_year = date("Y", time());
    $years = array();
    
    for($i=1900; $i<=$current_year; $i++)
    {
        $years[$i] = $i;
    }
    
    arsort($years);
    
    return $years;
}

/**
 * To print just part of a string and strip its html tags.
 *
 * @param string $string The string to print.
 * @param integer $word_count The amount of words to print of it.
 * @param bool $display_suspensive_points Flag to display 3 dots on the end of preview.
 *
 * @return string The trimmed string.
 */
function PrintContentPreview($string, $word_count=30, $display_suspensive_points = false)
{
   $string = \JarisCMS\Search\StripHTMLTags($string);
   
   $string = preg_replace("/(&nbsp;)*/i", "", $string);
   
   $string_array = explode(" ", $string);
   
   $string_count = count($string_array);
   
   $string = "";
   
   for($i=0; $i<$word_count && $i<=$string_count; $i++)
   {
        $string .= $string_array[$i] . " ";
   }

   $string = trim($string);
   
   //If last character is not a point add points to it.
   if($display_suspensive_points && $string != "" && $string{strlen($string)-1} != ".")
   {
        $string .= " ...";
   }
   
   return $string;
}

/**
 * Check if page cache expired and if not display the cached page.
 * 
 * @param string $uri The uri of the page to check.
 * @param array $page_data The actual data of the page to check. 
 */
function CachePageIfPossible($uri, $page_data)
{
    if(!$page_data["is_system"] && !\JarisCMS\Security\IsUserLogged() && \JarisCMS\Setting\Get("enable_cache", "main"))
    {
        //Skip administrator selected pages types
        $types_to_ignore = unserialize(\JarisCMS\Setting\Get("cache_ignore_types", "main"));
        if(is_array($types_to_ignore))
        {
            foreach($types_to_ignore as $type_name)
            {
                if($type_name == $page_data["type"])
                {
                    return;
                }
            }
        }
            
        if($page_data["input_format"] == "php_code" && !\JarisCMS\Setting\Get("cache_php_pages", "main"))
        {
            return;
        }
        
        if(!\JarisCMS\SQLite\DBExists("cache"))
        {
            $db = \JarisCMS\SQLite\Open("cache");
            
            $query = "create table last_change (id int primary key, value text)";
            \JarisCMS\SQLite\Query($query, $db);
            
            $query = "insert into last_change (id, value) values(1, '" . time() . "')";
            \JarisCMS\SQLite\Query($query, $db);
            
            \JarisCMS\SQLite\Close($db);
        }
        
        $db = \JarisCMS\SQLite\Open("cache");
        $file_updated = false;
        $times_string = "";
        
        //Check sqlite directory and bypass check on cache and search_engine database
        $databases = \JarisCMS\FileSystem\GetFiles(\JarisCMS\Setting\GetDataDirectory() . "sqlite");
        foreach($databases as $path)
        {
            //Skip administrator selected ignored databases
            $databases_to_ignore = unserialize(\JarisCMS\Setting\Get("cache_ignore_db", "main"));
            if(is_array($databases_to_ignore))
            {
                foreach($databases_to_ignore as $db_name)
                {
                    $full_db_path = \JarisCMS\Setting\GetDataDirectory() . "sqlite/" . $db_name;
                    if($path == $full_db_path)
                    {
                        continue 2;
                    }
                }
            }
            
            if("" . strpos($path, "sqlite/cache") . "" == "" && 
               "" . strpos($path, "sqlite/search_engine") . "" == "" &&
               "" . strpos($path, "sqlite/users") . "" == "")
            {
                $times_string .= filemtime($path);
            }
        }
        
        //Check the other system directories for changes
        $blocks[] = \JarisCMS\Setting\GetDataDirectory() . "blocks";
        $categories[] = \JarisCMS\Setting\GetDataDirectory() . "categories";
        $cache_events[] = \JarisCMS\Setting\GetDataDirectory() . "cache_events";
        $groups[] = \JarisCMS\Setting\GetDataDirectory() . "groups";
        $language[] = \JarisCMS\Setting\GetDataDirectory() . "language";
        $menus[] = \JarisCMS\Setting\GetDataDirectory() . "menus";
        $modules[] = \JarisCMS\Setting\GetDataDirectory() . "modules";
        $settings[] = \JarisCMS\Setting\GetDataDirectory() . "settings";
        $types[] = \JarisCMS\Setting\GetDataDirectory() . "types";
        
        $all_directories = array_merge($blocks, $categories, $cache_events, $groups, $language, $menus, $modules, $settings, $types);
        
        foreach($all_directories as $path)
        {
            $times_string .= filemtime($path);
        }
        
        //Calculate times md5
        $times_string = md5($times_string);
        
        //Obtain current timestamp
        $select = "select value from last_change where id=1";
        $result = \JarisCMS\SQLite\Query($select, $db);
        $last_change_data = \JarisCMS\SQLite\FetchArray($result);
        $current_time = $last_change_data["value"];
        
        if($current_time != $times_string)
        {
            $file_updated = true;
        }
        
        //Create new timestamp in case a file was updated
        $new_time = time();
        
        if($file_updated)
        {
            //Update las change timestamp
            $update = "update last_change set 
            value='$times_string' where id=1";
            \JarisCMS\SQLite\Query($update, $db);
                        
            //Create cache directory if not exists
            if(!file_exists(\JarisCMS\Setting\GetDataDirectory() . "cache/"))
            {
                \JarisCMS\FileSystem\MakeDir(\JarisCMS\Setting\GetDataDirectory() . "cache/");
            }
            
            $current_time = $times_string;
        }
        
        \JarisCMS\SQLite\Close($db);
        
        $cache_file = \JarisCMS\Setting\GetDataDirectory() . "cache/" . \JarisCMS\URI\FromText($uri) . \JarisCMS\Language\GetCurrent();
        $cache_time_file = \JarisCMS\Setting\GetDataDirectory() . "cache/" . \JarisCMS\URI\FromText($uri) . \JarisCMS\Language\GetCurrent() . ".time";
        
        if(file_exists($cache_file))
        {
            $cache_data = \JarisCMS\PHPDB\GetData(0, $cache_time_file);
            $page_path = \JarisCMS\Page\GeneratePath($uri) . "/data.php";
            $page_time = filemtime($page_path);
        
            if($cache_data["time"] == $current_time && $cache_data["page_time"] == $page_time)
            {
                \JarisCMS\Page\CountView($uri);
                
                $file_content = file_get_contents($cache_file);
                
                print $file_content;
                
                if(\JarisCMS\Setting\Get("view_script_stats", "main"))
                {
                    global $time_start;
                    
                    print "<div style=\"clear: both\">";
                    print "<div style=\"width: 90%; border: solid #f0b656 1px; background-color: #d0dde7; margin: 0 auto 0 auto; padding: 10px\">";
                    print "<b>Script execution time:</b> " . ceil((microtime(true) - $time_start) * 1000) . " milliseconds<br />";
                
                    print "<b>Peak memory usage:</b> " . number_format(memory_get_peak_usage() / 1024 / 1024, 0, '.', ',') . " MB<br />";
                    print "<b>Final memory usage:</b> " . number_format(memory_get_usage() / 1024 / 1024, 0, '.', ',') . " MB<br />";
                    print "<b>Page retrieved from:</b> cache <br />";
                    print "</div>";
                }
                exit;
            }
        }
    }
}

/**
 * If cache is enabled creates a cache file for a given page for later fast retreival.
 * 
 * @param string $uri The uri of the page to store.
 * @param array $page_data The actual data of the page to store.
 * @param string $content The html output of the page to store.
 */
function SavePageToCacheIfPossible($uri, $page_data, $content)
{
    $page_path = \JarisCMS\Page\GeneratePath($uri) . "/data.php";
    
    //Skip visual uris
    if(!file_exists($page_path))
    {
        return;
    }
    
    if(!IsSystemPage($uri) && !\JarisCMS\Security\IsUserLogged() && \JarisCMS\Setting\Get("enable_cache", "main"))
    {
        //Skip administrator selected pages types
        $types_to_ignore = unserialize(\JarisCMS\Setting\Get("cache_ignore_types", "main"));
        if(is_array($types_to_ignore))
        {
            foreach($types_to_ignore as $type_name)
            {
                if($type_name == $page_data["type"])
                {
                    return;
                }
            }
        }
        
        if($page_data["input_format"] == "php_code" && !\JarisCMS\Setting\Get("cache_php_pages", "main"))
        {
            return;
        }
        
        $cache_file = \JarisCMS\Setting\GetDataDirectory() . "cache/" . \JarisCMS\URI\FromText($uri) . \JarisCMS\Language\GetCurrent();
        $cache_time_file = \JarisCMS\Setting\GetDataDirectory() . "cache/" . \JarisCMS\URI\FromText($uri) . \JarisCMS\Language\GetCurrent() . ".time";
        
        $db = \JarisCMS\SQLite\Open("cache");
        $select = "select value from last_change where id=1";
        $result = \JarisCMS\SQLite\Query($select, $db);
        $data = \JarisCMS\SQLite\FetchArray($result);
        \JarisCMS\SQLite\Close($db);
        
        file_put_contents($cache_file, $content);
        
        $page_time = filemtime($page_path);
        
        $fields["time"] = $data["value"];
        $fields["page_time"] = $page_time;
        
        \JarisCMS\PHPDB\Edit(0, $fields, $cache_time_file);
    }    
}

?>
