<?php
/**
 *Copyright 2008, Jefferson Gonzàlez (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the api page for get ip info with city.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("My Ratings") ?>
    field;

    field: content
        <?php 
            JarisCMS\Security\ProtectPage(array("add_comments"));
            
            JarisCMS\Module\Comments\CleanUserComments(JarisCMS\Security\GetCurrentUser());
            
            JarisCMS\System\AddStyle("modules/comments/styles/user.css");
            
            $page = 1;
            
            if(isset($_REQUEST["page"]))
            {
                $page = $_REQUEST["page"];
            }
            
            $comments_count = JarisCMS\SQLite\CountColumn("comments", "comments", "id", "", JarisCMS\Module\Comments\GetUserPath(JarisCMS\Security\GetCurrentUser()));
            
            print "<h2>" . t("Total Comments:") . " " . $comments_count . "</h2>";
            
            $comments = JarisCMS\SQLite\GetDataList("comments", "comments", $page-1, 10, "order by created_timestamp desc", "*", JarisCMS\Module\Comments\GetUserPath(JarisCMS\Security\GetCurrentUser()));
            
            JarisCMS\System\PrintGenericNavigation($comments_count, $page, "user/comments", "comments", 10);
            
            foreach($comments as $data)
            {
                $page_data = JarisCMS\Page\GetData($data["uri"]);
                $comment_data = JarisCMS\Module\Comments\GetData($data["id"], $data["uri"]);
                
                print "<div class=\"comments-list\">\n";
                print "<div class=\"title\"><a title=\"{$page_data["title"]}\" href=\"" . JarisCMS\URI\PrintURL($data["uri"]) . "\">" . $page_data["title"] . "</a></div>\n";
                print "<div class=\"text\">\n";
                print $comment_data["comment_text"];
                
                $replies = JarisCMS\SQLite\GetDataList("comments", "comments", 0, 5, "where reply_to={$data['id']} order by created_timestamp desc", "*", JarisCMS\Module\Comments\GetPagePath($data["uri"]));
                foreach($replies as $reply_data)
                {
                    print "<h4>" . t("Recent replies to this comment") . "</h4>";
                    print "<div class=\"text\">\n";
                    print $reply_data["comment_text"];
                    print "</div>";
                }
                
                
                print "</div>";
                print "</div>\n";
            }
            
            if($comments_count <= 0)
            {
                JarisCMS\System\AddMessage(t("No comments posted by you yet."));
            }
            
            JarisCMS\System\PrintGenericNavigation($comments_count, $page, "user/comments", "comments", 10);
        ?>
    field;

    field: is_system
        1
    field;
row;
