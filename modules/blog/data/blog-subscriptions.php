<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the current user blog subscriptions page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php 
            if($user_data = JarisCMS\User\GetData($_REQUEST["user"]))
            {
                $blog_data = JarisCMS\Module\Blog\GetFromDB($_REQUEST["user"]);
                $title = $_REQUEST["user"];
                if($blog_data["title"])
                {
                    $title = $blog_data["title"];
                }
                print t("Blog subscriptions of") . " " . $title;
            }
            else
            {
                print t("My Blog Subscriptions");
            } 
        ?>
    field;

    field: content
        <?php 
            JarisCMS\System\AddStyle("modules/blog/styles/list.css");
            
            $user = "";
            
            if(isset($_REQUEST["user"]))
            {
                $user_data = JarisCMS\User\GetData($_REQUEST["user"]);
                if($user_data && JarisCMS\Group\GetTypePermission("blog", $user_data["group"]))
                {
                    $user = $_REQUEST["user"];
                    $user = str_replace("'", "''", $user);
                    
                    if(JarisCMS\Group\GetPermission("add_content", $user_data["group"]))
                    {
                        JarisCMS\System\AddTab(t("Blog"), JarisCMS\Module\GetPageURI("blog/user", "blog") . "/" . $_REQUEST["user"]);
                    }
                    
                    JarisCMS\System\AddTab(t("Subscriptions"), JarisCMS\Module\GetPageURI("blog/subscriptions", "blog"), array("user"=>$_REQUEST["user"]));
                }
                else
                {
                    JarisCMS\System\GoToPage("");
                }
            }
            else
            {
                if(JarisCMS\Security\IsUserLogged() && JarisCMS\Group\GetTypePermission("blog", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    $user = JarisCMS\Security\GetCurrentUser();
                    
                    if(JarisCMS\Group\GetPermission("add_content", JarisCMS\Security\GetCurrentUserGroup()) && JarisCMS\Group\GetTypePermission("blog", JarisCMS\Security\GetCurrentUserGroup()))
                    {
                        JarisCMS\System\AddTab(t("View Blog"), JarisCMS\Module\GetPageURI("blog/user", "blog") . "/" . JarisCMS\Security\GetCurrentUser());
                    
                        JarisCMS\System\AddTab(t("Edit Blog Settings"), JarisCMS\Module\GetPageURI("admin/blog", "blog"));
                    
                        JarisCMS\System\AddTab(t("New Post"), "admin/pages/add", array("type"=>"blog"));
                    }
                    
                    JarisCMS\System\AddTab(t("Subscriptions"), JarisCMS\Module\GetPageURI("blog/subscriptions", "blog"));
                }
                else
                {
                    JarisCMS\System\GoToPage("");
                }
            }
            
            $page = 1;
            
            if(isset($_REQUEST["page"]))
            {
                $page = $_REQUEST["page"];
            }
            
            $blog_count = 0;
            $blog_count = JarisCMS\SQLite\CountColumn("blog_subscriptions", "subscriptions", "id", "where subscriber='$user'");
            
            print "<h2>" . t("Total Subscriptions:") . " " . $blog_count . "</h2>";
            
            $blogs = array();
            $blogs = JarisCMS\SQLite\GetDataList("blog_subscriptions", "subscriptions", $page-1, 20, "where subscriber='$user' order by created_timestamp desc");
            
            JarisCMS\System\PrintGenericNavigation($blog_count, $page, "blog/subscriptions", "blog", 20, array("user"=>$_REQUEST["user"]));
            
            foreach($blogs as $data)
            {
                $user_data = JarisCMS\User\GetData($data["user"]);
                
                if($user_data["picture"])
                {
                    $poster = JarisCMS\URI\PrintURL("image/user/" . $data["user"]);
                }
                else
                {
                    $poster = JarisCMS\URI\PrintURL("modules/blog/images/no-picture.png");
                }
                
                $title = $data["user"];
                $blog_data = JarisCMS\Module\Blog\GetFromDB($data["user"]);
                if($blog_data["title"])
                {
                    $title = $blog_data["title"];
                }
                
                $user_url = JarisCMS\URI\PrintURL("blog/user/" . $data["user"]);
                
                print "<div class=\"blogs-list\">\n";
                print "<div class=\"title\"><a title=\"$title\" href=\"" . $user_url . "\">" . $title . "</a></div>\n";
                print "<div class=\"thumbnail\">
                <a title=\"$title\" href=\"" . $user_url . "\"><img alt=\"{$data["title"]}\" src=\"$poster\" /></a>
                </div>\n";
                print "<div class=\"details\">\n";
                print "<div class=\"views\">" . t("Views:") . " " . $blog_data["views"] . "</div>\n";
                print "</div>\n";
                print "<div style=\"clear: both\"></div>\n";
                print "</div>\n";
            }
            
            print "<div style=\"clear: both\"></div>\n";
            
            JarisCMS\System\PrintGenericNavigation($blog_count, $page, "blog/subscriptions", "blog", 20, array("user"=>$_REQUEST["user"]));
        ?>
    field;

    field: is_system
        1
    field;
row;
