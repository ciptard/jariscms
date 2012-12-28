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
		<?php print t("My Content") ?>
	field;
	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("add_content"));
            
            $type = "";
            if(trim($_REQUEST["type"]) != "")
            {
                $type = str_replace("'", "''", $_REQUEST["type"]);
                $type = "and type='$type'";
            }
            
            $types_array = JarisCMS\Type\GetList(JarisCMS\Security\GetCurrentUserGroup());
            
            print "<form method=\"get\" action=\"" . JarisCMS\URI\PrintURL("admin/user/content") . "\">\n";
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
            print "</form>\n";
            
            $page = 1;
			
			if(isset($_REQUEST["page"]))
			{
				$page = $_REQUEST["page"];
			}
            
            $user = JarisCMS\Security\GetCurrentUser();
            
            $pages_count = JarisCMS\SQLite\CountColumn("search_engine", "uris", "uri", "where author='$user' $type");
			
			print "<h2>" . t("Total content:") . " " . $pages_count . "</h2>";
			
			$pages = JarisCMS\SQLite\GetDataList("search_engine", "uris", $page - 1, 20, "where author='$user' $type order by created_date desc");
			
			JarisCMS\System\PrintGenericNavigation($pages_count, $page, "admin/user/content", "", 20, array("type"=>$_REQUEST["type"]));
			
			print "<table class=\"navigation-list\">";
			print "<thead>";
			print "<tr>";
			print "<td>" . t("Title") . "</td>";
            print "<td>" . t("Dates") . "</td>";
            print "<td>" . t("Type") . "</td>";
            
            if(JarisCMS\Group\GetPermission("edit_content", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Group\GetPermission("delete_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                print "<td>" . t("Operation") . "</td>";
            }
            
			print "</tr>";
			print "</thead>";
			
			foreach($pages as $data)
			{
				$page_data = JarisCMS\Page\GetData($data["uri"]);
                $type_data = JarisCMS\Type\GetData($page_data["type"]);
                $type = $page_data["type"]?t($type_data["name"]):t("system");
				
				print "<tr>";
				
				print "<td><a href=\"" . JarisCMS\URI\PrintURL($data["uri"]) . "\">" . JarisCMS\System\PHPEval($page_data["title"]) . "</a></td>";
                
                print 
                "<td>" . 
                    t("Created:") . " " . date("m/d/Y g:i:s a", $page_data["created_date"]) . "<br />" .
                    t("Edited:") . " " . date("m/d/Y g:i:s a", $page_data["last_edit_date"]) .
                "</td>";
                
                print "<td>" . $type . "</td>";
				
				$edit_url = JarisCMS\URI\PrintURL("admin/pages/edit", array("uri"=>$data["uri"]));
				$delete_url = JarisCMS\URI\PrintURL("admin/pages/delete", array("uri"=>$data["uri"]));
				
                if(JarisCMS\Group\GetPermission("edit_content", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Group\GetPermission("delete_content", JarisCMS\Security\GetCurrentUserGroup()))
                {
    				print "<td>";
                    if(JarisCMS\Group\GetPermission("edit_content", JarisCMS\Security\GetCurrentUserGroup()))
                    { 
    				    print "<a href=\"$edit_url\">" . t("Edit") . "</a> <br />";
                    }
                    if(JarisCMS\Group\GetPermission("delete_content", JarisCMS\Security\GetCurrentUserGroup()))
                    {
    				    print "<a href=\"$delete_url\">" . t("Delete") . "</a>";
                    }					
    			 	print "</td>";
                }
				
				print "</tr>";
			}
			
			print "</table>";
			
			JarisCMS\System\PrintGenericNavigation($pages_count, $page, "admin/user/content", "", 20, array("type"=>$_REQUEST["type"]));
		?>
	field;

	field: is_system
		1
	field;
row;
