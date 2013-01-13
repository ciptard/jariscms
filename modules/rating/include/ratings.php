<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module include file
 *
 *@note File with general functions
 */

namespace JarisCMS\Module\Rating;

function GetSettings($type)
{
    $settings = array();
    if(!($settings = \JarisCMS\Setting\Get($type, "ratings")))
    {
         $settings["enabled"] = false;
         $settings["number_of_points"] = 5;
         $settings["on_icon"] = "star-on.png";
         $settings["off_icon"] = "star-off.png";
         $settings["half_icon"] = "star-half.png";
         $settings["hints"] = "";
    }
    else
    { 
        $settings = unserialize($settings);
        
        $settings["enabled"] = $settings["enabled"]?$settings["enabled"]:false;
        $settings["number_of_points"] = $settings["number_of_points"]?$settings["number_of_points"]:5;
        $settings["on_icon"] = trim($settings["on_icon"])?$settings["on_icon"]:"star-on.png";
        $settings["off_icon"] = trim($settings["off_icon"])?$settings["off_icon"]:"star-off.png";
        $settings["half_icon"] = trim($settings["half_icon"])?$settings["half_icon"]:"star-half.png";
        $settings["hints"] = trim($settings["hints"])?$settings["hints"]:"";
    }
    
    return $settings;
}

function Get($page, $db_path=null)
{
    $fields["uri"] = $page;
    
    \JarisCMS\SQLite\EscapeArray($fields);
    
    if(!\JarisCMS\SQLite\DBExists("ratings", $db_path))
    {
        return null;
    }
    
    $db = \JarisCMS\SQLite\Open("ratings", $db_path);
    
    $select = "select * from ratings where uri='{$fields['uri']}'";
    
    $result = \JarisCMS\SQLite\Query($select, $db);
    
    $data = \JarisCMS\SQLite\FetchArray($result);
    
    \JarisCMS\SQLite\Close($db);
    
    return $data;
}

function Add($points, $page)
{
    if(!\JarisCMS\Form\CheckNumber($points))
    {
        return;
    }
    
    $page_data = \JarisCMS\Page\GetData($page);
    
    if(!is_array($page_data))
    {
        return;
    }
    
    $fields["uri"] = $page;
    $fields["last_rate_timestamp"] = time();
    $fields["day"] = date("j", $page_data["created_date"]);
    $fields["month"] = date("n", $page_data["created_date"]);
    $fields["year"] = date("Y", $page_data["created_date"]);
    $fields["type"] = $page_data["type"];
    
    \JarisCMS\SQLite\EscapeArray($fields);
    
    $db = \JarisCMS\SQLite\Open("ratings");
    
    //Create rating record on database if not exists
    if(!is_array(Get($page)))
    {
        $insert = "insert into ratings 
        (content_timestamp, last_rate_timestamp, day, month, year, uri, type, points, rates_count)
        values
        (
        '{$page_data['created_date']}',
        '{$fields['last_rate_timestamp']}',
        {$fields['day']},
        {$fields['month']},
        {$fields['year']},
        '{$fields['uri']}',
        '{$fields['type']}',
        0,
        0
        )";
        
        \JarisCMS\SQLite\Query($insert, $db);
    }
    
    $db_user = \JarisCMS\User\GeneratePath(\JarisCMS\Security\GetCurrentUser(), \JarisCMS\Security\GetCurrentUserGroup());
    $db_user = str_replace("data.php", "", $db_user);
    
    //Only sum points if user hasnt already voted
    if(!is_array(Get($page, $db_user)))
    {
        $update = "update ratings set
        points = points+$points,
        rates_count = rates_count+1,
        last_rate_timestamp = '{$fields['last_rate_timestamp']}'
        where uri='{$fields['uri']}'";
        
        \JarisCMS\SQLite\Query($update, $db);
        
        AddToUserDB($points, $fields);
    }
    
    \JarisCMS\SQLite\Close($db);
}

/**
 * To be only called from rating_add, so a record of rating is created
 * for the user that is rating the content and cant rate it again.
 */
function AddToUserDB($points, $data)
{
    $db_path = \JarisCMS\User\GeneratePath(\JarisCMS\Security\GetCurrentUser(), \JarisCMS\Security\GetCurrentUserGroup());
    $db_path = str_replace("data.php", "", $db_path);
    
    //Create ratings data base
    if(!\JarisCMS\SQLite\DBExists("ratings", $db_path))
    {        
        $db = \JarisCMS\SQLite\Open("ratings", $db_path);
        
        \JarisCMS\SQLite\Query("create table ratings (last_rate_timestamp text, day integer, month integer, year integer, uri text, type text, points integer)", $db);
        
        \JarisCMS\SQLite\Query("create index ratings_index on ratings (last_rate_timestamp desc, day desc, month desc, year desc, uri desc, type desc, points desc)", $db);
        
        \JarisCMS\SQLite\Close($db);
    }
    
    //Create rating record on user database if not exists
    if(!is_array(Get($page, $db_path)))
    {
        $db = \JarisCMS\SQLite\Open("ratings", $db_path);
        
        $insert = "insert into ratings 
        (last_rate_timestamp, day, month, year, uri, type, points)
        values
        (
        '{$data['last_rate_timestamp']}',
        {$data['day']},
        {$data['month']},
        {$data['year']},
        '{$data['uri']}',
        '{$data['type']}',
        $points
        )";
        
        \JarisCMS\SQLite\Query($insert, $db);
        
        \JarisCMS\SQLite\Close($db);
    }
}

/**
 * Calculate total amount of points from a given overall rating data.
 * @param array $rating_data Overall rating data of a rated content.
 * @param integer $maximun_points The maximum amount of points the rater can select.
 * @return integer Amount of points to display.
 */
function TotalPoints($rating_data, $maximun_points)
{   
    $total_rates = $rating_data["rates_count"];
    $total_points = $rating_data["points"];
    
    $total_display_points = 0;
    
    if($total_points > $maximun_points)
    {
        $total_display_points = $total_points / ($maximun_points * $total_rates) * $maximun_points;
    }
    else
    {
        if(!$total_points)
        {
            $total_display_points = 0;
        }
        else
        {
            $total_display_points = $total_points;
        }
    }
    
    return $total_display_points;
}

function PrintHints($hints)
{
    $hints_array = explode(",", $hints);
    
    $hints_string = "[";
    
    foreach($hints_array as $hint)
    {
        $hints_string .= "'" . t(trim($hint)) . "',";
    }
    
    $hints_string .= trim($hints_string, ",");
    
    $hints_string .= "]";
    
    return $hints_string;
}

function PrintContent($page, $type)
{   
    $rating_data = Get($page);
    
    $ratings_content = "<div id=\"rating\">\n";
    
    $ratings_content .= "<div class=\"message\"></div>\n";
    
    $ratings_content .= "<div style=\"clear: both\"></div>\n";
    
    //Check if user not logged and encourage user to login and vote
    if(\JarisCMS\Security\GetCurrentUserGroup() == "guest")
    {
        $ratings_content .= "<div class=\"login\">" . 
        "<a href=\"" . \JarisCMS\URI\PrintURL("admin/user", array("return"=>\JarisCMS\URI\Get())) ."\">" . t("Login") . "</a> " . t("or") . " " .
        "<a href=\"" . \JarisCMS\URI\PrintURL("register", array("return"=>\JarisCMS\URI\Get())) ."\">" . t("Register") . "</a> " . t("to rate.") .
        "</div>\n";
    }
    
    $ratings_content .= "<div class=\"content\">\n";
    
    $db_user = \JarisCMS\User\GeneratePath(\JarisCMS\Security\GetCurrentUser(), \JarisCMS\Security\GetCurrentUserGroup());
    $db_user = str_replace("data.php", "", $db_user);
    
    //Check if user has permissions to rate and have not rated yet
    if(\JarisCMS\Group\GetPermission("rate_content", \JarisCMS\Security\GetCurrentUserGroup()) && !is_array(Get($page, $db_user)))
    {
        $ratings_content .= "<div class=\"label\">" . t("Rate this:") . "</div>\n";
    }
    else
    {
        $ratings_content .= "<div class=\"label\">" . t("Rating:") . "</div>\n";
    }
    
    $ratings_content .= "<div id=\"rating-select\" class=\"select\"></div>\n";
    
    $ratings_content .= "</div>";
    
    $ratings_content .= "<div style=\"clear: both\"></div>\n";
    
    $ratings_content .= "</div>";
    
    return $ratings_content; 
}

?>