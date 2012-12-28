<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Script to manage file uploads.
 * 
 */

//File that includes all neccesary system functions
include("include/all_includes.php");

//Starts the main session for the user
session_start();

//Initialize error handler
JarisCMS\System\InitErrorCatch();

//Overrides configurations variables on settings.php if needed.
JarisCMS\Setting\Override();

//Check if cms is run for the first time and run the installer
JarisCMS\System\CheckInstall();

//Check if site status is online to continue
JarisCMS\System\CheckOffline();

if(JarisCMS\Form\CanUpload())
{
    error_reporting(E_ALL | E_STRICT);
    require('include/third_party/upload.class.php');
    
    $upload_path = str_replace(
        "data.php", 
        "uploads/", 
        JarisCMS\User\GeneratePath(
            JarisCMS\Security\GetCurrentUser(), 
            JarisCMS\Security\GetCurrentUserGroup()
        )
    );
    
    if(!is_dir($upload_path))
        JarisCMS\FileSystem\MakeDir($upload_path, 0755, true);
    
    $upload_handler = new UploadHandler(
        array('script_url'=>  JarisCMS\URI\PrintURL("upload.php"), "upload_dir"=>$upload_path)
    );
}

?>
