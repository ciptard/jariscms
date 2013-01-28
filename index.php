<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file The main execution entry point of Jaris CMS.
 */

//Time when script started executing useful to count page execution time on the theme.
$time_start = microtime(true);

//Starts the main session for the user
session_start();

//File that includes all neccesary system functions
include("include/all_includes.php");

//Increase the time for session to garbage collection
ini_set("session.gc_maxlifetime", "18000"); 

//Initialize error handler
JarisCMS\System\InitErrorCatch();

//Overrides configurations variables on settings.php if needed.
JarisCMS\Setting\Override();

//Check if cms is run for the first time and run the installer
JarisCMS\System\CheckInstall();

//Check if site status is online to continue
JarisCMS\System\CheckOffline();

//Sets the page that is going to be displayed
$page = JarisCMS\URI\Get();

//Stores the uri that end users will see even if the $page is changed by JarisCMS\Category\ShowResults() sinces template functions need the visual uri not the content uri
$visual_uri = JarisCMS\URI\Get();

//Skips all the data procesing if image or file and display it.
$page_type = JarisCMS\URI\GetType($page);
if($page_type == "image")
{
    $image_path = JarisCMS\URI\GetImagePath($page);
    JarisCMS\Image\Show($image_path);
}
elseif($page_type == "user_picture")
{
    JarisCMS\Image\PrintAvatar($page);
}
elseif($page_type == "user_profile")
{
    JarisCMS\User\ShowProfile($page);
}
elseif($page_type == "file")
{
    JarisCMS\FileSystem\PrintFile($page);
}
elseif($page_type == "category")
{
    JarisCMS\Category\ShowResults($page);
}

//Sets the language based on user selection or system default
$language = JarisCMS\Language\GetCurrent();

//Call initialization hooks so modules can make things before page is rendered
JarisCMS\Module\Hook("System", "Initialization");

//Read page data
$page_data[0] = JarisCMS\Page\GetData($page, $language);

//Call page data hooks so modules can modify page_data content
JarisCMS\Module\Hook("System", "GetPageData", $page_data);

//Check if page is cacheable and return cache if possible for performance
JarisCMS\System\CachePageIfPossible($page, $page_data[0]);

//Check if the current user can view the current content
if(!JarisCMS\Page\UserAccess($page_data[0]))
{
    JarisCMS\System\GoToPage("access-denied");
}

//Append hiden parameters to $_REQUEST for the correct execution of breadcrumbs
JarisCMS\System\AppendHiddenParams();

//Read all the specific page data
$header_data = JarisCMS\PHPDB\Sort(JarisCMS\PHPDB\Parse(dt(JarisCMS\Setting\GetDataDirectory() . "blocks/header.php")), "order");
$footer_data = JarisCMS\PHPDB\Sort(JarisCMS\PHPDB\Parse(dt(JarisCMS\Setting\GetDataDirectory() . "blocks/footer.php")), "order");
$left_data = JarisCMS\PHPDB\Sort(JarisCMS\PHPDB\Parse(dt(JarisCMS\Setting\GetDataDirectory() . "blocks/left.php")), "order");
$right_data = JarisCMS\PHPDB\Sort(JarisCMS\PHPDB\Parse(dt(JarisCMS\Setting\GetDataDirectory() . "blocks/right.php")), "order");
$center_data = JarisCMS\PHPDB\Sort(JarisCMS\PHPDB\Parse(dt(JarisCMS\Setting\GetDataDirectory() . "blocks/center.php")), "order");
$primary_links_data = JarisCMS\PHPDB\Sort(JarisCMS\Menu\GetSubItems(JarisCMS\Menu\GetPrimaryName()), "order");
$secondary_links_data = JarisCMS\PHPDB\Sort(JarisCMS\Menu\GetSubItems(JarisCMS\Menu\GetSecondaryName()), "order");

//Move blocks to other positions depending on current theme
JarisCMS\Block\MoveByTheme($header_data, $left_data, $right_data, $center_data, $footer_data);

//In case of page not found
if(!$page_data[0])
{
    $page_data = JarisCMS\System\MakePageNotFound();
}

//Format Data
$content = JarisCMS\Theme\MakeContent($page_data, $visual_uri);
$header = JarisCMS\Theme\MakeBlocks($header_data, "header", $visual_uri);
$footer = JarisCMS\Theme\MakeBlocks($footer_data, "footer", $visual_uri);
$left = JarisCMS\Theme\MakeBlocks($left_data, "left", $visual_uri);
$right = JarisCMS\Theme\MakeBlocks($right_data, "right", $visual_uri);
$center = JarisCMS\Theme\MakeBlocks($center_data, "center", $visual_uri);
$primary_links = JarisCMS\Theme\MakeLinks($primary_links_data, "primary-links");
$secondary_links = JarisCMS\Theme\MakeLinks($secondary_links_data, "secondary-links");

//Adds edit link on every page if administrator is logged.
JarisCMS\System\AddEditTab();

//Set the page title
if(JarisCMS\System\IsSystemPage() || ($page_type == "category" && $page == "search") || $page == "user") 
{
    //Parse the title if is system page
    $title = t(JarisCMS\System\PHPEval($page_data[0]["title"])) . " - " . t($title);
}
else
{
    //Just translate if not system page
    if(trim($page_data["meta_title"]) != "")
    {
        //If meta title is available use it
        $title = t($page_data[0]["meta_title"]);
    }
    else
    {
        $title = t($page_data[0]["title"]) . " - " . t($title);
    }
}

//Display Page and generate cache if enabled and possible
$page_html = JarisCMS\Theme\Display($visual_uri, $content, $left, $center, $right, $header, $footer);
JarisCMS\System\SavePageToCacheIfPossible($page, $page_data[0], $page_html);
print $page_html;

if(JarisCMS\Setting\Get("view_script_stats", "main"))
{
    print "<div style=\"clear: both\">";
    print "<div style=\"width: 90%; border: solid #f0b656 1px; background-color: #d0dde7; margin: 0 auto 0 auto; padding: 10px\">";
    print "<b>Script execution time:</b> " . ceil((microtime(true) - $time_start) * 1000) . " milliseconds<br />";

    print "<b>Peak memory usage:</b> " . number_format(memory_get_peak_usage() / 1024 / 1024, 0, '.', ',') . " MB<br />";
    print "<b>Final memory usage:</b> " . number_format(memory_get_usage() / 1024 / 1024, 0, '.', ',') . " MB<br />";
    print "</div>";
}
?>