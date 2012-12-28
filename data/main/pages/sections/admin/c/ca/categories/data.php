<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the categories configurations page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0

	field: title
		<?php print t("Categories") ?>
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
			
				$(".categories-list tbody").sortable({ 
					cursor: 'crosshair', 
					helper: fixHelper,
					handle: "a.sort-handle"
				});
			});
		</script>
		
		<?php
			JarisCMS\Security\ProtectPage(array("view_categories"));
			
			JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.js");
			JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.touch-punch.min.js");

			JarisCMS\System\AddTab(t("Create Category"), "admin/categories/add");

			$categories_array = JarisCMS\Category\GetList();
        ?>
        
        <form class="categories" action="<?php print JarisCMS\URI\PrintURL("admin/categories"); ?>" method="post">
        
        <?php
        
            if(isset($_REQUEST["btnSave"]))
			{
				$saved = true;

				for($i=0; $i<count($_REQUEST["category_name"]); $i++)
				{
					$new_category_data = JarisCMS\Category\GetData($_REQUEST["category_name"][$i]);
					$new_category_data["order"] = $i;

					if(!JarisCMS\Category\Edit($_REQUEST["category_name"][$i], $new_category_data))
					{
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

				JarisCMS\System\GoToPage("admin/categories");
			}

			print "<table class=\"categories-list\">\n";

			print "<thead><tr>\n";

			print "<td>" . t("Order") . "</td>\n";
			print "<td>" . t("Name") . "</td>\n";
			print "<td>" . t("Description") . "</td>\n";
			print "<td>" . t("Operation") . "</td>\n";

			print  "</tr></thead>\n";
			
			print "<tbody>\n";

			if(count($categories_array) > 0)
			{
				foreach($categories_array as $machine_name=>$fields)
				{
					print "<tr>\n";
	
					print "<td>" . 
					"<a class=\"sort-handle\"></a>" .
                    "<input type=\"hidden\" name=\"category_name[]\" value=\"$machine_name\" />" .
                    "<input type=\"hidden\" style=\"width: 30px;\" name=\"category_order[]\" value=\"{$fields['order']}\" />" .
                    "</td>\n";
					
					print "<td>" . t($fields["name"]) . "</td>\n";
					
					print "<td>" . t($fields["description"]) . "</td>\n";
	
					$edit_url = JarisCMS\URI\PrintURL("admin/categories/edit",array("category"=>$machine_name));
					$delete_url = JarisCMS\URI\PrintURL("admin/categories/delete", array("category"=>$machine_name));
					$subcategories_url = JarisCMS\URI\PrintURL("admin/categories/subcategories", array("category"=>$machine_name));
					$edit_text = t("Edit");
					$delete_text = t("Delete");
					$subcategories_text = t("Subcategories");
	
					print "<td>
							<a href=\"$edit_url\">$edit_text</a>&nbsp;
							<a href=\"$delete_url\">$delete_text</a>&nbsp;
							<a href=\"$subcategories_url\">$subcategories_text</a>
						   </td>\n";
	
					print "</tr>\n";
				}
			}
			
			print "</tbody>\n";
			
			print "</table>\n";
			
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
