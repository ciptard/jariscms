<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module include file
 *
 *@note File with general functions
 */

namespace JarisCMS\Module\Blog;

function GetFromDB($username)
{
    $db = JarisCMS\SQLite\Open("blog");
    
    $select = "select * from blogs where user = '$username'";
    
    $result = JarisCMS\SQLite\Query($select, $db);
    
    $data = JarisCMS\SQLite\FetchArray($result);
    
    JarisCMS\SQLite\Close($db);
    
    return $data;
}

function EditFromDB($username, $fields)
{
    $db = JarisCMS\SQLite\Open("blog");
    
    $time = time();
    
    JarisCMS\SQLite\EscapeArray($fields);
    
    $update = "update blogs set 
    title = '{$fields['title']}',
    description = '{$fields['description']}',
    tags = '{$fields['tags']}',
    edited_timestamp = '$time',
    category = '{$fields['category']}'
    where user='$username'";
    
    JarisCMS\SQLite\Query($update, $db);
    
    JarisCMS\SQLite\Close($db);
}
 
function DeleteFromDB($username)
{
    $db = JarisCMS\SQLite\Open("blog");
    
    $delete = "delete from blogs where user='$username'";
    
    JarisCMS\SQLite\Query($delete, $db);
    
    JarisCMS\SQLite\Close($db);
}

function CountView($username)
{
    $db = JarisCMS\SQLite\Open("blog");
    
    $update = "update blogs set
    views = views + 1
    where user = '$username'
    ";
    
    JarisCMS\SQLite\Query($update, $db);
    
    JarisCMS\SQLite\Close($db);
}

function AddPost($page, $data)
{
    Create($data["author"]);
    
    $username = $data["author"];
    $user_data = JarisCMS\User\GetData($username);
    
    $db_path = str_replace("data.php", "", JarisCMS\User\GeneratePath($username, $user_data["group"]));
    
    $db = JarisCMS\SQLite\Open("blog", $db_path);
    
    $fields["uri"] = $page;
    $fields["created_timestamp"] = $data["created_date"];
    $fields["month"] = date("n", time());
    $fields["year"] = date("Y", time());
    
    JarisCMS\SQLite\EscapeArray($fields);
    
    $insert = "insert into post (uri, created_timestamp, month, year) values(
    '{$fields['uri']}',
    '{$fields['created_timestamp']}',
    '{$fields['month']}',
    '{$fields['year']}'
    )"; 
    
    JarisCMS\SQLite\Query($insert, $db);
    
    JarisCMS\SQLite\Close($db);
}

function EditPost($actual_uri, $new_uri, $username)
{
    $user_data = JarisCMS\User\GetData($username);
    
    $db_path = str_replace("data.php", "", JarisCMS\User\GeneratePath($username, $user_data["group"]));
    
    $db = JarisCMS\SQLite\Open("blog", $db_path);
    
    $fields["uri"] = $actual_uri;
    $fields["new_uri"] = $new_uri;
    $fields["edited_timestamp"] = $data["edited_timestamp"];
    
    JarisCMS\SQLite\EscapeArray($fields);
    
    $update = "update post set 
    uri = '{$fields["new_uri"]}',
    edited_timestamp = '{$fields['edited_timestamp']}'
    where uri = '{$fields["uri"]}'"; 
    
    JarisCMS\SQLite\Query($update, $db);
    
    JarisCMS\SQLite\Close($db);
}

function DeletePost($page, $username)
{
    if(JarisCMS\User\Exists($username))
    {
        $user_data = JarisCMS\User\GetData($username);
        $db_path = str_replace("data.php", "", JarisCMS\User\GeneratePath($username, $user_data["group"]));
        
        $db = JarisCMS\SQLite\Open("blog", $db_path);
        
        $uri = str_replace("'", "''", $page);
        
        $delete = "delete from post where uri='$uri'";
        
        JarisCMS\SQLite\Query($delete, $db);
        
        JarisCMS\SQLite\Close($db);
    }
    else
    {
        $db = JarisCMS\SQLite\Open("blog");
        
        $delete = "delete from blogs where user='$username'"; 
        
        JarisCMS\SQLite\Query($delete, $db);
        
        JarisCMS\SQLite\Close($db);
    }
}

function Create($user)
{ 
    if($user_data = JarisCMS\User\GetData($user))
    {
        if(!$user_data["blog"])
        {
            $db = JarisCMS\SQLite\Open("blog");
            
            $select = "select user from blogs where user='" . str_replace("'", "''", $user) . "'";
            
            $result = JarisCMS\SQLite\Query($select, $db);
            
            if(!($data = JarisCMS\SQLite\FetchArray($result)))
            {
                $fields["created_timestamp"] = time();
                $fields["user"] = $user;
                $fields["views"] = "0";
                
                JarisCMS\SQLite\EscapeArray($fields);
                
                $insert = "insert into blogs 
                (
                    created_timestamp, 
                    user,
                    views
                )
                
                values(
                    '{$fields['created_timestamp']}',
                    '{$fields['user']}',
                    {$fields['views']}
                )
                ";
                
                JarisCMS\SQLite\Query($insert, $db);
            }
            
            JarisCMS\SQLite\Close($db);
            
            $user_data["blog"] = true;
            JarisCMS\User\Edit($user, $user_data["group"], $user_data, $user_data);
            
            //Create personal post database
            $db_path = str_replace("data.php", "", JarisCMS\User\GeneratePath($user, $user_data["group"]));
            
            if(!JarisCMS\SQLite\DBExists("blog", $db_path))
            {
                $db_post = JarisCMS\SQLite\Open("blog", $db_path);
                
                $create = "create table post (id integer primary key, created_timestamp text, edited_timestamp text, month text, year text, uri text)";
                
                JarisCMS\SQLite\Query($create, $db_post);
                
                $create_index = "create index post_index on post (created_timestamp desc, edited_timestamp desc, month desc, year desc, uri desc)";
                
                JarisCMS\SQLite\Query($create_index, $db_post);
                
                JarisCMS\SQLite\Close($db_post);
            }
        }
    }
}

function Subscribed($blog, $user)
{
    $fields["user_blog"] = $blog;
    $fields["subscriber"] = $user;
    
    JarisCMS\SQLite\EscapeArray($fields);
    
    $db = JarisCMS\SQLite\Open("blog_subscriptions");
    
    $select = "select id from subscriptions where user='{$fields['user_blog']}' and subscriber='{$fields['subscriber']}'";
    
    $result = JarisCMS\SQLite\Query($select, $db);
    
    $data = JarisCMS\SQLite\FetchArray($result);
    
    JarisCMS\SQLite\Close($db);
    
    return $data;
}

function Subscribe($blog, $user)
{
    if(!Subscribed($channel, $user))
    {
        $fields["user_blog"] = $blog;
        $fields["subscriber"] = $user;
        
        JarisCMS\SQLite\EscapeArray($fields);
        
        $db = JarisCMS\SQLite\Open("blog_subscriptions");
        
        $time = time();
        
        $insert = "insert into subscriptions (user, subscriber, created_timestamp) values('{$fields['user_blog']}','{$fields['subscriber']}', '$time')";
        
        JarisCMS\SQLite\Query($insert, $db);
        
        JarisCMS\SQLite\Close($db);
        
        return true;
    }
    
    return false;
}

function Unsubscribe($blog, $user)
{
    $fields["user_blog"] = $blog;
    $fields["subscriber"] = $user;
    
    JarisCMS\SQLite\EscapeArray($fields);
    
    $db = JarisCMS\SQLite\Open("blog_subscriptions");
    
    $delete = "delete from subscriptions where user='{$fields['user_blog']}' and subscriber='{$fields['subscriber']}'";
    
    JarisCMS\SQLite\Query($delete, $db);
    
    JarisCMS\SQLite\Close($db);
}

function GetSettings()
{
    $settings = JarisCMS\Setting\GetAll("blogs");
    
    $settings["main_category"] = $settings["main_category"]?$settings["main_category"]:"";
    
    return $settings;
}

/**
 *Prepares the content that is going to be displayed
 *
 *
 *@param $content Array that contains all the page data content.
 *
 *@return String with the content preformatted.
 */
function Theme($post_data)
{
    $images = JarisCMS\Image\GetList($post_data["uri"]);
                
    $thumbnail = false;
    
    if(is_array($images))
    {
        foreach($images as $image_id=>$image_data)
        {
            $thumbnail = JarisCMS\URI\PrintURL("image/" . $post_data["uri"] . "/$image_id", array("w"=>100,"h"=>60));
            break;
        }
    }
                
    $page_data = JarisCMS\Page\GetData($post_data["uri"]);
    $page_data_translation = JarisCMS\Page\GetData($post_data["uri"], JarisCMS\Language\GetCurrent());
    $page_data["title"] = $page_data_translation["title"];
    $page_data["content"] = $page_data_translation["content"];
    
    $url = JarisCMS\URI\PrintURL($post_data["uri"]);
    $title = $page_data["title"];
    $views = $page_data["views"];
                
    $description = JarisCMS\System\PrintContentPreview($page_data["content"], 50, true);
    
    
    $blog_post = "";

    ob_start();
        include(GetTemplatePath($post_data["uri"]));
        $blog_post .= ob_get_contents();
    ob_end_clean();

    return $blog_post;
}

/**
 *Search for the best blog template match
 *
 *@param $page The page uri that is going to be displayed.
 *
 *@return The template file to be used.
 *    It could be one of the followings in the same precedence:
 *        themes/theme/blog-post-uri.php
 *        themes/theme/blog-post.php
 */
function GetTemplatePath($page)
{
    global $theme;
    $page = str_replace("/", "-", $page);

    $current_page = "themes/" . $theme . "/blog-post-" . $page . ".php";
    $default_page = "themes/" . $theme . "/blog-post.php";
    
    $template_path = "";

    if(file_exists($current_page))
    {
        $template_path = $current_page;
    }
    elseif(file_exists($default_page))
    {
        $template_path = $default_page;
    }
    else
    {
        $template_path = "modules/blog/templates/blog-post.php";
    }
    
    return $template_path;
}
?>