<?php
/**
 *Copyright 2008, Jefferson Gonzàlez (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the user list page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Flagged Comments List") ?>
	field;
	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("manage_comments_flags"));
           
            $page = 1;
			
			if(isset($_REQUEST["page"]))
			{
				$page = $_REQUEST["page"];
			}
            
            
            $flags_count = JarisCMS\SQLite\CountColumn("comments", "comments", "id", "where flags > 0");
			
			print "<h2>" . t("Total flags:") . " " . $flags_count . "</h2>";
			
			$flags = JarisCMS\Module\Comments\GetFlaggedList($page - 1);
			
			JarisCMS\System\PrintGenericNavigation($flags_count, $page, JarisCMS\Module\GetPageURI("admin/comments/flags", "comments"));
			
			print "<table class=\"navigation-list\">";
			print "<thead>";
			print "<tr>";
			print "<td>" . t("Page Title") . "</td>";
			print "<td>" . t("Comment") . "</td>";
            print "<td>" . t("User") . "</td>";
            print "<td>" . t("Added on") . "</td>";
            print "<td>" . t("Flags") . "</td>";
            print "<td>" . t("Actions") . "</td>";
			print "</tr>";
			print "</thead>";
			
			foreach($flags as $data)
			{
				$comment_data = JarisCMS\Module\Comments\GetData($data["id"], $data["uri"]);
                $page_data = JarisCMS\Page\GetData($data["uri"]);
				
				print "<tr>";
				
				print "<td><a href=\"" . JarisCMS\URI\PrintURL($data["uri"]) . "\">" . JarisCMS\System\PHPEval($page_data["title"]) . "</a></td>";
				
				print "<td>" . $comment_data["comment_text"] . "</td>";
                
                print "<td>" . $comment_data["user"] . "</td>";
                
                print "<td>" . date("m/d/Y", $comment_data["created_timestamp"]) . "</td>";
                
                print "<td>" . $data["flags"] . "</td>";
				
				$delete_url = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/comments/flags/delete", "comments"), array("id"=>$comment_data["id"], "user"=>$comment_data["user"], "page"=>$data["uri"]));
                
                $remove_flags_url = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/comments/flags/remove", "comments"), array("id"=>$comment_data["id"], "user"=>$comment_data["user"], "page"=>$data["uri"]));
				
				print "<td>" . 
				"<a href=\"$delete_url\">" . t("Delete") . "</a><br />" .	
                "<a href=\"$remove_flags_url\">" . t("Unflag") . "</a>" .
			 	"</td>";
				
				print "</tr>";
			}
			
			print "</table>";
			
			JarisCMS\System\PrintGenericNavigation($flags_count, $page, JarisCMS\Module\GetPageURI("admin/comments/flags", "comments"));
		?>
	field;

	field: is_system
		1
	field;
row;
