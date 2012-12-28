<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the user edit page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php 
			if(!isset($_REQUEST["username"]))
            {
                $_REQUEST["username"] == JarisCMS\Security\GetCurrentUser();
            }
            
			if(JarisCMS\Security\GetCurrentUser() != $_REQUEST["username"])
			{
				print t("Edit User");
			}
			else
			{
				print t("My Account Details");
			}
			
		?>
	field;

	field: content
		<?php
            if(!JarisCMS\Security\IsUserLogged())
            {
                JarisCMS\Security\ProtectPage();
            }
            
            if(!isset($_REQUEST["username"]) || trim($_REQUEST["username"]) == "")
            {
                $_REQUEST["username"] = JarisCMS\Security\GetCurrentUser();
            }
			elseif(JarisCMS\Security\GetCurrentUser() != $_REQUEST["username"])
			{
				JarisCMS\Security\ProtectPage(array("edit_users"));
			}

			if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("edit-user"))
			{
				$fields = JarisCMS\User\GetData($_REQUEST["username"]);

				$fields["name"] = substr(JarisCMS\Search\StripHTMLTags($_REQUEST["name"]), 0, 65);
				$fields["email"] = JarisCMS\Search\StripHTMLTags($_REQUEST["email"]);
                $fields["gender"] = JarisCMS\Search\StripHTMLTags($_REQUEST["gender"]);
				$fields["personal_text"] = substr(JarisCMS\Search\StripHTMLTags($_REQUEST["personal_text"]), 0, 300);
                $fields["birth_date"] = mktime(0, 0, 0, $_REQUEST["month"], $_REQUEST["day"], $_REQUEST["year"]);
                
				$previous_user_status = $fields["status"];
				
                if(JarisCMS\Group\GetPermission("edit_users", JarisCMS\Security\GetCurrentUserGroup()))
                {
				    $fields["group"] = $_REQUEST["group"]?$_REQUEST["group"]:$fields["group"];
					$fields["status"] = $_REQUEST["status"]?$_REQUEST["status"]:$fields["status"];
                }

				$error = false;

				if($_REQUEST["password"] != "" && $_REQUEST["password"] == $_REQUEST["verify_password"])
				{
					$fields["password"] = crypt($_REQUEST["password"]);
				}
				elseif($_REQUEST["password"] != "" && $_REQUEST["password"] != $_REQUEST["verify_password"])
				{
					JarisCMS\System\AddMessage(t("The New password and Verify password doesn't match."), "error");
					$error = true;
				}

				if(!$error)
				{
					$message = "";

					if(JarisCMS\Setting\Get("user_picture", "main"))
					{
						$message = JarisCMS\User\Edit($_REQUEST["username"], $fields["group"], $fields, $_FILES["picture"]);
					}
					else
					{
						$message = JarisCMS\User\Edit($_REQUEST["username"], $fields["group"], $fields);
					}

					if($message == "true")
					{
						JarisCMS\System\AddMessage(t("Your changes have been successfully saved."));
						
						if(JarisCMS\Group\GetPermission("edit_users", JarisCMS\Security\GetCurrentUserGroup()))
						{
							//Send notification email to user if account was activated
							if($previous_user_status == "0" && $_REQUEST["status"] == "1")
							{
								$to = array();
								$to[$fields["name"]] = $fields["email"];

								$html_message = t("Your account has been activated.") . "<br /><br />";
								$html_message .= t("Username:") . " " . $_REQUEST["username"] . "<br /><br />";
								$html_message .= t("Login by visiting:") . " <a target=\"_blank\" href=\"".JarisCMS\URI\PrintURL("admin/user")."\">" . JarisCMS\URI\PrintURL("admin/user") . "</a>";

								JarisCMS\Email\Send($to, t("Account Activated"), $html_message);
							}
						}
					}
					else
					{
						JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
					}
				}
                
               	if($_REQUEST["password"] != "" && $_REQUEST["password"] == $_REQUEST["verify_password"])
				{
					JarisCMS\Security\LogoutUser();
                    JarisCMS\Security\LoginUser();
				}

				JarisCMS\System\GoToPage("admin/users/edit", array("username"=>$_REQUEST["username"]));
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
                if(JarisCMS\Security\IsAdminLogged())
                {
				    JarisCMS\System\GoToPage("admin/users/list");
                }
                else
                {
                    JarisCMS\System\GoToPage("admin/user");
                }
			}

			$arguments["username"] = $_REQUEST["username"];

            if(JarisCMS\Group\GetPermission("delete_users", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Delete"), "admin/users/delete", $arguments);
            }
            
            unset($fields);

			$user_data = JarisCMS\User\GetData($_REQUEST["username"]);

			$parameters["name"] = "edit-user";
			$parameters["class"] = "edit-user";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/users/edit");
			$parameters["method"] = "post";
			$parameters["enctype"] = "multipart/form-data";

			$fields[] = array("type"=>"hidden", "name"=>"username", "value"=>$_REQUEST["username"]);
			$fields[] = array("type"=>"text", "limit"=>65, "value"=>$user_data["name"], "name"=>"name", "label"=>t("Name:"), "id"=>"name", "required"=>true, "description"=>t("The name that others can see."));
			$fields[] = array("type"=>"textarea", "limit"=>300, "value"=>$user_data["personal_text"], "name"=>"personal_text", "label"=>t("Personal text:"), "id"=>"personal_text", "description"=>t("Writing displayed on your profile page."));
			$fields[] = array("type"=>"text", "value"=>$user_data["email"], "name"=>"email", "label"=>t("Email:"), "id"=>"email", "required"=>true, "description"=>t("The email used in case you forgot your password or to contact you."));
			$fields[] = array("type"=>"password", "name"=>"password", "label"=>t("New password:"), "id"=>"password", "description"=>t("You can enter a new password to change actual one."));
			$fields[] = array("type"=>"password", "name"=>"verify_password", "label"=>t("Verify password:"), "id"=>"verify_password", "description"=>t("Re-enter the new password to verify it."));

			$fieldset[] = array("fields"=>$fields);
            
            //Gender Fields
            $gender[t("Male")] = "m";
            $gender[t("Female")] = "f";
            
            $gender_fields[] = array("type"=>"radio", "name"=>"gender", "id"=>"gender", "value"=>$gender, "checked"=>$user_data["gender"], "required"=>true);

			$fieldset[] = array("name"=>t("Gender"), "fields"=>$gender_fields);
            
            $day = date("j", $user_data["birth_date"]);
            $month = date("n", $user_data["birth_date"]);
            $year = date("Y", $user_data["birth_date"]);
            
            //Birthdate fields
            $birth_date_fields[] = array("type"=>"select", "name"=>"day", "label"=>t("Day:"), "id"=>"day", "required"=>true, "value"=>JarisCMS\System\GetDatesArray(), "selected"=>$day, "required"=>true);
            $birth_date_fields[] = array("type"=>"select", "name"=>"month", "label"=>t("Month:"), "id"=>"month", "required"=>true, "value"=>JarisCMS\System\GetMonthsArray(), "selected"=>$month, "required"=>true);
            $birth_date_fields[] = array("type"=>"select", "name"=>"year", "label"=>t("Year:"), "id"=>"year", "required"=>true, "value"=>JarisCMS\System\GetYearsArray(), "selected"=>$year, "required"=>true);
            
            $fieldset[] = array("name"=>t("Birth date"), "fields"=>$birth_date_fields);

			//If user pictures are activated enable user to change or choose a pic.
			if(JarisCMS\Setting\Get("user_picture", "main"))
			{
				if($picture = JarisCMS\User\GetAvatarPath($_REQUEST["username"]))
				{
					$image_src = JarisCMS\URI\PrintURL("image/user/{$_REQUEST['username']}");
					$code = "<div class=\"edit-user-picture\">\n";
					$code .= "<img src=\"$image_src\" />\n";
					$code .= "</div>\n";

					$fields_picture[] = array("type"=>"other", "html_code"=>$code);
				}

				$size = null;
				if(!($size = JarisCMS\Setting\Get("user_picture_size", "main")))
				{
					$size = "150x150";
				}

				$fields_picture[] = array("id"=>"picture", "type"=>"file", "name"=>"picture", "description"=>t("A picture displayed in user post, comments, etc. Maximun size of:") . "&nbsp;" . $size);
				$fieldset[] = array("name"=>t("Picture"), "fields"=>$fields_picture);
			}

			//Display user group and status selector if user has permissions
			if(JarisCMS\Group\GetPermission("edit_users", JarisCMS\Security\GetCurrentUserGroup()))
			{
				$fields_extra[] = array("type"=>"select", "name"=>"group", "label"=>t("Group:"), "id"=>"group", "value"=>JarisCMS\Group\GetList(), "selected"=>$user_data["group"], "description"=>t("The group where the user belongs."));
				$fields_extra[] = array("type"=>"select", "name"=>"status", "label"=>t("Status:"), "id"=>"status", "value"=>JarisCMS\User\GetListStatus(), "selected"=>$user_data["status"], "description"=>t("The account status of this user."));
				
				$fieldset[] = array("fields"=>$fields_extra);
			}

			$fields_submit[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
			$fields_submit[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

			$fieldset[] = array("fields"=>$fields_submit);
            
            if($user_data["ip_address"])
            {
                print "<p>" . t("Last login from ip:") . " " . $user_data["ip_address"] . "</p>";
            }

			print JarisCMS\Form\Generate($parameters, $fieldset);
		?>
	field;


	field: is_system
		1
	field;
row;
