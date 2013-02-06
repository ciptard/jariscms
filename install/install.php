<?php

/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file JarisCMS Installation script.
*/

chdir("../");

//Starts the main session for the user
session_start();

//Include jaris cms functions
include("include/all_includes.php");

//Override settings.php base_url with an url detection.
$base_url = "http://" . $_SERVER["SERVER_NAME"] . str_replace("/install.php", "", $_SERVER["PHP_SELF"]);

//Use new settings if available.
JarisCMS\Setting\Override();

//Sets the language based on user selection or system default
$language = JarisCMS\Language\GetCurrent();

//For security skip page and go to index if already installed.
if(file_exists(JarisCMS\Setting\GetDataDirectory() . "settings/main.php") && JarisCMS\Setting\Get("mailer_from_name", "main") && $_REQUEST["action"] != "finalize_installation" && $_REQUEST["action"] != "cleanurl_check")
{
    JarisCMS\System\GoToPage("");
}

//Variables used to store what is showed on installation.
$step_title = "";
$content = "";
$error_message = "";

//Create data/default if not exists
if(!file_exists("data/default"))
{
    //Disable max execution time in case of slow copy
    ini_set('max_execution_time', '0');

    //This could be slow and needs testing we should also do directory writable check
    JarisCMS\FileSystem\CopyDirRecursively("data/main", "data/default");
}

//Welcome page for installation
if(!isset($_REQUEST["action"]) || $_REQUEST["action"] == "")
{
    $step_title = t("Select a language", "install.po");

    $languages_temp = JarisCMS\Language\GetAll();
    $languages = array();
    foreach($languages_temp as $value=>$label)
    {
        $languages[$label] = $value;
    }

    $parameters["action"] = $_SERVER["PHP_SELF"] . "?action=begin";
    $parameters["method"] = "post";

    $fields[] = array("type"=>"select", "name"=>"language", "id"=>"language", "label"=>t("Language:","install.po"), "value"=>$languages, "selected"=>"en");

    $fields[] = array("type"=>"other", "html_code"=>"<div style=\"text-align: right; margin-top: 30px;\">");

    $fields[] = array("type"=>"submit", "name"=>"next", "value"=>t("Continue", "install.po"));

    $fields[] = array("type"=>"other", "html_code"=>"</div>");

    $fieldset[] = array("fields"=>$fields);

    $content .= JarisCMS\Form\Generate($parameters, $fieldset);
}

else if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "begin")
{    
    $step_title = t("Jaris CMS Installation Wizard", "install.po");

    $content = t("<b>Welcome</b> to Jaris CMS installation wizard! To install it you just need some minimun system requirements to have a working enviroment. Before we continue you should know that <b>Jaris CMS</b> does not requires any relational database like mysql since it has its own engine written on php to store data. So in order to make it work you need to set write permissions to the user account that is running the php parser.<br />", "install.po") .

    "<h3>" . t("Requirements:", "install.po") . "</h3>

    <ul>
        <li>" . t("PHP 5.3 or greater", "install.po") . "</li>
        <li>" . t("PHP GD library for graphics processing", "install.po") . "</li>
        <li>" . t("Write permissions on <b>data</b> directory", "install.po") . "</li>
        <li>" . t("Apache with mod rewrite for clean url system", "install.po") . "</li>
        <li>" . t("Sqlite for search engine, users listing and third party modules.", "install.po") . "</li>
    </ul>
    ";

    $parameters["action"] = $_SERVER["PHP_SELF"] . "?action=check_requirements";
    $parameters["method"] = "post";

    $fields[] = array("type"=>"other", "html_code"=>"<div style=\"text-align: right; margin-top: 30px;\">");

    $fields[] = array("type"=>"submit", "name"=>"next", "value"=>t("Check Requirements", "install.po"));

    $fields[] = array("type"=>"other", "html_code"=>"</div>");

    $fieldset[] = array("fields"=>$fields);

    $content .= JarisCMS\Form\Generate($parameters, $fieldset);
}

//Requirements check
else if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "check_requirements")
{
    $php_version_fine = false;
    $php_gd_fine = false;
    $data_writable = false;
    $php_sqlite_fine = false;

    $step_title = t("Step One - Checking Requirements", "install.po");

    $content = "<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n";

    //Check php version.
    $content .= "<tr>";
    if(substr(PHP_VERSION, 0, 1) > 4 && substr(PHP_VERSION, 2, 1) >= 3)
    {
        $content .= "<td style=\"width: 100%; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<b>" . t("PHP version installed:", "install.po") . "</b> " . substr(PHP_VERSION, 0, 5);
        $content .= "</td>";

        $content .= "<td style=\"width: 150px; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<span style=\"color: #3cbe3e; font-weight: bold\">" . t("(OK)", "install.po") ."</span>";
        $content .= "</td>";

        $php_version_fine = true;
    }
    else
    {
        $content .= "<td style=\"width: 100%; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<b>" . t("PHP version installed:", "install.po") . "</b> " . substr(PHP_VERSION, 0, 5) . " " . t("Version 5.3 or greater is needed.", "install.po");
        $content .= "</td>";

        $content .= "<td style=\"width: 150px; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<span style=\"color: red; font-weight: bold\">" . t("(ERROR)", "install.po") ."</span>";
        $content .= "</td>";

        $php_version_fine = false;
    }
    $content .= "</tr>";

    //Check if php gd is installed
    $content .= "<tr>";
    if (extension_loaded('gd') && function_exists('gd_info'))
    {
        $content .= "<td style=\"width: 100%; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<b>" . t("PHP GD library:", "install.po") . "</b> " . t("available", "install.po");
        $content .= "</td>";

        $content .= "<td style=\"width: 150px; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<span style=\"color: #3cbe3e; font-weight: bold\">" . t("(OK)", "install.po") ."</span>";
        $content .= "</td>";

        $php_gd_fine = true;
    }
    else
    {
        $content .= "<td style=\"width: 100%; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<b>" . t("PHP GD library:", "install.po") . "</b> " . t("not available", "install.po");
        $content .= "</td>";

        $content .= "<td style=\"width: 150px; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<span style=\"color: red; font-weight: bold\">" . t("(ERROR)", "install.po") ."</span>";
        $content .= "</td>";

        $php_gd_fine = false;
    }

    //Check if data directory is writable
    $content .= "<tr>";
    if (is_writable('data'))
    {
        $content .= "<td style=\"width: 100%; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<b>" . t("Write permissions on data directory:", "install.po") . "</b> " . t("yes","install.po");
        $content .= "</td>";

        $content .= "<td style=\"width: 150px; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<span style=\"color: #3cbe3e; font-weight: bold\">" . t("(OK)","install.po") . "</span>";
        $content .= "</td>";

        $data_writable = true;
    }
    else
    {
        $content .= "<td style=\"width: 100%; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<b>" . t("Write permissions on data directory:","install.po") . "</b> " . t("no","install.po");
        $content .= "</td>";

        $content .= "<td style=\"width: 150px; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<span style=\"color: red; font-weight: bold\">" . t("(ERROR)","install.po") . "</span>";
        $content .= "</td>";

        $data_writable = false;
    }
    $content .= "</tr>";
    
    //Check if sqlite is available
    $content .= "<tr>";
    if (function_exists('sqlite_open') || class_exists("SQLite3"))
    {
        $content .= "<td style=\"width: 100%; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<b>" . t("PHP SQLite library:","install.po") . "</b> " . t("yes","install.po");
        $content .= "</td>";

        $content .= "<td style=\"width: 150px; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<span style=\"color: #3cbe3e; font-weight: bold\">" . t("(OK)","install.po") . "</span>";
        $content .= "</td>";

        $php_sqlite_fine = true;
    }
    else
    {
        $content .= "<td style=\"width: 100%; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<b>" . t("PHP SQLite library:","install.po") . "</b> " . t("no","install.po");
        $content .= "</td>";

        $content .= "<td style=\"width: 150px; border-bottom: solid #000000 1px; padding-top: 25px; text-align: left;\">";
        $content .= "<span style=\"color: red; font-weight: bold\">" . t("(ERROR)","install.po") . "</span>";
        $content .= "</td>";

        $php_sqlite_fine = false;
    }
    $content .= "</tr>";


    $content .= "</table>";

    if($php_version_fine && $php_gd_fine && $data_writable && $php_sqlite_fine)
    {
        $parameters["action"] = $_SERVER["PHP_SELF"] . "?action=site_details";
        $parameters["method"] = "post";

        $fields[] = array("type"=>"other", "html_code"=>"<div style=\"text-align: right; margin-top: 30px;\">");

        $fields[] = array("type"=>"submit", "name"=>"next", "value"=>t("Continue","install.po"));

        $fields[] = array("type"=>"other", "html_code"=>"</div>");

        $fieldset[] = array("fields"=>$fields);

        $content .= JarisCMS\Form\Generate($parameters, $fieldset);
    }
    else
    {
        $parameters["action"] = $_SERVER["PHP_SELF"] . "?action=check_requirements";
        $parameters["method"] = "post";

        $fields[] = array("type"=>"other", "html_code"=>"<div style=\"text-align: right; margin-top: 30px;\">");

        $fields[] = array("type"=>"submit", "name"=>"next", "value"=>t("Re-check Requirements.","install.po"));

        $fields[] = array("type"=>"other", "html_code"=>"</div>");

        $fieldset[] = array("fields"=>$fields);

        $content .= JarisCMS\Form\Generate($parameters, $fieldset);
    }
}

//Enter site details
else if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "site_details")
{
    if(isset($_REQUEST["finish"]))
    {   
        $username = $_REQUEST["username"];
        
        $fields = JarisCMS\User\GetData($username);
        
        $fields["name"] = $_REQUEST["name"];
        $fields["email"] = $_REQUEST["email"];

        $error = false;

        if(trim($_REQUEST["password"]) == "")
        {
            $error = true;
        }
        else if($_REQUEST["password"] == $_REQUEST["verify_password"])
        {
            $fields["password"] = $_REQUEST["password"];
        }
        elseif($_REQUEST["password"] != $_REQUEST["verify_password"])
        {
            $error = true;
        }

        if(!$error)
        {
            if(JarisCMS\Form\CheckUserName($username))
            {
                //Mark user account as active
                $fields["status"] = 1;

                if(JarisCMS\User\GetData($username) != null)
                {
                    $fields["password"] = crypt($_REQUEST["password"]);
                    JarisCMS\User\Edit($username, "administrator", $fields);
                }
                else
                {
                    $fields["register_date"] = time();
                    JarisCMS\User\Add($username, "administrator", $fields);
                }

                if(trim($_REQUEST["title"]) != "" && trim($_REQUEST["base_url"]) != "")
                {
                    $footer_message = t("Powered by","install.po") . "<img title=\"" . t("File Based CMS","install.po") . "\" src=\"{$_REQUEST['base_url']}/themes/jariscmsv1/images/logo_icon_ie.png\" />";

                    //Check if write is possible and continue to write settings
                    if(JarisCMS\Setting\Save("override", true, "main"))
                    {
                        JarisCMS\Setting\Save("site_status", true, "main");
                        JarisCMS\Setting\Save("title", $_REQUEST["title"], "main");
                        JarisCMS\Setting\Save("slogan", $_REQUEST["slogan"], "main");
                        JarisCMS\Setting\Save("timezone", $_REQUEST["timezone"], "main");
                        JarisCMS\Setting\Save("auto_detect_base_url", $_REQUEST["auto_detect_base_url"], "main");
                        JarisCMS\Setting\Save("base_url", $_REQUEST["base_url"], "main");
                        JarisCMS\Setting\Save("footer_message", $footer_message, "main");
                        JarisCMS\Setting\Save("language", $language, "main");
                        JarisCMS\Setting\Save("clean_urls", false, "main");
                        JarisCMS\Setting\Save("theme", "jariscmsv1", "main");
                        JarisCMS\Setting\Save("themes_enabled", serialize(array("jariscmsv1")), "main");
                        JarisCMS\Setting\Save("primary_menu", "primary", "main");
                        JarisCMS\Setting\Save("secondary_menu", "secondary", "main");
                        JarisCMS\Setting\Save("image_compression_maxwidth", "640", "main");
                        JarisCMS\Setting\Save("image_compression_quality", "100", "main");

                        header("Location: " . $base_url . "/install.php?action=mailing_details");
                        exit;
                    }
                    else
                    {
                        $error_message = t("Configuration could not be save. Check your write permissions on the data directory.","install.po");
                    }
                }
                else
                {
                    $error_message = t("You need to provide all the fields","install.po");
                }
            }
            else
            {
                $error_message = t("The administrator login name is invalid.","install.po");
            }
        }
        else
        {
            $error_message = t("Your password does not match. Try again.","install.po");
        }
    }

    unset($fields);

    $step_title = t("Step Two - Site Details","install.po");

    $parameters["action"] = $_SERVER["PHP_SELF"] . "?action=site_details";
    $parameters["method"] = "post";

    $fields[] = array("type"=>"text", "name"=>"title", "label"=>t("Site title:","install.po"), "value"=>$_REQUEST["title"]?$_REQUEST["title"]:$title, "required"=>true);
    
    $fields[] = array("type"=>"text", "name"=>"slogan", "label"=>t("Slogan:","install.po"), "value"=>$_REQUEST["slogan"]);
    
    include("include/time_zones.php");
    $timezones_list = JarisCMS\System\GetTimezones();
    $timezones = array();
    foreach($timezones_list as $timezone_text)
    {
        $timezones["$timezone_text"] = "$timezone_text";
    }
    $fields[] = array("type"=>"select", "label"=>t("Default timezone:","install.po"), "name"=>"timezone", "id"=>"timezone", "value"=>$timezones, "selected"=>$_REQUEST["timezone"]);

    $fields[] = array("type"=>"other", "html_code"=>"<br />");

    $fields[] = array("type"=>"checkbox", "name"=>"auto_detect_base_url", "label"=>t("Auto detect base url?","install.po"), "checked"=>$_REQUEST["auto_detect_base_url"]?$_REQUEST["auto_detect_base_url"]:true, "description"=>t("Automatically detects domain even if you change it. Mandatory on multisites."));

    $fields[] = array("type"=>"text", "name"=>"base_url", "label"=>t("Base url:","install.po"), "required"=>true, "value"=>$_REQUEST["base_url"]?$_REQUEST["base_url"]:str_replace("/install", "", $base_url));

    $fields[] = array("type"=>"text", "name"=>"username", "label"=>t("Administrator login name:","install.po"));
    
    $fields[] = array("type"=>"text", "name"=>"name", "label"=>t("Administrator full name:","install.po"));
    
    $fields[] = array("type"=>"text", "name"=>"email", "label"=>t("Administrator e-mail:","install.po"));
    
    $fields[] = array("type"=>"password", "name"=>"password", "label"=>t("Administrator password:","install.po"), "required"=>true);

    $fields[] = array("type"=>"password", "name"=>"verify_password", "label"=>t("Re-enter administrator password:","install.po"), "required"=>true);

    $fields[] = array("type"=>"other", "html_code"=>"<div style=\"text-align: right; margin-top: 30px;\">");

    $fields[] = array("type"=>"submit", "name"=>"finish", "value"=>t("Continue","install.po"));

    $fields[] = array("type"=>"other", "html_code"=>"</div>");

    $fieldset[] = array("fields"=>$fields);

    $content .= JarisCMS\Form\Generate($parameters, $fieldset);
}

//Enter mailing details
else if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "mailing_details")
{
    if(isset($_REQUEST["save_mail"]))
    {
        $error = false;

        if(trim($_REQUEST["mailer_from_name"]) == "" || trim($_REQUEST["mailer_from_email"]) == "")
        {
            $error = true;
        }

        if(!$error)
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

                header("Location: " . $base_url . "/install.php?action=cleanurl_check");
                exit;
            }
            else
            {
                $error_message = t("Configuration could not be save. Check your write permissions on the data directory.","install.po");
            }
        }
        else
        {
            $error_message = t("You need to provide all the fields","install.po");
        }
    }
    
    unset($fields);
    
    $step_title = t("Step Three - Mailing Details","install.po");
    
    $parameters["name"] = "mailer-settings";
    $parameters["class"] = "mailer-settings";
    $parameters["action"] = $_SERVER["PHP_SELF"] . "?action=mailing_details";
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

    $fields[] = array("type"=>"other", "html_code"=>"<div style=\"text-align: right; margin-top: 30px;\">");

    $fields[] = array("type"=>"submit", "name"=>"save_mail", "value"=>t("Continue","install.po"));

    $fields[] = array("type"=>"other", "html_code"=>"</div>");

    $fieldset[] = array("fields"=>$fields);

    $content .= JarisCMS\Form\Generate($parameters, $fieldset);
}

//Check if clean URL are available
else if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "cleanurl_check")
{
    $step_title = t("Step Four - Clean URL Check","install.po");
    
    $content .= t("Url rewrites make url's more readable and easy to acess. Here are the results of the test:", "install.po");
    
    $content .= "<br /><br />";
    
    $url = JarisCMS\Setting\Get("base_url", "main");
    
    if(JarisCMS\System\URLExists($url . "/search"))
    {
        $cleanurl = JarisCMS\Setting\Get("clean_urls", "main");
        
        if(!$cleanurl)
        {
            JarisCMS\Setting\Save("clean_urls", true, "main");
        }
        
        $content .= "<span style=\"color: #3cbe3e; font-weight: bold\">" . t("SUPPORTED and Activated","install.po") . "</span>";
    }
    else
    {
        $content .= "<span style=\"color: red; font-weight: bold\">" . t("NOT SUPPORTED","install.po") . "</span>";
        $content .= "<br /><br />";
        $content .= t("There are many factors that make clean url not work. If using apache server check if mod_rewrite is activated.","install.po");
    }
    
    $parameters["action"] = $_SERVER["PHP_SELF"] . "?action=finalize_installation";
    $parameters["method"] = "post";

    $fields[] = array("type"=>"other", "html_code"=>"<div style=\"text-align: right; margin-top: 30px;\">");

    $fields[] = array("type"=>"submit", "name"=>"next", "value"=>t("Continue","install.po"));

    $fields[] = array("type"=>"other", "html_code"=>"</div>");

    $fieldset[] = array("fields"=>$fields);

    $content .= JarisCMS\Form\Generate($parameters, $fieldset);
}

//Finalize Installation
else if(isset($_REQUEST["action"]) && $_REQUEST["action"] == "finalize_installation")
{
    $step_title = t("Step Five - You are done!","install.po");

    $content = t("<b>Congratulations,</b> you have successfully installed Jaris content management system. To visit your index site click", "install.po") .
    " <a style=\"font-weight: bold; color: #000000; text-decoration: underline\" href=\"" . str_replace("/install", "", $base_url) . "\">
    " . t("here","install.po") . "</a> " . t("and login with the name and password you specified","install.po") . ".";
}
?>

<html>
<head>
<title><?php print t("Jaris CMS - Installation Script","install.po"); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style>
body
{
    margin: 0 0 0 0;
    background-color: #f1f2f4;
}

/*Generated Forms*/
form .caption
{
    margin-top: 20px;

    font-weight: bold;
}

form fieldset
{
    margin-top: 20px;
}

form .caption .required
{
    color: red;
    font-weight: bold;
}

form .description
{
    font-size: 12px;
}

form .form-text
{
    width: 410px;
}

form .form-password
{
    width: 410px;
}

form .form-textarea
{
    width: 410px;
}

form .form-submit
{
    margin-top: 15px;
}

form .edit-user-picture
{
    margin-bottom: 15px;
}
</style>
</head>

<body>
<center>

<div style="padding: 15px; background-color: #257fdc; color: #ffffff; text-align: left; font-size: 32px; border-bottom: solid #f0b656 3px;">
    <?php print t("Jaris CMS - Installation Script","install.po"); ?>
</div>

<div style="width: 90%; border: solid #f0b656 1px; background-color: #d0dde7; margin-top: 30px;">
    <div style="padding: 15px;">
        <h3 style="text-align: left; margin-bottom: 30px; padding-bottom: 15px; border-bottom: dashed 2px #257fdc;">
            <?php print $step_title ?>
        </h3>

        <?php if($error_message){?>
        <div style="background-color: #f1f2f4; border: solid #000000 1px; color: red; font-weight: bold; padding: 5px;">
            <?php print $error_message ?>
        </div>
        <?php } ?>

        <div style="text-align: left; padding-left: 15px; font-size: 14px;">
            <?php print $content ?>
        </div>
    </div>
</div>

</center>
</body>

</html>
