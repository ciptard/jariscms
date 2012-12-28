<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the view user post page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0

	field: title
		<?php
            $blog_data = JarisCMS\Module\Blog\GetFromDB($_REQUEST["user"]);
            
            if($blog_data["title"])
            {
                print $blog_data["title"];
            }
            else
            {
                print $_REQUEST["user"];
            }
            print " " . t("blog");
        ?>
	field;

	field: content
		<?php
            JarisCMS\System\AddStyle("modules/blog/styles/post.css");
            
            $user_data = JarisCMS\User\GetData($_REQUEST["user"]);
            
            if(JarisCMS\Security\IsUserLogged() && JarisCMS\Security\GetCurrentUser() == $_REQUEST["user"])
            {
                if(JarisCMS\Group\GetPermission("add_content", $user_data["group"]) && JarisCMS\Group\GetTypePermission("blog", $user_data["group"]))
                {
                    JarisCMS\System\AddTab(t("Manage Blog"), JarisCMS\Module\GetPageURI("user/blog", "blog"));
            
                    JarisCMS\System\AddTab(t("Add Post"), JarisCMS\Module\GetPageURI("admin/pages/add", "blog"), array("type"=>"blog"));
                }
            }
            
            JarisCMS\System\AddTab(t("Subscriptions"), JarisCMS\Module\GetPageURI("blog/subscriptions", "blog"), array("user"=>$_REQUEST["user"]));
            
            
            if(JarisCMS\Security\IsUserLogged() && JarisCMS\Security\GetCurrentUser() != $_REQUEST["user"])
            {
                if(!JarisCMS\Module\Blog\Subscribed($_REQUEST["user"], JarisCMS\Security\GetCurrentUser()))
                {
                    JarisCMS\System\AddTab(t("Subscribe"), JarisCMS\Module\GetPageURI("blog/subscribe", "blog"), array("user"=>$_REQUEST["user"]));
                }
                else
                {
                    JarisCMS\System\AddTab(t("Unsubscribe"), JarisCMS\Module\GetPageURI("blog/unsubscribe", "blog"), array("user"=>$_REQUEST["user"]));
                }
            }
            
            $blog_data = JarisCMS\Module\Blog\GetFromDB($_REQUEST["user"]);
            
            if($blog_data["description"])
            {
                print "<div class=\"blog-description\">{$blog_data['description']}</div>";
            }
            
            $page = 1;
            
            if(isset($_REQUEST["page"]))
            {
            	$page = $_REQUEST["page"];
            }
            
            $month_query = "";
            $year_query = "";
            $where = "";
            
            $arguments = null;
            
            if(isset($_REQUEST["m"]))
            {
            	$_REQUEST["m"] = intval($_REQUEST["m"]);
            	
                $month = str_replace("'", "''", $_REQUEST["m"]);
                $month_query = "month='$month' and ";
                
                $arguments["m"] = $_REQUEST["m"];
            }
            
            if(isset($_REQUEST["y"]))
            {
            	$_REQUEST["y"] = intval($_REQUEST["y"]);
            	
                $year = str_replace("'", "''", $_REQUEST["y"]);
                $year_query = "year='$year'";
                
                $arguments["y"] = $_REQUEST["y"];
            }
            
            if(isset($_REQUEST["m"]) || isset($_REQUEST["y"]))
            {
                $where = "where {$month_query}{$year_query}";
            }
            
            $database_path = str_replace("data.php", "", JarisCMS\User\GeneratePath($_REQUEST["user"], $user_data["group"]));
            
            $post_count = JarisCMS\SQLite\CountColumn("blog", "post", "id", $where, $database_path);
            
            $post = JarisCMS\SQLite\GetDataList("blog", "post", $page - 1, 10, $where . "order by created_timestamp desc", "*", $database_path);
            
            JarisCMS\System\PrintGenericNavigation($post_count, $page, "blog/user/" . $_REQUEST["user"], "", 10, $arguments);
            
            foreach($post as $post_data)
            {
                print JarisCMS\Module\Blog\Theme($post_data);
            }
            
            JarisCMS\System\PrintGenericNavigation($post_count, $page, "blog/user/" . $_REQUEST["user"], "", 10, $arguments);
		?>
	field;

	field: is_system
		1
	field;
row;
