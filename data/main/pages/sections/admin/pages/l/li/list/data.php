<?php
/**
 *Copyright 2008, Jefferson Gonzï¿½lez (JegoYalu.com)
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
		<?php print t("Pages List") ?>
	field;
	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("view_content"));
            
            JarisCMS\System\AddTab(t("Navigation View"), "admin/pages");
            JarisCMS\System\AddTab(t("Create Page"), "admin/pages/types");
            
            $type = "";
            if(trim($_REQUEST["type"]) != "")
            {
            	$type = str_replace("'", "''", $_REQUEST["type"]);
            	$type = "type='$type'";
            }
            
            $types_array = JarisCMS\Type\GetList();
            
            $author = "";
            if(trim($_REQUEST["author"]) != "")
            {
            	$username = str_replace("'", "''", $_REQUEST["author"]);
            	
            	if($type)
            		$author = "and ";
            
            	$author .= "author='$username'";
            }
            
            $where = "";
            if($type || $author)
            {
            	$where = "where ";
            }
            
            $page = 1;
            
            if(isset($_REQUEST["page"]))
            {
            	$page = $_REQUEST["page"];
            }
            
            print "<form method=\"get\" action=\"" . JarisCMS\URI\PrintURL("admin/pages/list") . "\">\n";
			print "<div style=\"float: left\">";
            print t("Filter by type:") . " <select onchange=\"javascript: this.form.submit()\" name=\"type\">\n";
            print "<option value=\"\">" . t("All") . "</option>\n";
            foreach($types_array as $machine_name=>$type_data)
            {
            	$selected = "";
            
            	if($_REQUEST["type"] == $machine_name)
            	{
            		$selected = "selected=\"selected\"";
            	}
            
            	print "<option $selected value=\"$machine_name\">{$type_data['name']}</option>\n";
            }
            print "</select>\n";
            print "</div>";
            
            print "<div style=\"float: right\">";
            print t("Username:") . " <input style=\"width: 80px;\" type=\"text\" name=\"author\" value=\"{$_REQUEST["author"]}\">";
            print " <input type=\"submit\" value=\"".t("Submit")."\">\n";
            print "</div>";
            
            print "</form>\n";
            
            print "<div style=\"clear: both\"></div>";
			
			$pages_count = JarisCMS\SQLite\CountColumn("search_engine", "uris", "uri", "$where $type $author");
			
			print "<h2>" . t("Total content:") . " " . $pages_count . "</h2>";
			
			$pages = JarisCMS\SQLite\GetDataList("search_engine", "uris", $page - 1, 20, "$where $type $author order by created_date desc");
           
			JarisCMS\System\PrintGenericNavigation($pages_count, $page, "admin/pages/list", "", 20, array("type"=>$_REQUEST["type"], "author"=>$_REQUEST["author"]));
			
			print "<table class=\"navigation-list\">";
			print "<thead>";
			print "<tr>";
			print "<td>" . t("Title") . "</td>";
			print "<td>" . t("Author") . "</td>";
            print "<td>" . t("Dates") . "</td>";
            print "<td>" . t("Type") . "</td>";
			print "<td>" . t("Operation") . "</td>";
			print "</tr>";
			print "</thead>";
			
			foreach($pages as $result_fields)
			{
				$uri = $result_fields["uri"];
				
				$page_data = JarisCMS\Page\GetData($uri);
                $author = $page_data["author"]?$page_data["author"]:t("system");
                $type_data = JarisCMS\Type\GetData($page_data["type"]);
                $type = $page_data["type"]?t($type_data["name"]):t("system");
				
				print "<tr>";
				
				print "<td>" . JarisCMS\System\PHPEval($page_data["title"]) . "</td>";
				
				print "<td>" . $author . "</td>";
                
                print 
                "<td>" . 
                    t("Created:") . " " . date("m/d/Y g:i:s a", $page_data["created_date"]) . "<br />" .
                    t("Edited:") . " " . date("m/d/Y g:i:s a", $page_data["last_edit_date"]) .
                "</td>";
                
                print "<td>" . $type . "</td>";
				
				$edit_url = JarisCMS\URI\PrintURL("admin/pages/edit", array("uri"=>$uri));
				$delete_url = JarisCMS\URI\PrintURL("admin/pages/delete", array("uri"=>$uri));
				
				print "<td>" . 
				"<a href=\"$edit_url\">" . t("Edit") . "</a> <br />" .
				"<a href=\"$delete_url\">" . t("Delete") . "</a>" .					
			 	"</td>";
				
				print "</tr>";
			}
			
			print "</table>";
			
			JarisCMS\System\PrintGenericNavigation($pages_count, $page, "admin/pages/list", "", 20, array("type"=>$_REQUEST["type"], "author"=>$_REQUEST["author"]));
		?>
	field;

	field: is_system
		1
	field;
row;
