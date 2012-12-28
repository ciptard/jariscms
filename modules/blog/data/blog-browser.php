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
		<?php print t("Blog Browser") ?>
	field;

	field: content
		<?php 
            JarisCMS\System\AddStyle("modules/blog/styles/list.css");
            
            $page = 1;
			
			if(isset($_REQUEST["page"]))
			{
				$page = $_REQUEST["page"];
			}
            
            $category = "";
            
            if(isset($_REQUEST["c"]))
            {
                $category = $_REQUEST["c"];
                $category = str_replace("'", "''", $category);
            }
            
            $blogs_count = 0;
            if($category != "")
            {
                $blogs_count = JarisCMS\SQLite\CountColumn("blog", "blogs", "id", "where category='$category'");
            }
            else
            {
                $blogs_count = JarisCMS\SQLite\CountColumn("blog", "blogs", "id");
            }
			
			print "<h2>" . t("Total Blogs:") . " " . $blogs_count . "</h2>";
			
            $blogs = array();
            if($category != "")
            {
                $blogs = JarisCMS\SQLite\GetDataList("blog", "blogs", $page-1, 20, "where category='$category' order by created_timestamp desc");
            }
            else
            {
                $blogs = JarisCMS\SQLite\GetDataList("blog", "blogs", $page-1, 20);
            }
			
			JarisCMS\System\PrintGenericNavigation($blogs_count, $page, "blog/browser", "blog", 20, array("c"=>$_REQUEST["c"]));
			
			foreach($blogs as $data)
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
                
                print "<div class=\"blog-list\">\n";
                print "<div class=\"title\"><a title=\"{$data["title"]}\" href=\"" . $user_url . "\">" . $data["title"] . "</a></div>\n";
                print "<div class=\"thumbnail\">
                <a title=\"{$data["title"]}\" href=\"" . $user_url . "\"><img alt=\"{$data["title"]}\" src=\"$picture\" /></a>
                </div>\n";
                print "<div class=\"details\">\n";
                print "<div class=\"views\">" . t("Views:") . " " . $data["views"] . "</div>\n";
                print "<div class=\"user\">" . t("Created by:") . " <a href=\"$user_url\">" . $data["user"] . "</a></div>\n";
                print "</div>\n";
                
                if($data["description"])
                {
                    print "<div class=\"description\">" . $data["description"] . "</div>\n";
                }
        
                print "<div style=\"clear: both\"></div>\n";
                print "</div>\n";
            }
            
            if(count($blogs) <= 0)
            {
                JarisCMS\System\AddMessage(t("No available blogs on the system yet. Register an account and start posting"));
            }
            
            print "<div style=\"clear: both\"></div>\n";
			
			JarisCMS\System\PrintGenericNavigation($blogs_count, $page, "blog/browser", "blog", 20, array("c"=>$_REQUEST["c"]));
        ?>
	field;

	field: is_system
		1
	field;
row;
