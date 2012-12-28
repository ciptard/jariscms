<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Script to run all cron jobs.
 * 
 *@example To run cron job from system an example command is the following:
 * /usr/bin/php-cgi /home/username/public_html/cron.php "HTTP_HOST=www.mysite.com" or
 * /usr/bin/php-cgi /home/username/public_html/cron.php 'HTTP_HOST=www.mysite.com' or
 * /usr/bin/php /home/username/public_html/cron.php www.mysite.com
 */
 
//Disables execution time and enables unlimited execution time for cron jobs
ini_set('max_execution_time', '0');

//If running in cli mode
if($_SERVER["PHP_SELF"] != "")
{
	chdir(str_replace("cron.php", "", $_SERVER["PHP_SELF"]));
	
	$_REQUEST["HTTP_HOST"] = $_SERVER["argv"][0];
}

//File that includes all neccesary system functions
include("include/all_includes.php");

//Starts the main session for the user
if(isset($_SERVER["SERVER_NAME"]))
{
    session_start();
}

//Initialize error handler
JarisCMS\System\InitErrorCatch();

//Overrides configurations variables on settings.php if needed.
JarisCMS\Setting\Override();

//Check if cms is run for the first time and run the installer
JarisCMS\System\CheckInstall();

//Check if site status is online to continue
JarisCMS\System\CheckOffline();

//Check if cron is already running and if running exit cron script
if(!file_exists(JarisCMS\Setting\GetDataDirectory() . "cron_running.lock"))
{
	file_put_contents(JarisCMS\Setting\GetDataDirectory() . "cron_running.lock", "");
}
else
{
	exit;
}

//Calls the cron job function of each module that requires it
JarisCMS\Module\Hook("System", "CronJob");

//Save execution time
JarisCMS\Setting\Save("last_cron_jobs_run", time(), "main");

//Remove cron lock file
unlink(JarisCMS\Setting\GetDataDirectory() . "cron_running.lock");

//If script was executed from control panel return to it
if(isset($_REQUEST["return"]))
{
    JarisCMS\System\AddMessage(t("All jobs successfully executed."));
    JarisCMS\System\GoToPage($_REQUEST["return"]);
}
else if(!JarisCMS\Security\IsAdminLogged() && isset($_SERVER["SERVER_NAME"]))
{
    JarisCMS\System\GoToPage("");
}
?>
