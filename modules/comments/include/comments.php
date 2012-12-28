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

namespace JarisCMS\Module\Comments;

function GetSettings($type)
{
    $settings = array();
    if(!($settings = JarisCMS\Setting\Get($type, "comments")))
    {
        $settings["enabled"] = false;
        $settings["maximun_characters"] = 500;
    }
    else
    { 
        $settings = unserialize($settings);
        
        $settings["enabled"] = $settings["enabled"]?$settings["enabled"]:false;
        $settings["maximun_characters"] = $settings["maximun_characters"]?$settings["maximun_characters"]:500;
    }
    
    return $settings;
}

function GetData($id, $page)
{
    CreateDB($page, JarisCMS\Security\GetCurrentUser());
    
    $fields["id"] = $id;

    JarisCMS\SQLite\EscapeArray($fields);
    
    $db = JarisCMS\SQLite\Open("comments", GetPagePath($page));
    
    $select = "select * from comments where id={$fields['id']}";
    
    $result = JarisCMS\SQLite\Query($select, $db);
    
    $data = JarisCMS\SQLite\FetchArray($result);
    
    JarisCMS\SQLite\Close($db);
    
    return $data;
}

function Add($comment, $page, $reply_to_id=null)
{
    if($reply_to_id <= 0)
    {
        $reply_to_id = "null";
    }
    
    CreateDB($page, JarisCMS\Security\GetCurrentUser());
    
    $fields["created_timestamp"] = time();
    $fields["comment_text"] = $comment;
    $fields["user"] = JarisCMS\Security\GetCurrentUser();
    $fields["reply_to"] = $reply_to_id;
    $fields["flags"] = 0;
    $fields["uri"] = $page;
    $fields["type"] = JarisCMS\Page\GetType($page);

    JarisCMS\SQLite\EscapeArray($fields);
    
    //Page database    
    $db_page = JarisCMS\SQLite\Open("comments", GetPagePath($page));
    
    $insert_page = "insert into comments (created_timestamp, comment_text, user, reply_to, flags)
    values(
    '{$fields['created_timestamp']}', '{$fields['comment_text']}', '{$fields['user']}',
    {$fields['reply_to']}, {$fields['flags']}
    )";
    
    JarisCMS\SQLite\Query($insert_page, $db_page);
    
    //Retrieve id of created comment
    $select_id = "select id from comments where 
    created_timestamp='{$fields['created_timestamp']}' and user='{$fields['user']}'";
    
    $result = JarisCMS\SQLite\Query($select_id, $db_page);
    
    $data = JarisCMS\SQLite\FetchArray($result);
    
    JarisCMS\SQLite\Close($db_page);
    
    //User database
    $db_user = JarisCMS\SQLite\Open("comments", GetUserPath(JarisCMS\Security\GetCurrentUser()));
    
    $insert_user = "insert into comments (id, created_timestamp, comment_text, reply_to, flags, uri)
    values(
    {$data['id']}, '{$fields['created_timestamp']}', '{$fields['comment_text']}',
    {$fields['reply_to']}, {$fields['flags']}, '{$fields['uri']}'
    )";
    
    JarisCMS\SQLite\Query($insert_user, $db_user);
    
    JarisCMS\SQLite\Close($db_user);
    
    //System database
    $db_system = JarisCMS\SQLite\Open("comments");
    
    $insert_system = "insert into comments (id, created_timestamp, flags, uri, type)
    values(
    {$data['id']}, '{$fields['created_timestamp']}',
    {$fields['flags']}, '{$fields['uri']}', '{$fields['type']}'
    )";
    
    JarisCMS\SQLite\Query($insert_system, $db_system);
    
    JarisCMS\SQLite\Close($db_system);
    
    return $data["id"];
}

function Edit($comment, $id, $page, $user)
{
    CreateDB($page, $user);
    
    $fields["id"] = $id;
    $fields["user"] = $user;
    $fields["uri"] = $page;
    $fields["edited_timestamp"] = time();
    $fields["comment_text"] = $comment;
    
    JarisCMS\SQLite\EscapeArray($fields);
    
    //update page db
    $db_page = JarisCMS\SQLite\Open("comments", GetPagePath($page));
    $update_page = "update comments set
    edited_timestamp = '{$fields['edited_timestamp']}',
    comment_text = '{$fields['comment_text']}'
    where id={$fields['id']}";
    JarisCMS\SQLite\Query($update_page, $db_page);
    JarisCMS\SQLite\Close($db_page);
    
    //update user db
    $db_user = JarisCMS\SQLite\Open("comments", GetUserPath($user));
    $update_user = "update comments set
    edited_timestamp = '{$fields['edited_timestamp']}',
    comment_text = '{$fields['comment_text']}'
    where id={$fields['id']} and uri='{$fields['uri']}'";
    JarisCMS\SQLite\Query($update_user, $db_user);
    JarisCMS\SQLite\Close($db_user);
    
    //update system db
    $db_system = JarisCMS\SQLite\Open("comments");
    $update_system = "update comments set
    edited_timestamp = '{$fields['edited_timestamp']}',
    comment_text = '{$fields['comment_text']}'
    where id={$fields['id']} and uri='{$fields['uri']}'";
    JarisCMS\SQLite\Query($update_system, $db_system);
    JarisCMS\SQLite\Close($db_system);
}

function Delete($id, $page, $user)
{
    CreateDB($page, $user);
    
    $fields["id"] = $id;
    $fields["user"] = $user;
    $fields["uri"] = $page;
    
    JarisCMS\SQLite\EscapeArray($fields);
    
    //Delete from page db
    $db_page = JarisCMS\SQLite\Open("comments", GetPagePath($page));
    $delete_page = "delete from comments where id={$fields['id']}";
    JarisCMS\SQLite\Query($delete_page, $db_page);
    JarisCMS\SQLite\Close($db_page);
    
    //Delete from user db
    $db_user = JarisCMS\SQLite\Open("comments", GetUserPath($user));
    $delete_user = "delete from comments where id={$fields['id']} and uri='{$fields['uri']}'";
    JarisCMS\SQLite\Query($delete_user, $db_user);
    JarisCMS\SQLite\Close($db_user);
    
    //Delete from system db
    $db_system = JarisCMS\SQLite\Open("comments");
    $delete_system = "delete from comments where id={$fields['id']} and uri='{$fields['uri']}'";
    JarisCMS\SQLite\Query($delete_system, $db_system);
    JarisCMS\SQLite\Close($db_system);
}

function Flag($id, $page, $user)
{
    CreateDB($page, $user);
    
    $fields["id"] = $id;
    $fields["user"] = $user;
    $fields["uri"] = $page;
    
    JarisCMS\SQLite\EscapeArray($fields);
    
    //update page db
    $db_page = JarisCMS\SQLite\Open("comments", GetPagePath($page));
    $update_page = "update comments set
    flags = flags+1
    where id={$fields['id']}";
    
    JarisCMS\SQLite\Query($update_page, $db_page);
    JarisCMS\SQLite\Close($db_page);
    
    //update user db
    $db_user = JarisCMS\SQLite\Open("comments", GetUserPath($user));
    $update_user = "update comments set
    flags = flags+1
    where id={$fields['id']} and uri='{$fields['uri']}'";
    JarisCMS\SQLite\Query($update_user, $db_user);
    JarisCMS\SQLite\Close($db_user);
    
    //update system db
    $db_system = JarisCMS\SQLite\Open("comments");
    $update_system = "update comments set
    flags = flags+1
    where id={$fields['id']} and uri='{$fields['uri']}'";
    JarisCMS\SQLite\Query($update_system, $db_system);
    JarisCMS\SQLite\Close($db_system);
}

function RemoveFlag($id, $page, $user)
{
    CreateDB($page, $user);
    
    $fields["id"] = $id;
    $fields["user"] = $user;
    $fields["uri"] = $page;
    
    JarisCMS\SQLite\EscapeArray($fields);
    
    //update page db
    $db_page = JarisCMS\SQLite\Open("comments", GetPagePath($page));
    $update_page = "update comments set
    flags = 0
    where id={$fields['id']}";
    
    JarisCMS\SQLite\Query($update_page, $db_page);
    JarisCMS\SQLite\Close($db_page);
    
    //update user db
    $db_user = JarisCMS\SQLite\Open("comments", GetUserPath($user));
    $update_user = "update comments set
    flags = 0
    where id={$fields['id']} and uri='{$fields['uri']}'";
    JarisCMS\SQLite\Query($update_user, $db_user);
    JarisCMS\SQLite\Close($db_user);
    
    //update system db
    $db_system = JarisCMS\SQLite\Open("comments");
    $update_system = "update comments set
    flags = 0
    where id={$fields['id']} and uri='{$fields['uri']}'";
    JarisCMS\SQLite\Query($update_system, $db_system);
    JarisCMS\SQLite\Close($db_system);
}

function IsFromCurrentUser($id, $page)
{
    CreateDB($page, JarisCMS\Security\GetCurrentUser());
    
    $fields["id"] = $id;
    $fields["uri"] = $page;
    
    JarisCMS\SQLite\EscapeArray($fields);
    
    //select comment from page db
    $db = JarisCMS\SQLite\Open("comments", GetPagePath($page));
    
    $select = "select user from comments where id={$fields['id']}";
    
    $result = JarisCMS\SQLite\Query($select, $db);
    
    $data = JarisCMS\SQLite\FetchArray($result);
    
    if($data["user"] == JarisCMS\Security\GetCurrentUser())
    {
        JarisCMS\SQLite\Close($db);
        return true;
    }
    
    JarisCMS\SQLite\Close($db);
    return false;
}

/**
 *Creates the user and page database if they do not exist
 * 
 *@param $page the uri of the page to create its database
 *@param $user the username of the user to creates its database
 */
function CreateDB($page, $user)
{    
    //Create page comments data base
    if(!JarisCMS\SQLite\DBExists("comments", GetPagePath($page)))
    {
        $db = JarisCMS\SQLite\Open("comments", GetPagePath($page));
        
        JarisCMS\SQLite\Query("create table comments (id integer primary key, created_timestamp text, edited_timestamp text, comment_text text, reply_to integer, user text, flags integer)", $db);
        
        JarisCMS\SQLite\Query("create index comments_index on comments (created_timestamp desc, reply_to desc, user desc, flags desc)", $db);
        
        JarisCMS\SQLite\Close($db);
    }
    
    //Create user comments data base
    if(!JarisCMS\SQLite\DBExists("comments", GetUserPath($user)))
    {
        $db = JarisCMS\SQLite\Open("comments", GetUserPath($user));
        
        JarisCMS\SQLite\Query("create table comments (id integer, created_timestamp text, edited_timestamp text, comment_text text, reply_to integer, uri text, flags integer)", $db);
        
        JarisCMS\SQLite\Query("create index comments_index on comments (created_timestamp desc, reply_to desc, uri desc, flags desc)", $db);
        
        JarisCMS\SQLite\Close($db);
    }
}

function GetUserPath($user)
{
    if($user == "Guest")
    {
        return null;
    }
    
    $user_exist = JarisCMS\User\Exists($user);
    
    $group = $user_exist["group"];
    
    $user_data_path = JarisCMS\User\GeneratePath($user, $group);
    $user_data_path = str_replace("data.php", "", $user_data_path);
    
    return $user_data_path;
}

function GetPagePath($page)
{
    return JarisCMS\Page\GeneratePath($page) . "/";
}

/**
 *To retrieve a list of comments of page from sqlite database to generate comments list
 *
 *@param $page the current page count of pages list the admin is viewing
 *@param $limit the amount of comments per page to display
 *@param $page_uri the uri of the page to retrieve its comments
 * 
 *@return array with each page uri not longer than $limit
 */
function GetList($page=0, $limit=30, $page_uri)
{
    $db = null;
    $page *=  $limit;
    $comments = array();
        
    if(JarisCMS\SQLite\DBExists("comments", GetPagePath($page_uri)))
    {
        $db = JarisCMS\SQLite\Open("comments", GetPagePath($page_uri));
        $result = JarisCMS\SQLite\Query("select * from comments order by created_timestamp desc limit $page, $limit", $db);
    }
    else
    {
        return $comments;
    }
    
    $fields = array();
    if($fields = JarisCMS\SQLite\FetchArray($result))
    {
        $comments[] = $fields;
        
        while($fields = JarisCMS\SQLite\FetchArray($result))
        {
            $comments[] = $fields;
        }
        
        JarisCMS\SQLite\Close($db);
        return $comments;
    }
    else
    {
        JarisCMS\SQLite\Close($db);
        return $comments;
    }
}

function CleanUserComments($user)
{
    //Make a copy because of db locks while reading and writing at same time
    $comments_original = GetUserPath($user) . "comments";
    $comments_copy = GetUserPath($user) . "comments-copy";
    
    //Create a copy of original users database to query comments
    copy($comments_original, $comments_copy);
    
    $db_copy = JarisCMS\SQLite\Open("comments-copy", GetUserPath($user));
    
    //Open original user comments db to delete non existen comments
    $db = JarisCMS\SQLite\Open("comments", GetUserPath($user));
    
    $select = "select id, uri from comments;";
    
    $result = JarisCMS\SQLite\Query($select, $db_copy);
    
    while($comment = JarisCMS\SQLite\FetchArray($result))
    {
        //Delete comment if original page that hold comment was deleted
        if(!JarisCMS\SQLite\DBExists("comments", GetPagePath($comment["uri"])))
        {
            $delete = "delete from comments where id={$comment['id']} and uri='{$comment['uri']}'";
                
            JarisCMS\SQLite\Query($delete, $db);
        }
        
        //Delete if comment doesnt exist on the page comments database
        else
        {
            $db_page = JarisCMS\SQLite\Open("comments", GetPagePath($comment["uri"]));
        
            $select_page = "select id from comments where id={$comment['id']}";
            
            $result_page = JarisCMS\SQLite\Query($select_page, $db_page);
            
            if(!($comment_page = JarisCMS\SQLite\FetchArray($result_page)))
            {
                $delete = "delete from comments where id={$comment['id']} and uri='{$comment['uri']}'";
                
                JarisCMS\SQLite\Query($delete, $db);
            }
            
            JarisCMS\SQLite\Close($db_page);   
        }
    }
    
    JarisCMS\SQLite\Close($db);
    JarisCMS\SQLite\Close($db_copy);
    
    unlink($comments_copy);
}

/**
 *To retrieve a list of flagged comments
 *
 *@param $page the current page count of pages list the admin is viewing
 *@param $limit the amount of comments per page to display
 * 
 *@return array with each page uri not longer than $limit
 */
function GetFlaggedList($page=0, $limit=30)
{
    $db = null;
    $page *=  $limit;
    $comments = array();
        
    if(JarisCMS\SQLite\DBExists("comments"))
    {
        $db = JarisCMS\SQLite\Open("comments");
        $result = JarisCMS\SQLite\Query("select * from comments where flags > 0 order by flags desc limit $page, $limit", $db);
    }
    else
    {
        return $comments;
    }
    
    $fields = array();
    if($fields = JarisCMS\SQLite\FetchArray($result))
    {
        $comments[] = $fields;
        
        while($fields = JarisCMS\SQLite\FetchArray($result))
        {
            $comments[] = $fields;
        }
        
        JarisCMS\SQLite\Close($db);
        return $comments;
    }
    else
    {
        JarisCMS\SQLite\Close($db);
        return $comments;
    }
}

?>