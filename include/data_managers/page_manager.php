<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Functions related to the management of the file system based database
 *
 */

namespace JarisCMS\Page;

/**
 * Creates a page data directory.
 *
 * @param string $page The uri of the page to create, example: mysection/mypage
 * @param array $data An array of data to store to the page in the format:
 *             data = array("title"=>"value", "content"=>value)
 * @param string $uri Reference to return the page uri in case it was renamed because
 *       it already exist.
 *
 * @return bool True on success or false on fail.
 *
 * @note if the given uri to create the page already exist is automatically
 *      renamed, example: section/home (already exist) renamed to section/home-0
*/
function Create($page, $data, &$uri)
{
    $page = trim($page);
    if($page == "")
    {
        return false;
    }
    
    $path = GeneratePath($page);
    $path = \JarisCMS\FileSystem\Rename($path);

    //Returns the page uri to the argument reference.
    $uri = \JarisCMS\FileSystem\GetURIFromPath(str_replace(\JarisCMS\Setting\GetDataDirectory() . "pages/", "", $path));

    \JarisCMS\FileSystem\MakeDir($path, 0755, true);
    \JarisCMS\FileSystem\MakeDir($path . "/files", 0755, true);
    \JarisCMS\FileSystem\MakeDir($path . "/images", 0755, true);
    \JarisCMS\FileSystem\MakeDir($path . "/blocks", 0755, true);

    //Call create_page hook before creating the page
    \JarisCMS\Module\Hook("Page", "Create", $uri, $data, $path);
    
    $data["groups"] = serialize($data["groups"]);
    $data["categories"] = serialize($data["categories"]);

    if(\JarisCMS\PHPDB\Add($data, $path . "/data.php"))
    {
        //In case a module is installing a system page skip it of the database
        if(!$data["is_system"])
        {
            AddURISQLite($uri, $data);
            
            //Update cache_events folder
            if(!file_exists(\JarisCMS\Setting\GetDataDirectory() . "cache_events"))
            {
                \JarisCMS\FileSystem\MakeDir(\JarisCMS\Setting\GetDataDirectory() . "cache_events");
            }
            file_put_contents(\JarisCMS\Setting\GetDataDirectory() . "cache_events/new_page", "");
        }
        
        return true;
    }
    
    return false;
}

/**
 * Deletes a page data directory.
 *
 * @param string $page The uri of the page to delete, example: mysection/mypage
 *
 * @return bool True on success or false on fail.
 */
function Delete($page)
{
    $page = trim($page);
    if($page == "")
    {
        return false;
    }
    
    $page_path = GeneratePath($page);
    
    //Call Delete hook before deleting the page
    \JarisCMS\Module\Hook("Page", "Delete", $page, $page_path);

    //Clears the page directory to be able to delete it
    if(!\JarisCMS\FileSystem\RemoveDirRecursively($page_path, true))
    {
        return false;
    }

    RemoveEmptyDirs($page_path);
    
    RemoveURISQLite($page);

    return true;
}

/**
 * Modifies the data of a page.
 *
 * @param string $page The uri of the page to modify.
 * @param array $new_data Array of new data in the format:
 *       $data = array("title"=>value, "content"=>value)
 *
 * @return bool True on success or false on fail.
 */
function Edit($page, $new_data)
{
    $page = trim($page);
    if($page == "")
    {
        return false;
    }
    
    $page_path = GeneratePath($page);

    //Call edit_page_data hook before editing the page
    \JarisCMS\Module\Hook("Page", "Edit", $page, $new_data, $page_path);
    
    $new_data["groups"] = serialize($new_data["groups"]);
    $new_data["categories"] = serialize($new_data["categories"]);

    if(\JarisCMS\PHPDB\Edit(0, $new_data, $page_path . "/data.php"))
    {        
        EditDataURISQLite($page, $new_data);
        
        return true;
    }
    
    return false;
}

/**
 * Count a view to a page.
 *
 * @param string $page The uri of the page to count a view.
 *
 * @return string The total count of page views.
 */
function CountView($page)
{   
    if(!\JarisCMS\System\IsSystemPage($page))
    {
        $file_path = GeneratePath($page);
        if(file_exists($file_path))
        {
            $file_path .= "/views.php";
            $views_data = \JarisCMS\PHPDB\GetData(0, $file_path);
            
            if(!$views_data["views"])
            {
                $views_data = null;
                $views_data["views"] = 0;
            }
            
            $search_database_views = null;
            if(\JarisCMS\SQLite\DBExists("search_engine"))
            {
                $db = \JarisCMS\SQLite\Open("search_engine");
                \JarisCMS\SQLite\Turbo($db);
                
                $select = "select * from uris where uri='" . str_replace("'", "''", $page) . "'";
                
                $result = \JarisCMS\SQLite\Query($select, $db);
                
                if($data = \JarisCMS\SQLite\FetchArray($result))
                {
                    $search_database_views = $data["views"]+1;
                    
                    $current_day = date("j", time());
                    $current_week = date("W", time());
                    $current_month = date("n", time());
                    
                    if($data["views_day"] != $current_day)
                    {
                        $day_set = "views_day=$current_day, views_day_count=1";
                    }
                    else
                    {
                        $day_set = "views_day_count=views_day_count+1";
                    }
                    
                    if($data["views_week"] != $current_week)
                    {
                        $week_set = "views_week=$current_week, views_week_count=1";
                    }
                    else
                    {
                        $week_set = "views_week_count=views_week_count+1";
                    }
                    
                    if($data["views_month"] != $current_month)
                    {
                        $month_set = "views_month=$current_month, views_month_count=1";
                    }
                    else
                    {
                        $month_set = "views_month_count=views_month_count+1";
                    }
                    
                    $update = "update uris set views=views+1, $day_set, $week_set, $month_set where uri='" . str_replace("'", "''", $page) . "'";
                
                    \JarisCMS\SQLite\Query($update, $db);
                }
                
                \JarisCMS\SQLite\Close($db);
            }
            
            //In case search_engine database views values is most up to date than views.php file
            if($search_database_views && $search_database_views >= $views_data["views"])
            {
                $views_data["views"] = $search_database_views;
            }
            
            //In case the search database was rindexed use original views value
            else
            {
                 $views_data["views"]++;
            }
            
            \JarisCMS\PHPDB\Edit(0, $views_data, $file_path);
            
            return $views_data["views"];
        }
    }
     
     return 0;
}


/**
 * Gets all the data of a page.
 *
 * @param string $page The uri of the page to retrive data.
 * @param string $language_code Optional parameter to get the page data of a
 *       translation if available.
 *
 * @return array All the data fields of the page in the format
 *        $data["field_name"] = "value";
 */
function GetData($page, $language_code = null)
{
    $page = trim($page);
    if($page == "")
    {
        return false;
    }
    
    $page_path = "";

    if(!$language_code)
    {
        $page_path = GeneratePath($page);
    }
    else
    {
        $page_path = dt(GeneratePath($page), $language_code);
    }

    if(!file_exists($page_path . "/data.php"))
    {
        return null;
    }
    
    //get page data
    $data = \JarisCMS\PHPDB\GetData(0, $page_path . "/data.php");
    
    //get views count data
    $views_data = \JarisCMS\PHPDB\GetData(0, $page_path . "/views.php");
    
    //append views count to page data
    if($views_data["views"])
    {
        $data["views"] = $views_data["views"];
    }
    else
    {
        $data["views"] = 0;
    }
    
    $data["groups"] = unserialize($data["groups"]);
    $data["categories"] = unserialize($data["categories"]);

    //Call GetData hook before returning the data
    \JarisCMS\Module\Hook("Page", "GetData", $page, $data, $language_code);

    return $data;
}

/**
 * Check the current type of a page.
 * 
 * @param string $page The page to check its type.
 *
 * @return string The type of the page.
 */
function GetType($page)
{
    $page = trim($page);
    if($page == "")
    {
        return false;
    }
    
    $data = GetData($page);
    
    return $data["type"];
}

/**
 * Check if the current user is owner of the page.
 * 
 * @param string $page The page to check its ownership.
 *
 * @return bool True if current user is the owner or is the admin logged otherwise false.
 */
function IsOwner($page)
{   
    $page = trim($page);
    if($page == "")
    {
        return false;
    }
    
    if(\JarisCMS\Security\IsAdminLogged())
    {
        return true;
    }
    
    $data = GetData($page);
    
    if(\JarisCMS\Group\GetPermission("edit_all_user_content", \JarisCMS\Security\GetCurrentUserGroup()) && \JarisCMS\Group\GetTypePermission($data['type'], \JarisCMS\Security\GetCurrentUserGroup()))
    {
        return true;
    }
    
    if($data["author"] == \JarisCMS\Security\GetCurrentUser())
    {
        return true;
    }
    
    return false;
    
}

/**
 * Moves a page data path to another one.
 *
 * @param string $actual_uri The actual page to move.
 * @param string $new_uri The path to new page data. Returns the new uri of the page
 *                useful since it renames the uri in case it exist.
 *
 * @return bool True on success or false if fail.
 */
function Move($actual_uri, &$new_uri)
{
    $actual_uri = trim($actual_uri);
    $new_uri = trim($new_uri);
    if($actual_uri == "" || $new_uri == "")
    {
        return false;
    }
    
    $actual_path = GeneratePath($actual_uri);
    $new_path = \JarisCMS\FileSystem\Rename(GeneratePath($new_uri));

    $new_uri = \JarisCMS\FileSystem\GetURIFromPath(str_replace(\JarisCMS\Setting\GetDataDirectory() . "pages/", "", $new_path));

    //Call move_page hook before moving the page
    \JarisCMS\Module\Hook("Page", "Move", $actual_uri, $new_uri);

    if(\JarisCMS\FileSystem\MakeDir($new_path, 0755, true))
    {
        \JarisCMS\FileSystem\MoveDirRecursively($actual_path, $new_path);

        //Clears the page directory to be able to delete it
        \JarisCMS\FileSystem\RemoveDirRecursively($actual_path, true);

        RemoveEmptyDirs($actual_path);
        
        EditURISQLite($actual_uri, $new_uri);

        return true;
    }

    return false;
}

/**
 * Checks if the current user group has access to the page
 *
 * @param array $page_data Data array of the content to check.
 *
 * @return bool True if has access or false if not
 */
function UserAccess($page_data)
{    
    $current_group = \JarisCMS\Security\GetCurrentUserGroup();
    
    //If administrator not selected any group return true or admin logged.
    if(!$page_data["groups"] || \JarisCMS\Security\IsAdminLogged())
    {
        return true;
    }
    
    foreach($page_data["groups"] as $machine_name)
    {
        if($machine_name == $current_group)
        {
            return true;
        }
    }
    
    return false;
}

/**
 * Starts deleting empty directories from the deepest one to its root
 *
 * @param string $path The path wich the empty directories are going to be deleted.
 */
function RemoveEmptyDirs($path)
{
    $path = trim($path);
    if($path == "")
    {
        return false;
    }
    
    $main_dir = \JarisCMS\Setting\GetDataDirectory() . "pages/singles/"; //This is the directory that is not going to be deleted

    //Checks if the path belongs to the sections path
    $path = str_replace(\JarisCMS\Setting\GetDataDirectory() . "pages/sections/", "", $path, $count);
    if($count > 0)
    {
        $main_dir = \JarisCMS\Setting\GetDataDirectory() . "pages/sections/";
    }
    else
    {
        $path = str_replace(\JarisCMS\Setting\GetDataDirectory() . "pages/singles/", "", $path, $count);
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

/**
 * Add a created page uri to the search_engine sqlite database for faster searching.
 *
 * @param string $uri The uri to add.
 */
function AddURISQLite($uri, $data)
{
    if(!\JarisCMS\SQLite\DBExists("search_engine"))
    {
        $db = \JarisCMS\SQLite\Open("search_engine");
        \JarisCMS\SQLite\Query("create table uris (
        title text, 
        content text, 
        description text, 
        keywords text, 
        groups text, 
        categories text,
        input_format text,
        created_date text,
        last_edit_date text,
        last_edit_by text,
        author text,
        type text,
        views integer,
        views_day integer,
        views_day_count integer,
        views_week integer,
        views_week_count integer,
        views_month integer,
        views_month_count integer,
        uri text, 
        data text)", $db);
        
        \JarisCMS\SQLite\Query("create index uris_index on uris (
        title desc, 
        content desc, 
        description desc, 
        keywords desc, 
        categories desc,
        created_date desc,
        last_edit_date desc,
        author desc,
        type desc,
        views desc,
        views_day_count desc,
        views_week_count desc,
        views_month_count desc,
        uri desc)", $db);
        
        \JarisCMS\SQLite\Close($db);
    }
    
    $all_data = $data;
    $all_data["groups"] = unserialize($all_data["groups"]);
    $all_data["categories"] = unserialize($all_data["categories"]);
    $all_data = str_replace("'", "''", serialize($all_data));
    
    $data["content"] = \JarisCMS\Search\StripHTMLTags($data["content"]); 
    //Substitute some characters with spaces to improve search quality
    $data["content"] = str_replace(array(".", ",", "'", "\"", "(", ")"), " ", $data["content"]);
    //Remove repeated whitespaces
    $data["content"] = preg_replace('/\s\s+/', ' ', $data["content"]);
    $data["content"] = strtolower($data["content"]);
    
    if(!$data["views"])
    {
        $data["views"] = "0";
    }
    
    \JarisCMS\SQLite\EscapeArray($data);
    
    $uri = str_replace("'", "''", $uri);
    
    $db = \JarisCMS\SQLite\Open("search_engine");
    \JarisCMS\SQLite\Query("insert into uris 
    (title, content, description, keywords, groups, categories, input_format, 
     created_date, last_edit_date, last_edit_by, author, type, views, uri, data) 
     
    values ('{$data['title']}', '{$data['content']}', '{$data['description']}', '{$data['keywords']}',
    '{$data['groups']}', '{$data['categories']}','{$data['input_format']}','{$data['created_date']}', 
    '{$data['last_edit_date']}', '{$data['last_edit_by']}', '{$data['author']}', '{$data['type']}', 
    {$data['views']}, '$uri', '$all_data')", $db);
    
    \JarisCMS\SQLite\Close($db);
}

/**
 * Edit an existing uri on the sqlite search_engine database, used when moving a page from location.
 *
 * @param string $old_uri The old uri to renew.
 * @param string $new_uri The new uri that is going to replace the old one.
 */
function EditURISQLite($old_uri, $new_uri)
{
    if(\JarisCMS\SQLite\DBExists("search_engine"))
    {
        $db = \JarisCMS\SQLite\Open("search_engine");
        
        $old_uri = str_replace("'", "''", $old_uri);
        $new_uri = str_replace("'", "''", $new_uri);
        
        \JarisCMS\SQLite\Query("update uris set uri = '$new_uri' where uri = '$old_uri'", $db);
        
        \JarisCMS\SQLite\Close($db);
    }
}

/**
 * Edit data of existing uri.
 *
 * @param string $uri The uri to edit its data.
 * @param string $data The new data to store.
 */
function EditDataURISQLite($uri, $data)
{
    if(\JarisCMS\SQLite\DBExists("search_engine"))
    {
        $all_data = $data;
        $all_data["groups"] = unserialize($all_data["groups"]);
        $all_data["categories"] = unserialize($all_data["categories"]);
        $all_data = str_replace("'", "''", serialize($all_data));
        
        $data["content"] = \JarisCMS\Search\StripHTMLTags($data["content"]); 
        //Substitute some characters with spaces to improve search quality
        $data["content"] = str_replace(array(".", ",", "'", "\"", "(", ")"), " ", $data["content"]);
        //Remove repeated whitespaces
        $data["content"] = preg_replace('/\s\s+/', ' ', $data["content"]);
        $data["content"] = strtolower($data["content"]);
        
        \JarisCMS\SQLite\EscapeArray($data);
        
        $uri = str_replace("'", "''", $uri);
        
        $db = \JarisCMS\SQLite\Open("search_engine");
        
        //No need to save views since views are managed by separate
        
        \JarisCMS\SQLite\Query("update uris set 
        title = '{$data['title']}', 
        content = '{$data['content']}', 
        description = '{$data['description']}', 
        keywords = '{$data['keywords']}',
        groups = '{$data['groups']}', 
        categories = '{$data['categories']}',
        input_format = '{$data['input_format']}',
        created_date = '{$data['created_date']}', 
        last_edit_date = '{$data['last_edit_date']}', 
        last_edit_by = '{$data['last_edit_by']}', 
        author = '{$data['author']}', 
        type = '{$data['type']}', 
        data = '$all_data' 
        
        where uri = '$uri'", $db);
        
        \JarisCMS\SQLite\Close($db);
    }
}

/**
 * Removes an uri from the search_engine sqlite database, used when deleting a page that is not
 * going to be anymore available for searching.
 *
 * @param string $uri The uri to delete.
 */
function RemoveURISQLite($uri)
{
    if(\JarisCMS\SQLite\DBExists("search_engine"))
    {
        $uri = str_replace("'", "''", $uri);
       
        $db = \JarisCMS\SQLite\Open("search_engine");
        \JarisCMS\SQLite\Query("delete from uris where uri = '$uri'", $db);
        
        \JarisCMS\SQLite\Close($db);
    }
}

/**
 * To retrieve a list of pages from sqlite database to generate pages list page.
 *
 * @param integer $page The current page count of pages list the admin is viewing.
 * @param integer $limit The amount of pages per page to display.
 * 
 * @return array Each page uri not longer than $limit
 */
function GetListSQLite($page=0, $limit=30)
{
    $db = null;
    $page *=  $limit;
    $pages = array();
        
    if(\JarisCMS\SQLite\DBExists("search_engine"))
    {
        $db = \JarisCMS\SQLite\Open("search_engine");
        $result = \JarisCMS\SQLite\Query("select uri from uris order by created_date desc, last_edit_date desc limit $page, $limit", $db);
    }
    else
    {
        return $pages;
    }
    
    $fields = array();
    if($fields = \JarisCMS\SQLite\FetchArray($result))
    {
        $pages[] = $fields["uri"];
        
        while($fields = \JarisCMS\SQLite\FetchArray($result))
        {
            $pages[] = $fields["uri"];
        }
        
        \JarisCMS\SQLite\Close($db);
        return $pages;
    }
    else
    {
        \JarisCMS\SQLite\Close($db);
        return $pages;
    }
}

/**
 * Generates the system path to the page data directory.
 *
 * @param string $page The uri to generate the path from, example mysection/pagename
 *
 * @return string Path to page data directory.
 */
function GeneratePath($page)
{
    $path = \JarisCMS\Setting\GetDataDirectory() . "pages/";

    $sections = explode("/",$page);

        //Last element of the array is the name of the page, so we substract it.
        $sections_available = count($sections) - 1;

        if($sections_available != 0)
        {
            //Here we replace the full $page value with sections stripped out.
            $page = $sections[count($sections)-1];
            $path .= "sections/";

            for($i=0; $i<$sections_available; ++$i)
            {
                $path .= $sections[$i] . "/";
            }

            $path .= substr($page,0,1) . "/" . substr($page,0,2) . "/" . $page;
        }
        else
        {
            $path .= "singles/" . substr($page,0,1) . "/" . substr($page,0,2) . "/" . $page;
        }

    return $path;
}
?>