<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
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
		<?php print t("Rated Content List") ?>
	field;
	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("navigate_ratings"));
           
            $page = 1;
			
			if(isset($_REQUEST["page"]))
			{
				$page = $_REQUEST["page"];
			}
            
            
            $ratings_count = JarisCMS\SQLite\CountColumn("ratings", "ratings", "id");
			
			print "<h2>" . t("Total Rated Content:") . " " . $ratings_count . "</h2>";
			
			$ratings = JarisCMS\SQLite\GetDataList("ratings", "ratings", $page-1, 30, "order by content_timestamp desc");
			
			JarisCMS\System\PrintGenericNavigation($flags_count, $page, JarisCMS\Module\GetPageURI("admin/ratings/list", "rating"));
			
			print "<table class=\"navigation-list\">";
			print "<thead>";
			print "<tr>";
			print "<td>" . t("Page Title") . "</td>";
			print "<td>" . t("Last Rate Date") . "</td>";
            print "<td>" . t("Points") . "</td>";
            print "<td>" . t("Total Rates") . "</td>";
			print "</tr>";
			print "</thead>";
			
			foreach($ratings as $data)
			{
                $page_data = JarisCMS\Page\GetData($data["uri"]);
				
				print "<tr>";
				
				print "<td><a href=\"" . JarisCMS\URI\PrintURL($data["uri"]) . "\">" . JarisCMS\System\PHPEval($page_data["title"]) . "</a></td>";
                
                print "<td>" . date("m/d/Y", $data["last_rate_timestamp"]) . "</td>";
				
				print "<td>" . $data["points"] . "</td>";
                
                print "<td>" . $data["rates_count"] . "</td>";
				
				print "</tr>";
			}
			
			print "</table>";
			
			JarisCMS\System\PrintGenericNavigation($flags_count, $page, JarisCMS\Module\GetPageURI("admin/ratings/list", "rating"));
		?>
	field;

	field: is_system
		1
	field;
row;
