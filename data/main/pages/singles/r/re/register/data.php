<?php
/**
 *Copyright 2008, Jefferson Gonzï¿½lez (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the create account page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Create My Account") ?>
	field;

	field: content
		<?php
            
            if(!JarisCMS\Setting\Get("new_registrations", "main") && !JarisCMS\Security\IsAdminLogged())
            {
                JarisCMS\System\AddMessage(t("Registrations are disabled, sorry for any inconvinience."), "error");
                JarisCMS\System\GoToPage("");
            }
            
            //Store return url
            if(isset($_REQUEST["return"]))
            {
                $_SESSION["return_url"] = $_REQUEST["return"];
            }
            
            if(JarisCMS\Security\IsUserLogged())
            {
                JarisCMS\System\GoToPage("admin/user");
            }
            
            $valid_email = true;
            if(isset($_REQUEST["email"]))
            {
                $valid_email = JarisCMS\Form\CheckEmail($_REQUEST["email"]);
                
                if(!$valid_email)
                {
                    JarisCMS\System\AddMessage(t("The email you entered is not a valid one."), "error");
                }
                else 
                {
                	//Check that the email is not in use by other account 
                	$db_users = JarisCMS\SQLite\Open("users");
                	$select = "select email from users where email='" . trim($_REQUEST["email"]) . "'";
                	$result = JarisCMS\SQLite\Query($select, $db_users);
                	
                	if($data = JarisCMS\SQLite\FetchArray($result))
                	{
                		$valid_email = false;
                		JarisCMS\System\AddMessage(t("The email you entered already has a registered account associated to it."), "error");
                	}
                	
                	JarisCMS\SQLite\Close($db_users);
                }
            }
            
            $valid_username = true;
            if(isset($_REQUEST["username"]))
            {
                $valid_username = JarisCMS\Form\CheckUserName($_REQUEST["username"]);
                
                if(!$valid_username)
                {
                    JarisCMS\System\AddMessage(t("The username you provided has invalid characters."), "error");
                }
            }
            
            if($valid_username && isset($_REQUEST["username"]))
            {
                if(strlen($_REQUEST["username"]) < 3)
                {
                    JarisCMS\System\AddMessage(t("The username should be at least 3 characters long."), "error");
                    $valid_username = false;
                }
                else if(strlen($_REQUEST["username"]) > 60)
                {
                    JarisCMS\System\AddMessage(t("The username exceeds from 60 characters."), "error");
                    $valid_username = false;
                }
            }
            
            $agree_terms = true;
            if(JarisCMS\Setting\Get("registration_terms", "main") && isset($_REQUEST["accept_terms_conditions"]))
			{
                $agree_terms = $_REQUEST["accept_terms_conditions"];
                
                if(!$agree_terms)
                {
                    JarisCMS\System\AddMessage(t("You must agree to the terms and conditions of this website in order to register."), "error");
                }
            }

			if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("register-user") && $valid_email && $valid_username && $agree_terms)
			{
				$fields["name"] = substr(JarisCMS\Search\StripHTMLTags($_REQUEST["full_name"]), 0, 65);
				$fields["group"] = "regular";
				$fields["register_date"] = time();
                $fields["ip_address"] = $_SERVER["REMOTE_ADDR"];
                $fields["gender"] = $_REQUEST["gender"];
                $fields["birth_date"] = mktime(0, 0, 0, $_REQUEST["month"], $_REQUEST["day"], $_REQUEST["year"]);
				
				if(JarisCMS\Setting\Get("registration_needs_approval", "main"))
				{
					$fields["status"] = "0";
				}
				else
				{
					$fields["status"] = "1";
				}

				$error = false;

				if($_REQUEST["password"] != "" && $_REQUEST["password"] == $_REQUEST["verify_password"])
				{
					$fields["password"] = $_REQUEST["password"];
				}
				elseif($_REQUEST["password"] == "" || $_REQUEST["password"] != $_REQUEST["verify_password"])
				{
					JarisCMS\System\AddMessage(t("The Password and Verify password doesn't match."), "error");
					$error = true;
				}
                
                if($_REQUEST["email"] == $_REQUEST["verify_email"])
				{
					$fields["email"] = trim($_REQUEST["email"]);
				}
				else
				{
					JarisCMS\System\AddMessage(t("The e-mail and verify e-mail doesn't match."), "error");
					$error = true;
				}

				if(!$error)
				{
					$message = "";

					if(JarisCMS\Setting\Get("user_picture", "main"))
					{
						$message = JarisCMS\User\Add($_REQUEST["username"], $fields["group"], $fields, $_FILES["picture"]);
					}
					else
					{
						$message = JarisCMS\User\Add($_REQUEST["username"], $fields["group"], $fields);
					}

					if($message == "true")
					{
						if(JarisCMS\Setting\Get("registration_needs_approval", "main"))
						{
							JarisCMS\System\AddMessage(t("Your registration is awaiting for approval. If the registration is approved you will receive an email notification."));
							
							JarisCMS\Security\NotifyAdminsForRegApproval($_REQUEST["username"]);
						}
						else
						{
							JarisCMS\System\AddMessage(t("Your account has been successfully created. Enter your details to login."));
						}

						JarisCMS\System\GoToPage("admin/user");
					}
					else
					{
						JarisCMS\System\AddMessage($message, "error");
					}
				}
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("");
			}
            
            unset($fields);

			$parameters["name"] = "register-user";
			$parameters["class"] = "register-user";
			$parameters["action"] = JarisCMS\URI\PrintURL("register");
			$parameters["method"] = "post";
			$parameters["enctype"] = "multipart/form-data";

			$fields[] = array("type"=>"text", "limit"=>65, "value"=>$_REQUEST["full_name"], "name"=>"full_name", "label"=>t("Fullname:"), "id"=>"full_name", "required"=>true, "description"=>t("Your full real name."));
			
            $fields[] = array("type"=>"text", "limit"=>60, "value"=>$_REQUEST["username"], "name"=>"username", "label"=>t("Username:"), "id"=>"name", "required"=>true, "description"=>t("The name that you are going to use to log in, at least 3 characters long. Permitted characters are A to Z, 0 to 9 and underscores."));
			
			$fields[] = array("type"=>"password", "name"=>"password", "label"=>t("Password:"), "id"=>"password", "required"=>true, "description"=>t("The password used to login, should be at least 6 characters long."));
			$fields[] = array("type"=>"password", "name"=>"verify_password", "label"=>t("Verify password:"), "id"=>"verify_password", "required"=>true, "description"=>t("Re-enter the password to verify it."));
			
			$fields[] = array("type"=>"text", "value"=>$_REQUEST["email"], "name"=>"email", "label"=>t("E-mail:"), "id"=>"email", "required"=>true, "description"=>t("The email used in case you forgot your password."));
            
            $fields[] = array("type"=>"text", "name"=>"verify_email", "label"=>t("Verify the e-mail:"), "id"=>"verify_email", "required"=>true, "description"=>t("Re-enter the e-mail to verify is correct."));
            
            $fieldset[] = array("fields"=>$fields);
            
            //Gender Fields
            $gender[t("Male")] = "m";
            $gender[t("Female")] = "f";
            
            $gender_fields[] = array("type"=>"radio", "name"=>"gender", "id"=>"gender", "value"=>$gender, "checked"=>$_REQUEST["gender"], "required"=>true);

			$fieldset[] = array("name"=>t("Gender"), "fields"=>$gender_fields);
            
            //Birthdate fields
            $birth_date_fields[] = array("type"=>"select", "name"=>"day", "label"=>t("Day:"), "id"=>"day", "required"=>true, "value"=>JarisCMS\System\GetDatesArray(), "selected"=>$_REQUEST["day"], "required"=>true);
            $birth_date_fields[] = array("type"=>"select", "name"=>"month", "label"=>t("Month:"), "id"=>"month", "required"=>true, "value"=>JarisCMS\System\GetMonthsArray(), "selected"=>$_REQUEST["month"], "required"=>true);
            $birth_date_fields[] = array("type"=>"select", "name"=>"year", "label"=>t("Year:"), "id"=>"year", "required"=>true, "value"=>JarisCMS\System\GetYearsArray(), "selected"=>$_REQUEST["year"], "required"=>true);
            
            $fieldset[] = array("name"=>t("Birth date"), "fields"=>$birth_date_fields);

            //If user pictures are activated.
			if(JarisCMS\Setting\Get("user_picture", "main"))
			{
                $size = null;
				if(!($size = JarisCMS\Setting\Get("user_picture_size", "main")))
				{
					$size = "150x150";
				}
                
				$fields_picture[] = array("id"=>"picture", "type"=>"file", "name"=>"picture", "description"=>t("A logo or picture of your self.") . "&nbsp;" . $size);
				$fieldset[] = array("name"=>t("Picture"), "fields"=>$fields_picture);
			}
            
            if(JarisCMS\Setting\Get("registration_terms", "main"))
			{
                $terms[t("I do not agree")] = false;
                $terms[t("I agree")] = true;
            
                $fields_submit[] = array("type"=>"textarea", "name"=>"terms_conditions", "label"=>t("Terms and Conditions:"), "id"=>"terms_conditions", "value"=>JarisCMS\Setting\Get("registration_terms", "main"), "readonly"=>true, "description"=>t("The terms and conditions that you have to accept in order to register."));
                $fields_submit[] = array("type"=>"radio", "name"=>"accept_terms_conditions", "id"=>"accept_terms_conditions", "value"=>$terms, "checked"=>false);
			}

			$fields_submit[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Register"));
			$fields_submit[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

			$fieldset[] = array("fields"=>$fields_submit);

			print JarisCMS\Form\Generate($parameters, $fieldset);
		?>
	field;
	
	field: is_system
		1
	field;
row;
