<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the administration login page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("My Account") ?>
	field;

	field: content
		<?php
            
            //Store return url
            if(isset($_REQUEST["return"]))
            {
                $_SESSION["return_url"] = $_REQUEST["return"];
            }
            
            if(JarisCMS\Security\LoginUser() || JarisCMS\Security\IsUserLogged())
            {
                //Goto return url if it is set
                if($_SESSION["return_url"])
                {
                    $return = $_SESSION["return_url"];
                    unset($_SESSION["return_url"]);
                    
                    JarisCMS\System\GoToPage($return);                    
                }
                
                //Display user page
            	JarisCMS\User\PrintPage();
            }
            else
            {
                //To remove any login session data
                JarisCMS\Security\LogoutUser();
                
            	$parameters["action"] = JarisCMS\URI\PrintURL("admin/user");
            	$parameters["method"] = "post";
            
            	$fields[] = array("type"=>"text", "name"=>"username", "label"=>t("Username or E-mail:"), "id"=>"page-username");
            	$fields[] = array("type"=>"password", "name"=>"password", "label"=>t("Password:"), "id"=>"page-password", "description"=>t("the password is case sensitive"));
            	$fields[] = array("type"=>"submit", "name"=>"login", "value"=>t("Login"));
            	$fields[] = array("type"=>"reset", "name"=>"reset", "value"=>t("Reset"));
            
            	$fieldset[] = array("fields"=>$fields);
            	
            	print "<table id=\"my-account\">";
				print "<tbody>";
				print "<tr>";
				
				print "<td class=\"login\">";
				if(JarisCMS\Setting\Get("new_registrations", "main"))
                {
					print "<h2>" . t("Existing User") . "</h2>";
                }
				print JarisCMS\Form\Generate($parameters, $fieldset);
				print "<div style=\"margin-top: 15px\">";
				print "<a href=\"" . JarisCMS\URI\PrintURL("forgot-password") . "\">" . t("Forgot Password?") . "</a>";
				print "</div>";
				print "</td>";
				
				if(JarisCMS\Setting\Get("new_registrations", "main"))
                {
					print "<td class=\"register\">";
					print "<h2>" . t("Create Account") . "</h2>";
					print "<a class=\"register-link\" href=\"" . JarisCMS\URI\PrintURL("register", array("return"=>$_REQUEST["return"])) . "\">" . t("Register") . "</a>";
					print JarisCMS\System\PHPEval(JarisCMS\Setting\Get("registration_benefits", "main"));
					print "</td>";
                }
                
				print "</tr>";
				print "</tbody>";
				print "</table>";
                
                
            }
            
		?>
	field;

	field: is_system
		1
	field;
row;
