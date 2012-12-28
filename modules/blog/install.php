<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module install file
 *
 *Stores the installation script for blog module.
 */

namespace JarisCMS\Module\Blog;

function Install()
{
    $string = t("Blog Post");
    $string = t("Message automatically posted on your personal blog");
    
    //Add blog type
    $blog_fields["name"] = "Blog Post";
    $blog_fields["description"] = "Message automatically posted on your personal blog";
    JarisCMS\Type\Add("blog", $blog_fields);
    
    //Create blog data base
    if(!JarisCMS\SQLite\DBExists("blog"))
    {
        $db = JarisCMS\SQLite\Open("blog");
        
        JarisCMS\SQLite\Query("create table blogs (id integer primary key, created_timestamp text, edited_timestamp text, title text, description text, tags text, views integer, user text, category text)", $db);
        
        JarisCMS\SQLite\Query("create index blogs_index on blogs (created_timestamp desc, title desc, description desc, tags desc, views desc, user desc, category desc)", $db);
        
        JarisCMS\SQLite\Close($db);
    }
    
    //Create blog subscriptions data base
    if(!JarisCMS\SQLite\DBExists("blog_subscriptions"))
    {
        $db = JarisCMS\SQLite\Open("blog_subscriptions");
        
        JarisCMS\SQLite\Query("create table subscriptions (id integer primary key, user text, subscriber text, created_timestamp text)", $db);
        
        JarisCMS\SQLite\Query("create index subscriptions_index on subscriptions (user desc, subscriber desc, created_timestamp desc)", $db);
        
        JarisCMS\SQLite\Close($db);
    }
    
    //Add user blog archive navigation
    $text = t("Blog Archive");
    $block_blog_archive["description"] = "User Blog Archive";
    $block_blog_archive["title"] = "Blog Archive";
    $block_blog_archive["content"] = '<?php  
      
    $current_year = date("Y", time());
    $current_month = date("n", time());
    $username = "";
    
    //Get blogs username
    if(JarisCMS\Page\GetType(JarisCMS\URI\Get()) == "blog")
    {
        $page_data = JarisCMS\Page\GetData(JarisCMS\URI\Get());
        $username = $page_data["author"]; 
    }
    else
    {
        $uri_parts = explode("/", JarisCMS\URI\Get());
        $username = $uri_parts[2];
    }
    
    $user_data = JarisCMS\User\GetData($username);
    $database_path = JarisCMS\User\GeneratePath($username, $user_data["group"]);
    $database_path = str_replace("data.php", "", $database_path);
    
    if(JarisCMS\SQLite\DBExists("blog", $database_path))
    {
        $months[1] = t("January");
        $months[2] = t("February");
        $months[3] = t("March");
        $months[4] = t("April");
        $months[5] = t("May");
        $months[6] = t("June");
        $months[7] = t("July");
        $months[8] = t("August");
        $months[9] = t("September");
        $months[10] = t("October");
        $months[11] = t("November");
        $months[12] = t("December");
        
        $months_found = 0;
        $total_post_checked = 0;
        $total_post_count = JarisCMS\SQLite\CountColumn("blog", "post", "id", "", $database_path);
        
        while($months_found <= 12 && $total_post_checked < $total_post_count)
        {
            $post_count = JarisCMS\SQLite\CountColumn("blog", "post", "id", "where month=\'$current_month\' and year=\'$current_year\'", $database_path);
            
            if($post_count > 0)
            {
                $months_found++;
                $total_post_checked += $post_count;
                
                $url = JarisCMS\URI\PrintURL("blog/user/$username", array("m"=>$current_month, "y"=>$current_year));
                print "
                <div class=\"blog-archive-link\">
                <a href=\"$url\">" . 
                "<span class=\"month\">" . $months[$current_month] . "</span> " . 
                "<span class=\"year\">" . $current_year . "</span></a> 
                <span class=\"blog-archive-count\">
                (<span class=\"number\">$post_count</span>)
                </span>
                </div>" . "\n";
            }
            
            if($current_month == 1)
            {
                $current_year--;
                $current_month = 12;
            }
            else
            {
                $current_month--;
            }
        }
    }
    ?>';
    $block_blog_archive["order"] = "-1";
    $block_blog_archive["display_rule"] = "all_except_listed";
    $block_blog_archive["pages"] = "";
    $block_blog_archive["return"] = '<?php 
    if(JarisCMS\Page\GetType(JarisCMS\URI\Get()) == "blog" || "" . strpos(JarisCMS\URI\Get(), "blog/user/") . "" != "")
    {
        print "true";
    }
    else
    {
        print "false";
    }
    ?>';
    $block_blog_archive["is_system"] = true;
    $block_blog_archive["block_name"] = "blog_user_archive";
    
    JarisCMS\Block\Add($block_blog_archive, "left");
    
    
    //Add recent user posts
    $text = t("Recent Posts by This User");
    $block_recent_post["description"] = "5 Recent User Posts";
    $block_recent_post["title"] = "Recent Posts by This User";
    $block_recent_post["content"] = '<?php 
    
    $page_data = JarisCMS\Page\GetData(JarisCMS\URI\Get());
    $username = $page_data["author"];
    $user_data = JarisCMS\User\GetData($username);
    $database_path = JarisCMS\User\GeneratePath($username, $user_data["group"]);
    $database_path = str_replace("data.php", "", $database_path);
    
    $db = JarisCMS\SQLite\Open("blog", $database_path);
    
    $select = "select 
    * from post
    order by created_timestamp desc limit 0, 5"; 
    
    $result = JarisCMS\SQLite\Query($select, $db);
    
    print "<div class=\"blog-recent-post\">\n";
    print "<ul>\n";
    while($data = JarisCMS\SQLite\FetchArray($result))
    {
        $post_data = JarisCMS\Page\GetData($data["uri"]);
        
        print "<li><a href=\"" . JarisCMS\URI\PrintURL($data["uri"]) . "\">" . $post_data["title"] . "</a></li>\n";
    }
    print "</ul>\n";
    print "</div>\n";
    
    JarisCMS\SQLite\Close($db);
    ?>';
    $block_recent_post["order"] = "-2";
    $block_recent_post["display_rule"] = "all_except_listed";
    $block_recent_post["pages"] = "";
    $block_recent_post["return"] = '<?php
    if(JarisCMS\Page\GetType(JarisCMS\URI\Get()) == "blog")
    {
        print "true";
    }
    else
    {
        print "false";
    }
    ?>';
    $block_recent_post["is_system"] = true;
    $block_recent_post["block_name"] = "blog_recent_user_posts";
    
    JarisCMS\Block\Add($block_recent_post, "left");
    
    
    //Add new created blogs
    $text = t("New Blogs");
    $block_new_blogs["description"] = "10 newly created blogs";
    $block_new_blogs["title"] = "New Blogs";
    $block_new_blogs["content"] = '<?php 
    JarisCMS\System\AddStyle("modules/blog/styles/list.css");
    
    $db = JarisCMS\SQLite\Open("blog");
    
    $select = "select 
    title, description, user, created_timestamp, views from blogs
    order by created_timestamp desc limit 0, 10"; 
    
    $result = JarisCMS\SQLite\Query($select, $db);
    
    while($data = JarisCMS\SQLite\FetchArray($result))
    {
        $user_data = JarisCMS\User\GetData($data["user"]);
                
        if($user_data["picture"])
        {
            $picture = JarisCMS\URI\PrintURL("image/user/" . $data["user"]);
        }
        else
        {
            $picture = JarisCMS\URI\PrintURL("modules/blog/images/no-picture.png");
        }
        
        $user_url = JarisCMS\URI\PrintURL("blog/user/" . $data["user"]);
        $title = trim($data["title"]) != ""?$data["title"]:$data["user"] . " " . t("blog");
        
        print "<div class=\"blog-list blog-recent-blogs\">\n";
        
        print "<div class=\"title\"><a href=\"" . $user_url . "\">" . $title . "</a></div>\n";
        
        print "<div class=\"thumbnail\">
        <a title=\"$title\" href=\"" . $user_url . "\"><img alt=\"$title\" src=\"$picture\" /></a>
        </div>\n";
                
        print "<div class=\"views\"><span class=\"label\">" . t("Views:") . "</span> " . $data["views"] . "</div>\n";
        print "<div class=\"user\"><span class=\"label\">" . t("Created by:") . "</span> <a href=\"$user_url\">" . $data["user"] . "</a></div>\n";
        
        if($data["description"])
        {
            print "<div class=\"description\">" . $data["description"] . "</div>\n";
        }
        print "<div style=\"clear: both\"></div>\n";
        print "</div>\n";
    }
    
    JarisCMS\SQLite\Close($db);
    ?>';
    $block_new_blogs["order"] = "0";
    $block_new_blogs["display_rule"] = "all_except_listed";
    $block_new_blogs["pages"] = "";
    $block_new_blogs["return"] = '';
    $block_new_blogs["is_system"] = true;
    $block_new_blogs["block_name"] = "blog_new_blogs";
    
    JarisCMS\Block\Add($block_new_blogs, "none");
    
    //Add most viewed blogs block
    $text = t("Most Viewed Blogs");
    $text = t("Views:");
    $text = t("Created by:");
    $block_most_viewed_blogs["description"] = "Top 10 Most Viewed Blogs";
    $block_most_viewed_blogs["title"] = "Most Viewed Blogs";
    $block_most_viewed_blogs["content"] = '<?php 
    JarisCMS\System\AddStyle("modules/blog/styles/list.css");
    
    $db = JarisCMS\SQLite\Open("blog");
    
    $select = "select 
    title, user, views from blogs 
    order by views desc, created_timestamp desc limit 0, 10"; 
    
    $result = JarisCMS\SQLite\Query($select, $db);
    
    while($data = JarisCMS\SQLite\FetchArray($result))
    {
        $user_data = JarisCMS\User\GetData($data["user"]);
                
        if($user_data["picture"])
        {
            $picture = JarisCMS\URI\PrintURL("image/user/" . $data["user"]);
        }
        else
        {
            $picture = JarisCMS\URI\PrintURL("modules/blog/images/no-picture.png");
        }
        
        $user_url = JarisCMS\URI\PrintURL("blog/user/" . $data["user"]);
        $title = trim($data["title"]) != ""?$data["title"]:$data["user"] . " " . t("blog");
        
        print "<div class=\"blog-list blog-most-viewed-blogs\">\n";
        
        print "<div class=\"title\"><a href=\"" . JarisCMS\URI\PrintURL($user_url) . "\">" . $title . "</a></div>\n";
        
        print "<div class=\"thumbnail\">
        <a title=\"$title\" href=\"" . $user_url . "\"><img alt=\"$title\" src=\"$picture\" /></a>
        </div>\n";
        
        print "<div class=\"details\">\n";
        print "<div class=\"views\"><span class=\"label\">" . t("Views:") . "</span> " . $data["views"] . "</div>\n";
        print "<div class=\"user\"><span class=\"label\">" . t("Created by:") . "</span> <a href=\"$user_url\">" . $data["user"] . "</a></div>\n";
        print "</div>\n";
        print "<div style=\"clear: both\"></div>\n";
        
        print "</div>\n";
    }
    
    JarisCMS\SQLite\Close($db);
    ?>';
    $block_most_viewed_blogs["order"] = "0";
    $block_most_viewed_blogs["display_rule"] = "all_except_listed";
    $block_most_viewed_blogs["pages"] = "";
    $block_most_viewed_blogs["return"] = '';
    $block_most_viewed_blogs["is_system"] = true;
    $block_most_viewed_blogs["block_name"] = "blog_most_viewed_blogs";
    
    JarisCMS\Block\Add($block_most_viewed_blogs, "none");
    
    
    //Add navigate by categories blogs block
    $text = t("Categories");
    $block_categories_blog["description"] = "Navigate blogs by categories";
    $block_categories_blog["title"] = "Categories";
    $block_categories_blog["content"] = '<?php 
    $settings = JarisCMS\Module\Blog\GetSettings();
    if($settings["main_category"] != "")
    {
        $subcategories = JarisCMS\Category\GetChildrenList($settings["main_category"]);
        
        print "<ul class=\"blog-categories\">";
        print "<li><a href=\"" . JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("blog/browser", "blog")) . "\">" . t("All") . "</a></li>";
        foreach($subcategories as $id=>$data)
        {
            print "<li><a href=\"" . JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("blog/browser", "blog"), array("c"=>$id)) . "\">" . t($data["title"]) . "</a></li>";
        }
        print "<li><a href=\"" . JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("blog/browser", "blog"), array("c"=>-1)) . "\">" . t("Other") . "</a></li>";
        print "</ul>";
    }
    ?>';
    
    $block_categories_blog["order"] = "0";
    $block_categories_blog["display_rule"] = "just_listed";
    $block_categories_blog["pages"] = "blog/browser";
    $block_categories_blog["return"] = '';
    $block_categories_blog["is_system"] = true;
    $block_categories_blog["block_name"] = "blog_categories_blogs";
    
    JarisCMS\Block\Add($block_categories_blog, "left");
    
    JarisCMS\System\AddMessage(t("Remember to set the blog configurations to work properly.") . " <a href=\"" . JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/blog", "blog")) . "\">" . t("Configure Now") . "</a>");
}

?>