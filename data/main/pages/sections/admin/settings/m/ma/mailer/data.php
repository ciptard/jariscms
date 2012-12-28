<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the site settings management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
	field: title
		<?php print t("Mailer Settings") ?>
	field;

	field: content
		<?php

			JarisCMS\Security\ProtectPage(array("edit_settings"));
			
			$site_settings = JarisCMS\Setting\GetAll("main");

			if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("mailer-settings"))
			{
				//Check if write is possible and continue to write settings
				if(JarisCMS\Setting\Save("mailer", $_REQUEST["mailer"], "main"))
				{
					JarisCMS\Setting\Save("mailer_from_name", $_REQUEST["mailer_from_name"], "main");
					JarisCMS\Setting\Save("mailer_from_email", $_REQUEST["mailer_from_email"], "main");
					
					JarisCMS\Setting\Save("smtp_auth", $_REQUEST["smtp_auth"], "main");
					JarisCMS\Setting\Save("smtp_ssl", $_REQUEST["smtp_ssl"], "main");
					JarisCMS\Setting\Save("smtp_host", $_REQUEST["smtp_host"], "main");
					JarisCMS\Setting\Save("smtp_port", $_REQUEST["smtp_port"], "main");
					JarisCMS\Setting\Save("smtp_user", $_REQUEST["smtp_user"], "main");
					JarisCMS\Setting\Save("smtp_pass", $_REQUEST["smtp_pass"], "main");

					JarisCMS\System\AddMessage(t("Your settings have been successfully saved."));

					global $clean_urls;

					$clean_urls = $_REQUEST["clean_urls"];
				}
				else
				{
					JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
					JarisCMS\System\GoToPage("admin/settings/mailer");
				}

				JarisCMS\System\GoToPage("admin/settings");
			}
			elseif(isset($_REQUEST["btnCancel"]))
			{
				JarisCMS\System\GoToPage("admin/settings");
			}
			
			$parameters["name"] = "mailer-settings";
			$parameters["class"] = "mailer-settings";
			$parameters["action"] = JarisCMS\URI\PrintURL("admin/settings/mailer");
			$parameters["method"] = "post";
			
			$mailer[t("Mail (default)")] = "mail";
			$mailer[t("Sendmail")] = "sendmail";
			$mailer[t("SMTP")] = "smtp";

			$fields_main[] = array("type"=>"select", "label"=>t("Mailing system:"), "name"=>"mailer", "id"=>"mailer", "value"=>$mailer, "selected"=>$site_settings["mailer"]);
			$fields_main[] = array("type"=>"text", "label"=>t("From name:"), "name"=>"mailer_from_name", "id"=>"mailer_from_name", "value"=>$site_settings["mailer_from_name"], "required"=>true, "description"=>t("The name used on the from email."));
			$fields_main[] = array("type"=>"text", "label"=>t("From e-mail:"), "name"=>"mailer_from_email", "id"=>"mailer_from_email", "value"=>$site_settings["mailer_from_email"], "required"=>true, "description"=>t("The email used on the from email."));
			
			$fieldset[] = array("fields"=>$fields_main);
			
			$stmp_options[t("Enable")] = true;
			$stmp_options[t("Disable")] = false;
			
			$fields_smtp[] = array("type"=>"select", "label"=>t("Authentication:"), "name"=>"smtp_auth", "id"=>"smtp_auth", "value"=>$stmp_options, "selected"=>$site_settings["smtp_auth"]);			
			$fields_smtp[] = array("type"=>"select", "label"=>t("SSL:"), "name"=>"smtp_ssl", "id"=>"smtp_ssl", "value"=>$stmp_options, "selected"=>$site_settings["smtp_ssl"]);
			$fields_smtp[] = array("type"=>"text", "label"=>t("Host:"), "name"=>"smtp_host", "id"=>"smtp_host", "value"=>$site_settings["smtp_host"]);
			$fields_smtp[] = array("type"=>"text", "label"=>t("Port:"), "name"=>"smtp_port", "id"=>"smtp_port", "value"=>$site_settings["smtp_port"]);
			$fields_smtp[] = array("type"=>"text", "label"=>t("Username:"), "name"=>"smtp_user", "id"=>"smtp_user", "value"=>$site_settings["smtp_user"]);
			$fields_smtp[] = array("type"=>"password", "label"=>t("Password:"), "name"=>"smtp_pass", "id"=>"smtp_pass", "value"=>$site_settings["smtp_pass"]);
			
			$fieldset[] = array("name"=>t("SMTP Configuration"), "fields"=>$fields_smtp, "collapsible"=>true, "collapsed"=>false);

			$fields[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
			$fields[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

			$fieldset[] = array("fields"=>$fields);

			print JarisCMS\Form\Generate($parameters, $fieldset);

		?>
	field;

	field: is_system
		1
	field;
row;
