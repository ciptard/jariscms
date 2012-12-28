<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the language edit strings page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Edit language strings") ?>
	field;

	field: content
		<?php
			JarisCMS\Security\ProtectPage(array("view_languages", "edit_languages"));
			
			//Prevent editing non existing language code
			if(trim($_REQUEST["code"]) == "")
			{
				JarisCMS\System\GoToPage("admin/languages");
			}

			$lang_code = $_REQUEST["code"];
			$current_page = $_REQUEST["current_page"]?$_REQUEST["current_page"]:1;
			
			if(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/languages/edit", array("code"=>$lang_code, "current_page"=>$current_page));
			}

			//Add string form
			if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "add")
			{
				$parameters["name"] = "add-language-string";
				$parameters["class"] = "add-language-string";
				$parameters["action"] = JarisCMS\URI\PrintURL("admin/languages/edit");
				$parameters["method"] = "get";

				$fields[] = array("type"=>"hidden", "value"=>"save_new", "name"=>"action");
				$fields[] = array("type"=>"hidden", "value"=>$current_page, "name"=>"current_page");
				$fields[] = array("type"=>"hidden", "value"=>$lang_code, "name"=>"code");
				$fields[] = array("type"=>"text", "code"=>"style=\"width: 100%\"", "name"=>"original", "label"=>t("Original text:"), "id"=>"original", "required"=>true, "description"=>t("Original string to translate."));
				$fields[] = array("type"=>"text", "code"=>"style=\"width: 100%\"", "name"=>"translation", "label"=>t("Translation:"), "id"=>"translation", "required"=>true, "description"=>t("Meaning of the original text in this language."));

				$fields[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
				$fields[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

				$fieldset[] = array("fields"=>$fields);

				print JarisCMS\Form\Generate($parameters, $fieldset);
			}

			//Edit exisiting string form
			else if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "edit")
			{
				//Get and sort strings
				$strings_sorted = JarisCMS\Language\GetStrings($lang_code);
				$strings_sorted = JarisCMS\PHPDB\Sort($strings_sorted, "original");
				$strings = array();
				$string_index = 0;
				foreach($strings_sorted as $string_fields)
				{
					$strings[$string_index] = $string_fields;
					$string_index++;
				}
				
				$position = $_REQUEST["position"];

				$parameters["name"] = "edit-language-string";
				$parameters["class"] = "edit-language-string";
				$parameters["action"] = JarisCMS\URI\PrintURL("admin/languages/edit#$position");
				$parameters["method"] = "get";

				$fields[] = array("type"=>"hidden", "value"=>"save_changes", "name"=>"action");
				$fields[] = array("type"=>"hidden", "value"=>$current_page, "name"=>"current_page");
				$fields[] = array("type"=>"hidden", "value"=>$lang_code, "name"=>"code");
				$fields[] = array("type"=>"hidden", "value"=>$position, "name"=>"position");
				$fields[] = array("type"=>"text", "code"=>"style=\"width: 100%\"", "value"=>$strings[$position]["original"], "name"=>"original", "label"=>t("Original text:"), "id"=>"original", "readonly"=>true, "description"=>t("Original string to translate."));
				$fields[] = array("type"=>"text", "code"=>"style=\"width: 100%\"", "value"=>$strings[$position]["translation"], "name"=>"translation", "label"=>t("Translation:"), "id"=>"translation", "description"=>t("Meaning of the original text in this language."));

				$fields[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
				$fields[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

				$fieldset[] = array("fields"=>$fields);

				print JarisCMS\Form\Generate($parameters, $fieldset);
			}

			//Save new string function
			else if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "save_new" && !JarisCMS\Form\CheckFields("add-language-string"))
			{
				$original = $_REQUEST["original"];
				$translation = $_REQUEST["translation"];

				if(isset($_REQUEST["btnSave"]))
				{
					if(JarisCMS\Language\AddString($lang_code, $original, $translation))
					{
						JarisCMS\System\AddMessage("Changes successfully saved.");
					}
					else
					{
						JarisCMS\System\AddMessage("Check your write permissions on the <b>language</b> directory.", "error");
					}
				}

				$_REQUEST["action"] = null;
			}

			//Save modified string function
			else if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "save_changes")
			{
				//Get and sort strings
				$strings_sorted = JarisCMS\Language\GetStrings($lang_code);
				$strings_sorted = JarisCMS\PHPDB\Sort($strings_sorted, "original");
				$strings = array();
				$string_index = 0;
				foreach($strings_sorted as $string_fields)
				{
					$strings[$string_index] = $string_fields;
					$string_index++;
				}
				
				$position = $_REQUEST["position"];
				$translation = $_REQUEST["translation"];

				if(isset($_REQUEST["btnSave"]))
				{
					if(JarisCMS\Language\AddString($lang_code, $strings[$position]["original"], $translation))
					{
						JarisCMS\System\AddMessage("Changes successfully saved.");
					}
					else
					{
						JarisCMS\System\AddMessage("Check your write permissions on the <b>language</b> directory.", "error");
					}
				}

				$_REQUEST["action"] = null;
			}

			//Delete exisiting string form
			else if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "delete")
			{
				//Get and sort strings
				$strings_sorted = JarisCMS\Language\GetStrings($lang_code);
				$strings_sorted = JarisCMS\PHPDB\Sort($strings_sorted, "original");
				$strings = array();
				$string_index = 0;
				foreach($strings_sorted as $string_fields)
				{
					$strings[$string_index] = $string_fields;
					$string_index++;
				}
				
				$position = $_REQUEST["position"];

				print "<form class=\"delete-language-string\" method=\"post\" action=\"" . JarisCMS\URI\PrintURL("admin/languages/edit") . "\">
					<input type=\"hidden\" name=\"action\" value=\"delete_now\" />
					<input type=\"hidden\" name=\"current_page\" value=\"$current_page\" />
					<input type=\"hidden\" name=\"code\" value=\"$lang_code\" />
					<input type=\"hidden\" name=\"position\" value=\"$position\" />
					<br />
					<div>" . t("Are you sure you want to delete the string?") . "
					<div><b>" . t("Original string:") . " " . $strings[$position]["original"] . "</b></div>
					</div>
					<input class=\"form-submit\" type=\"submit\" name=\"btnYes\" value=\"" . t("Yes") . "\" />
					<input class=\"form-submit\" type=\"submit\" name=\"btnNo\" value=\"" . t("No") . "\" />
				</form>";
			}

			//Delete exisiting string function
			else if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "delete_now")
			{
				//Get and sort strings
				$strings_sorted = JarisCMS\Language\GetStrings($lang_code);
				$strings_sorted = JarisCMS\PHPDB\Sort($strings_sorted, "original");
				$strings = array();
				$string_index = 0;
				foreach($strings_sorted as $string_fields)
				{
					$strings[$string_index] = $string_fields;
					$string_index++;
				}
				
				$position = $_REQUEST["position"];

				if(isset($_REQUEST["btnYes"]))
				{
					if(JarisCMS\Language\DeleteString($lang_code, $strings[$position]["original"]))
					{
						JarisCMS\System\AddMessage("String successfully removed.");
					}
					else
					{
						JarisCMS\System\AddMessage("Check your write permissions on the <b>language</b> directory.", "error");
					}
				}

				$_REQUEST["action"] = null;
			}


			//Print list of strings
			if(!isset($_REQUEST["action"]))
			{
				JarisCMS\System\AddTab(t("Add string"), "admin/languages/edit", array("action"=>"add", "code"=>$lang_code));
                JarisCMS\System\AddTab(t("Import strings"), "admin/languages/import", array("code"=>$lang_code));

				$amount_translated = JarisCMS\Language\GetTranslatedStats($lang_code);

				//Display amount of strings translated.
				print "<div class=\"total-translated\">\n";
				print "<span>" . t("Translated:") . "</span>" . " " . $amount_translated["translated_strings"] . "<br />\n";
				print "<span>" . t("Total system strings:") . "</span>" . " " . $amount_translated["total_strings"] . "<br />\n";
				print "<span>" . t("Percent translated:") . "</span>" . " " . $amount_translated["percent"] . "%\n";
				print "</div>";

				//Get and sort strings
				$strings_sorted = JarisCMS\Language\GetStrings($lang_code);
				$strings_sorted = JarisCMS\PHPDB\Sort($strings_sorted, "original");
				$strings = array();
				$string_index = 0;
				foreach($strings_sorted as $string_fields)
				{
					$strings[$string_index] = $string_fields;
					$string_index++;
				}								
				$strings_amount = count($strings);
				$strings_per_page = 20;

				$page_count = ceil($strings_amount / $strings_per_page);

				print "<br />\n";

				//Print page navigation
				$previous_page = $current_page>1?"<a href=\"" . JarisCMS\URI\PrintURL("admin/languages/edit", array("code"=>$lang_code, "current_page"=>$current_page-1)) . "\"> << " . t("Previous") . "</a>":"";
				$next_page = $current_page>=1 && $current_page != $page_count?"<a href=\"" . JarisCMS\URI\PrintURL("admin/languages/edit", array("code"=>$lang_code, "current_page"=>$current_page+1)) . "\">" . t("Next") . " >></a>":"";
				print "<div class=\"language-navigation\" />\n";
				print "<div style=\"float: left\">" . $previous_page . "</div>\n";
				print "<div style=\"float: right\">" . $next_page . "</div>\n";
				print "</div>\n";

				print "<br />\n";

				//Print available strings
				print "<table class=\"languages-list\">\n";

				print "<thead><tr>\n";

				print "<td>" . t("Original") . "</td>\n";
				print "<td>" . t("Translation") . "</td>\n";
				print "<td>" . t("Operation") . "</td>\n";

				print  "</tr></thead>\n";

				if($current_page > 1)
				{
					for($i=($current_page-1)*$strings_per_page; $i<($current_page-1)*$strings_per_page+20 && $i < $strings_amount; $i++)
					{

						print "<tr>\n";

						print "<td><a name=\"$i\">" . $strings[$i]["original"] . "</a></td>\n";
						print "<td>" . $strings[$i]["translation"] . "</td>\n";

						$edit_url = JarisCMS\URI\PrintURL("admin/languages/edit",array("code"=>$lang_code, "action"=>"edit", "position"=>$i, "current_page"=>$current_page));
						$edit_text = t("Edit");

						$delete_url = JarisCMS\URI\PrintURL("admin/languages/edit",array("code"=>$lang_code, "action"=>"delete", "position"=>$i, "current_page"=>$current_page));
						$delete_text = t("Delete");

						print "<td>
								<a href=\"$edit_url\">$edit_text</a>&nbsp;
								<a href=\"$delete_url\">$delete_text</a>
							   </td>\n";

						print "</tr>\n";
					}
				}
				else
				{
					for($i=0; $i<$strings_per_page; $i++)
					{
						print "<tr>\n";

						print "<td><a name=\"$i\">" . $strings[$i]["original"] . "</a></td>\n";
						print "<td>" . $strings[$i]["translation"] . "</td>\n";

						$edit_url = JarisCMS\URI\PrintURL("admin/languages/edit",array("code"=>$lang_code, "action"=>"edit", "position"=>$i, "current_page"=>$current_page));
						$edit_text = t("Edit");

						$delete_url = JarisCMS\URI\PrintURL("admin/languages/edit",array("code"=>$lang_code, "action"=>"delete", "position"=>$i, "current_page"=>$current_page));
						$delete_text = t("Delete");

						print "<td>
								<a href=\"$edit_url\">$edit_text</a>&nbsp;
								<a href=\"$delete_url\">$delete_text</a>
							   </td>\n";

						print "</tr>\n";
					}
				}

				print "</table>\n";

				print "<br />\n";
				
				print "<center>
				<form action=\"" . JarisCMS\URI\PrintURL("admin/languages/edit") . "\" method=\"get\">
				<input type=\"hidden\" name=\"code\" value=\"$lang_code\" />
				" . t("Goto Page:") . " <select name=\"current_page\">
				";
				
				for($i=1; $i<=$page_count; $i++)
				{
					$selected = "";
					if($current_page == $i)
					{
						$selected = "selected";
					}
					
					print "<option $selected value=\"$i\">$i</option>";
				}
				
				print "</select>
				<input type=\"submit\" value=\"" . t("Go") . "\" />
				</form>
				</center>";

				//Print page navigation
				$previous_page = $current_page>1?"<a href=\"" . JarisCMS\URI\PrintURL("admin/languages/edit", array("code"=>$lang_code, "current_page"=>$current_page-1)) . "\"> << " . t("Previous") . "</a>":"";
				$next_page = $current_page>=1 && $current_page != $page_count?"<a href=\"" . JarisCMS\URI\PrintURL("admin/languages/edit", array("code"=>$lang_code, "current_page"=>$current_page+1)) . "\">" . t("Next") . " >></a>":"";
				print "<div class=\"language-navigation\" />\n";
				print "<div style=\"float: left\">" . $previous_page . "</div>\n";
				print "<div style=\"float: right\">" . $next_page . "</div>\n";
				print "</div>\n";

			}
		?>
	field;
	
	field: is_system
		1
	field;
row;
