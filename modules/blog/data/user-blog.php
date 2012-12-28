<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the view uploaded videos page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0

    field: title
        <?php print t("Blog Posted Content") ?>
    field;

    field: content
        <?php
            if(!JarisCMS\Group\GetTypePermission("blog", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\Security\ProtectPage();
            }
            
            if(JarisCMS\Group\GetPermission("add_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("View Blog"), JarisCMS\Module\GetPageURI("blog/user", "blog") . "/" . JarisCMS\Security\GetCurrentUser());
                
                JarisCMS\System\AddTab(t("Edit Blog Settings"), JarisCMS\Module\GetPageURI("admin/blog/edit", "blog"));
            
                JarisCMS\System\AddTab(t("Add Post"), JarisCMS\Module\GetPageURI("admin/pages/add", "blog"), array("type"=>"blog"));
            }
            
            JarisCMS\System\AddTab(t("Subscriptions"), JarisCMS\Module\GetPageURI("blog/subscriptions", "blog"));
           
            $page = 1;
            
            if(isset($_REQUEST["page"]))
            {
                $page = $_REQUEST["page"];
            }
            
            $user_path = str_replace("data.php", "", JarisCMS\User\GeneratePath(JarisCMS\Security\GetCurrentUser(), JarisCMS\Security\GetCurrentUserGroup()));
            
            $blog_count = JarisCMS\SQLite\CountColumn("blog", "post", "id", "", $user_path);
            
            print "<h2>" . t("Total post:") . " " . $blog_count . "</h2>";
            
            $blogs = JarisCMS\SQLite\GetDataList("blog", "post", $page-1, 10, "order by created_timestamp desc", "*", $user_path);
            
            JarisCMS\System\PrintGenericNavigation($blog_count, $page, "user/blog", "blog", 10);
            
            print "<table class=\"navigation-list\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Thumbnail") . "</td>";
            print "<td>" . t("Title") . "</td>";
            print "<td>" . t("Date") . "</td>";
            print "<td>" . t("Views") . "</td>";
            print "<td>" . t("Actions") . "</td>";
            print "</tr>";
            print "</thead>";
            
            foreach($blogs as $blog_data)
            {
                $page_data = JarisCMS\Page\GetData($blog_data["uri"]);
                
                $images = JarisCMS\Image\GetList($blog_data["uri"]);
                $thumbnail = false;
                if(is_array($images))
                {
                    foreach($images as $image)
                    {
                        $thumbnail = JarisCMS\URI\PrintURL("image/" . $blog_data["uri"] . "/0", array("w"=>100,"h"=>60));
                        break;
                    }
                }
                
                print "<tr>";
                
                print "<td>";
                if($thumbnail)
                {
                    print "<a href=\"" . JarisCMS\URI\PrintURL($blog_data["uri"]) . "\"><img alt=\"{$page_data['title']}\" src=\"$thumbnail\" /></a>";
                }
                print "</td>";
                
                print "<td>{$page_data["title"]}</td>";
                
                $created = "<strong>" . t("Created:") . "</strong> " . date("m/d/Y g:i:s a", $page_data["created_date"]);
                $edited = $page_data["last_edit_date"]?"<div><strong>" . t("Edited:") . "</strong> " . date("m/d/Y g:i:s a", $page_data["last_edit_date"]) . "</div>":"";
                
                print "<td>$created $edited</td>";
                
                print "<td>" . $page_data["views"] . "</td>";
                
                $view_url = JarisCMS\URI\PrintURL($blog_data["uri"]);
                $edit_url = JarisCMS\URI\PrintURL("admin/pages/edit", array("uri"=>$blog_data["uri"]));
                $delete_url = JarisCMS\URI\PrintURL("admin/pages/delete", array("uri"=>$blog_data["uri"]));
                
                print "<td>";
                
                print "<div class=\"view\"><a href=\"$view_url\">" . t("View") . "</a><div>" .
                "<div class=\"edit\"><a href=\"$edit_url\">" . t("Edit") . "</a><div>" .
                "<div class=\"delete\"><a href=\"$delete_url\">" . t("Delete") . "</a><div>";                    
                
                 print "</td>";
                
                print "</tr>";
            }
            
            print "</table>";
            
            JarisCMS\System\PrintGenericNavigation($blog_count, $page, "user/blog", "blog", 10);
        ?>
    field;

    field: is_system
        1
    field;
row;
