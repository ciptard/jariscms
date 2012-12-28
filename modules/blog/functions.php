<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module functions file
 *
 *@note File that stores all hook functions.
 */

namespace JarisCMS\Module\Blog\System
{
    use JarisCMS\URI;
    use JarisCMS\User;
    use JarisCMS\Page;
    use JarisCMS\Group;
    use JarisCMS\Module;
    use JarisCMS\System;
    
    function GetPageData(&$page_data)
    {
        $segments = explode("/", URI\Get());

        if(count($segments) == 3)
        {
            if($segments[0] == "blog" && $segments[1] == "user")
            {
                if($user_data = User\GetData($segments[2]))
                {   
                    if(Group\GetTypePermission("blog", $user_data["group"]))
                    {
                        $_REQUEST["user"] = $segments[2];
                        JarisCMS\Module\Blog\Create($_REQUEST["user"]);
                        JarisCMS\Module\Blog\CountView($_REQUEST["user"]);
                        $page_data[0] = Page\GetData(Module\GetPageURI("blog/user", "blog"));
                        $page_data[0]["title"] = System\PHPEval($page_data[0]["title"]);
                    }
                }
            }
        }
    }
}

namespace JarisCMS\Module\Blog\Page
{
    use JarisCMS\Page;
    use JarisCMS\SQLite;
    use JarisCMS\User;
    use JarisCMS\Group;
    
    function Create(&$page, &$data, &$path)
    {
        if($data["type"] == "blog")
        {
            JarisCMS\Module\Blog\AddPost($page, $data);
        }
    }
    
    function Delete(&$page, &$page_path)
    {
        $page_data = Page\GetData($page);

        if($page_data["type"] == "blog")
        {
            JarisCMS\Module\Blog\DeletePost($page, $page_data["author"]);
        }
    }
    
    function Edit(&$page, &$new_data, &$page_path)
    {
        $username = $new_data["author"];
        $user_data = User\GetData($username);

        //Check user has blog permissions
        if(!Group\GetTypePermission("blog", $user_data["group"]))
        {
            return;
        }

        //In case users blog database doesnt exists yet
        JarisCMS\Module\Blog\Create($username);

        //Ensure that if user changed content type from blog to another to delete the post from the blog listing
        if($new_data["type"] != "blog")
        {
            JarisCMS\Module\Blog\DeletePost($page, $username);
        }

        //If some one took an existing content and changed the content type from another one to blog and it to the users post list
        else
        {    
            $db_path = str_replace("data.php", "", User\GeneratePath($username, $user_data["group"]));

            $db = SQLite\Open("blog", $db_path);

            $select = "select * from post where uri = '$page'"; 

            $result = SQLite\Query($select, $db);

            $in_db = SQLite\FetchArray($result);

            //Ensure database gets unlocked in order to use it for writing
            unset($result);

            SQLite\Close($db);

            if(!is_array($in_db))
            {
                JarisCMS\Module\Blog\AddPost($page, $new_data);
            }
        }
    }
    
    function Move(&$actual_uri, &$new_uri)
    {
        $page_data = Page\GetData($actual_uri);

        if($page_data["type"] == "blog")
        {
            JarisCMS\Module\Blog\EditPost($actual_uri, $new_uri, $page_data["author"]);
        }
    }
}

namespace JarisCMS\Module\Blog\User
{
    use JarisCMS\Group;
    use JarisCMS\Security;
    use JarisCMS\Module;
    
    function Delete(&$username, &$group)
    {
        JarisCMS\Module\Blog\DeleteFromDB($username);
    }
    
    function PrintPage(&$content, &$tabs)
    {
        if(Group\GetTypePermission("blog", Security\GetCurrentUserGroup()))
        {
            $tabs[t("Blog")] = array("uri"=>Module\GetPageURI("user/blog", "blog"));
        }
    }
}

namespace JarisCMS\Module\Blog\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Module;
    
    function MakeTabsCode(&$tabs_array)
    {
        if(URI\Get() == "admin/settings")
        {
            $tabs_array[0][t("Blog")] = array("uri"=>Module\GetPageURI("admin/settings/blog", "blog"), "arguments"=>null);
        }
    }
    
    function GetContentTemplateFile(&$page, &$type, &$template_path)
    {
        global $theme;
        
        $default_template = "themes/" . $theme . "/content.php";

        if($template_path == $default_template)
        {
            if($type == "blog")
            {
                $template_path = "modules/blog/templates/content-blog.php";
            }
        }
    }
}

?>
