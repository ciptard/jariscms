<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the input formats configurations page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0

	field: title
		<?php print t("Input Formats") ?>
	field;

	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("view_input_formats"));
            
            if(JarisCMS\Group\GetPermission("add_input_formats", JarisCMS\Security\GetCurrentUserGroup()))
            {
			     JarisCMS\System\AddTab(t("Create Input Format"), "admin/input-formats/add");
            }

			$input_formats_array = array();
			$input_formats_array = JarisCMS\InputFormat\GetList();

			print "<table class=\"types-list\">\n";

			print "<thead><tr>\n";

			print "<td>" . t("Name") . "</td>\n";
			print "<td>" . t("Description") . "</td>\n";
            
            if(JarisCMS\Group\GetPermission("edit_input_formats", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Group\GetPermission("delete_input_formats", JarisCMS\Security\GetCurrentUserGroup()))
			{
                print "<td>" . t("Operation") . "</td>\n";
            }

			print  "</tr></thead>\n";

			foreach($input_formats_array as $machine_name=>$fields)
			{
				print "<tr>\n";

				print "<td>" . t($fields["name"]) . "</td>\n";
				print "<td>" . t($fields["description"]) . "</td>\n";

				$edit_url = JarisCMS\URI\PrintURL("admin/input-formats/edit",array("input_format"=>$machine_name));
				$delete_url = JarisCMS\URI\PrintURL("admin/input-formats/delete", array("input_format"=>$machine_name));
				
				$edit_text = t("Edit");
				$delete_text = t("Delete");

                if(JarisCMS\Group\GetPermission("edit_input_formats", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Group\GetPermission("delete_input_formats", JarisCMS\Security\GetCurrentUserGroup()))
                {
				    print "<td>";
                    if(JarisCMS\Group\GetPermission("edit_input_formats", JarisCMS\Security\GetCurrentUserGroup()))
                    {
					   print "<a href=\"$edit_url\">$edit_text</a>&nbsp;";
                    }
                    
                    if(JarisCMS\Group\GetPermission("delete_input_formats", JarisCMS\Security\GetCurrentUserGroup()))
                    {
					   print "<a href=\"$delete_url\">$delete_text</a>";
                    }
					print "</td>\n";
                }
                
                print "</tr>\n";
			}

			print "</table>\n";
            
            if(count($input_formats_array) <= 0)
            {
                JarisCMS\System\AddMessage(t("No custom input formats available."));
            }
		?>
	field;

	field: is_system
		1
	field;
row;
