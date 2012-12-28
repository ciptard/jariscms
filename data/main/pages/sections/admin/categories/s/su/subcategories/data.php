<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the subcategories configuration page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Subcategories") ?>
	field;

	field: content
		<script>
			$(document).ready(function(){
				var fixHelper = function(e, ui) {
					ui.children().each(function() {
						$(this).width($(this).width());
					});
					return ui;
				};
			
				$(".subcategories-list tbody").sortable({ 
					cursor: 'crosshair', 
					helper: fixHelper,
					handle: "a.sort-handle"
				});
			});
		</script>
		
		<?php
			JarisCMS\Security\ProtectPage(array("view_subcategories"));
			
			JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.js");
			JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.touch-punch.min.js");

            JarisCMS\System\AddTab(t("Categories"), "admin/categories");
			JarisCMS\System\AddTab(t("Create Subcategory"), "admin/categories/subcategories/add", array("category"=>$_REQUEST["category"]));
			
			function print_subcategories($category_name, $parent="root", $position="")
			{
                $category_data = JarisCMS\Category\GetData($category_name);
                
                if(!$category_data["sorting"])
                {
				    $subcategories_list = JarisCMS\PHPDB\Sort(JarisCMS\Category\GetChildrenRecursively($category_name, $parent), "order");
                }
                else
                {
                    $subcategories_list = JarisCMS\PHPDB\Sort(JarisCMS\Category\GetChildrenRecursively($category_name, $parent), "title");
                }
				
				if($subcategories_list)
				{
					foreach($subcategories_list as $id => $fields)
					{
						$select = "<select name=\"parent[]\">\n";
						$subcategories_for_parent["root"] = array("title"=>"&lt;root&gt;");
						$subcategories_for_parent += JarisCMS\Category\GetChildrenList($category_name);
						foreach($subcategories_for_parent as $select_id=>$select_fields)
						{
							$selected = "";
							if("" . $fields["parent"] . "" == "" . $select_id . "")
							{
								$selected = "selected";
							}
							
							if("" . $select_id . "" != "" . $id . "")
							{
								$select .= "<option $selected value=\"$select_id\">" . t($select_fields['title']) . "</option>\n";	
							}
						}
						$select .= "</select>";
	
						print "<tr>\n";
	
						print "<td>\n
						<a class=\"sort-handle\"></a>
						<input type=\"hidden\" name=\"subcategory_id[]\" value=\"$id\" />\n
						<input size=\"3\" class=\"form-text\" type=\"hidden\" name=\"order[]\" value=\"" . $fields["order"] . "\" />\n
						</td>\n";
						
						print "<td>$position" . t($fields['title']) . "</td>\n";
						
						print "<td>"  . $select . "</td>\n";
	
						$url_arguments["id"] = $id;
						$url_arguments["category"] = $category_name;
	
						print "<td>
						<a href=\"" . JarisCMS\URI\PrintURL("admin/categories/subcategories/edit", $url_arguments) . "\">" . t("Edit") . "</a>
						&nbsp;
						<a href=\"" . JarisCMS\URI\PrintURL("admin/categories/subcategories/delete", $url_arguments) . "\">" . t("Delete") . "</a>
						</td>";
	
						print "</tr>\n";
						
						print_subcategories($category_name, "$id", $position . "&nbsp;&nbsp;&nbsp;");
					}
				}
			}
			
			if(isset($_REQUEST["btnSave"]))
			{
				$saved = true;

				for($i = 0; $i<count($_REQUEST["subcategory_id"]); $i++)
				{
					$subcategory_data = JarisCMS\Category\GetChildData($_REQUEST["category"], $_REQUEST["subcategory_id"][$i]);
					$subcategory_data["order"] = $i;
					
					//Checks if client is trying to move a root parent subcategory to its own subcategory and makes subs category root
					if($subcategory_data["parent"] == "root" && $_REQUEST["parent"][$i] != "root")
					{
						$new_parent_subcategory = JarisCMS\Category\GetChildData($_REQUEST["category"], $_REQUEST["parent"][$i]);
						
						if("" . $new_parent_subcategory["parent"] . "" == "" . $_REQUEST["subcategory_id"][$i] . "")
						{
							$new_parent_subcategory["parent"] = "root";
							JarisCMS\Category\EditChild($_REQUEST["category"], $new_parent_subcategory, $_REQUEST["parent"][$i]);
						}
					}
					
					$subcategory_data["parent"] = $_REQUEST["parent"][$i];
					

					if(!JarisCMS\Category\EditChild($_REQUEST["category"], $subcategory_data, $_REQUEST["subcategory_id"][$i]))
					{
						JarisCMS\System\AddMessage($_REQUEST["category"]);
						JarisCMS\System\AddMessage($_REQUEST["subcategory_id"][$i]);
						$saved = false;
						break;
					}
				}

				if($saved)
				{
					JarisCMS\System\AddMessage(t("Your changes have been saved."));
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
				}

				JarisCMS\System\GoToPage("admin/categories/subcategories", array("category"=>$_REQUEST["category"]));
			}
		?>
		
		<form action="<?php print JarisCMS\URI\PrintURL("admin/categories/subcategories"); ?>" method="post">
		<input type="hidden" name="category" value="<?php print $_REQUEST["category"] ?>" />
		
		<?php
			$main_category = JarisCMS\Category\GetData($_REQUEST["category"]);
			
			print "<table class=\"subcategories\">";

			print "<tr><td class=\"name\"><h3>" . t(t($main_category["name"])) . "</h3></td>\n";

			print "</tr></table>";

			$subcategories_list = JarisCMS\PHPDB\Sort(JarisCMS\Category\GetChildrenList($_REQUEST["category"]), "order");
			
			if(count($subcategories_list) > 0 && $subcategories_list != false)
			{
				print "<table class=\"subcategories-list\">\n";

				print "<thead><tr>\n";

				print "<td>" . t("Order") . "</td>\n";
				print "<td>" . t("Title") . "</td>\n";
				print "<td>" . t("Parent") . "</td>\n";
				print "<td>" . t("Operation") . "</td>\n";

				print  "</tr></thead>\n";

				print "<tbody>\n";
				
				print_subcategories($_REQUEST["category"]);
				
				print "</tbody>\n";

				print "</table>\n";
			}
			else
			{
				print t("No subcategories available.") . "<br />\n";
			}
		?>
		
		<div>
		<br />
		<input class="form-submit" type="submit" name="btnSave" value="<?php print t("Save") ?>" />
		&nbsp;
		<input class="form-submit" type="submit" name="btnCancel" value="<?php print t("Cancel") ?>" />
		</div>
		</form>
	field;
	
	field: is_system
		1
	field;
row;