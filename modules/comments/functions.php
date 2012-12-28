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

namespace JarisCMS\Module\Comments\System
{
    use JarisCMS\URI;
    use JarisCMS\Group;
    use JarisCMS\Module;
    use JarisCMS\System;
    use JarisCMS\Security;
    
    function GetPageData(&$page_data)
    {   
        $comment_settings = JarisCMS\Module\Comments\GetSettings($page_data[0]["type"]);

        if($comment_settings["enabled"])
        {    
            if(Group\GetPermission("view_comments", Security\GetCurrentUserGroup()))
            {
                System\AddStyle("modules/comments/styles/comments.css");
                System\AddScript("scripts/optional/jquery.limit.js");
                System\AddScript("comments-script", array("page"=>URI\Get(), "type"=>$page_data[0]["type"]));
            }
        }
    }
    
    function GenerateAdminPage(&$sections)
    {
        $group = Security\GetCurrentUserGroup();

        $title = t("Content");

        foreach($sections as $index=>$sub_section)
        {
            if($sub_section["title"] == $title)
            {
                if(Group\GetPermission("manage_comments_flags", Security\GetCurrentUserGroup()))
                {
                    $sub_section["sub_sections"][] = array("title"=>t("Manage Comment Flags"), "url"=>URI\PrintURL(Module\GetPageURI("admin/comments/flags", "comments")), "description"=>t("To see which comments has been flagged and delete them."));   
                    $sections[$index]["sub_sections"] = $sub_section["sub_sections"];
                }

                break;
            }
        }
    }
}

namespace JarisCMS\Module\Comments\Group
{
    function GetPermissions(&$permissions, &$group)
    {
        if($group != "guest")
        {
            $comments["view_comments"] = t("View");
            $comments["add_comments"] = t("Add");
            $comments["edit_comments"] = t("Edit");
            $comments["delete_comments"] = t("Delete");
            $comments["flag_comments"] = t("Flag");
            $comments["notifications_comments"] = t("Notifications");
            $comments["manage_comments_flags"] = t("Manage Flags");


            $permissions[t("Comments")] = $comments;
        }
        else
        {
            $comments["view_comments"] = t("View");
            $comments["flag_comments"] = t("Flag");

            $permissions[t("Comments")] = $comments;
        }
    }
}

namespace JarisCMS\Module\Comments\Page
{
    use JarisCMS\SQLite;
    
    function Delete(&$page, &$page_path)
    {
        $fields["uri"] = $page;

        SQLite\EscapeArray($fields);

        //Delete from system db
        $db_system = SQLite\Open("comments");
        $delete_system = "delete from comments where uri='{$fields['uri']}'";
        SQLite\Query($delete_system, $db_system);
        SQLite\Close($db_system);
    }
}

namespace JarisCMS\Module\Comments\User
{
    use JarisCMS\Group, JarisCMS\Security, JarisCMS\Module;
    
    function PrintPage(&$content, &$tabs)
    {
        if(Group\GetPermission("add_comments", Security\GetCurrentUserGroup()))
        {
            $tabs[t("Comments")] = array("uri"=>Module\GetPageURI("user/comments", "comments"));
        }
    }
}

namespace JarisCMS\Module\Comments\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Group;
    use JarisCMS\Module;
    use JarisCMS\Security;
    
    function MakeContent(&$content, &$content_title, &$content_data)
    {
        $comment_settings = JarisCMS\Module\Comments\GetSettings($content_data["type"]);

        if($comment_settings["enabled"])
        {    
            if(Group\GetPermission("view_comments", Security\GetCurrentUserGroup()))
            {   
                $comments_content = JarisCMS\Module\Comments\PrintPost();

                $comments_content .= "<h3 class=\"comments-head\">" . t("Comments") . "</h3>";

                $comments_content .= JarisCMS\Module\Comments\PrintAll(URI\Get(), $content_data["type"]);

                $content .= $comments_content;

                $content_data["comments_content"] = $comments_content;
            }
        }
    }
    
    function MakeTabsCode(&$tabs_array)
    {
        if(URI\Get() == "admin/types/edit")
        {
            $tabs_array[0][t("Comments")] = array("uri"=>Module\GetPageURI("admin/types/comments", "comments"), "arguments"=>array("type"=>$_REQUEST["type"]));
        }
    }
    
    function GetPageTemplateFile(&$page, &$template_path)
    {
        global $theme;
        $default_template = "themes/" . $theme . "/page.php";

        if($template_path == $default_template)
        {
            if(URI\Get() == Module\GetPageURI("add/comment", "comments"))
            {
                $template_path = "modules/comments/templates/page-empty.php";
            }
            else if(URI\Get() == Module\GetPageURI("edit/comment", "comments"))
            {
                $template_path = "modules/comments/templates/page-empty.php";
            }
            else if(URI\Get() == Module\GetPageURI("delete/comment", "comments"))
            {
                $template_path = "modules/comments/templates/page-empty.php";
            }
            else if(URI\Get() == Module\GetPageURI("flag/comment", "comments"))
            {
                $template_path = "modules/comments/templates/page-empty.php";
            }
            else if(URI\Get() == Module\GetPageURI("comments-script", "comments"))
            {
                $template_path = "modules/comments/templates/page-empty.php";
            }
            else if(URI\Get() == Module\GetPageURI("navigations/comment", "comments"))
            {
                $template_path = "modules/comments/templates/page-empty.php";
            }
        }
    }
    
    function GetContentTemplateFile(&$page, &$type, &$template_path)
    {
        global $theme;
        
        $default_template = "themes/" . $theme . "/content.php";

        if($template_path == $default_template)
        {
            if(URI\Get() == Module\GetPageURI("add/comment", "comments"))
            {
                $template_path = "modules/comments/templates/content-empty.php";
            }
            else if(URI\Get() == Module\GetPageURI("edit/comment", "comments"))
            {
                $template_path = "modules/comments/templates/content-empty.php";
            }
            else if(URI\Get() == Module\GetPageURI("delete/comment", "comments"))
            {
                $template_path = "modules/comments/templates/content-empty.php";
            }
            else if(URI\Get() == Module\GetPageURI("flag/comment", "comments"))
            {
                $template_path = "modules/comments/templates/content-empty.php";
            }
            else if(URI\Get() == Module\GetPageURI("comments-script", "comments"))
            {
                $template_path = "modules/comments/templates/content-empty.php";
            }
            else if(URI\Get() == Module\GetPageURI("navigations/comment", "comments"))
            {
                $template_path = "modules/comments/templates/content-empty.php";
            }
        }
    }
}

?>