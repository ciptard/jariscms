<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Script to get an json list of uri's that match a given query. Used
 * for the auto complete functionality of uri's
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

$query = JarisCMS\URI\FromText($_REQUEST["query"], true);

if(JarisCMS\SQLite\DBExists("search_engine"))
{
    $db = JarisCMS\SQLite\Open("search_engine");
    
    $select = "select uri, haspermission(groups, '".JarisCMS\Security\GetCurrentUserGroup()."') as has_permissions 
    from uris where uri like '{$query}%' and has_permissions > 0 limit 0,10";
    
    $result = JarisCMS\SQLite\Query($select, $db);
    
    $list = array();
    
    while($data = JarisCMS\SQLite\FetchArray($result))
    {
        $list[] = $data["uri"];
    }
    
    print json_encode(array("query"=>$query, "suggestions"=>$list));
}
else
{
    print json_encode(array("query"=>$query, "suggestions"=>array()));
}
?>
