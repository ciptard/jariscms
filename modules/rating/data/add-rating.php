<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the video upload script.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        Add Rating
    field;

    field: content
        <?php
            if(isset($_REQUEST["s"]) && JarisCMS\Security\GetCurrentUser() == "Guest")
            {
                session_destroy();
                session_id($_REQUEST["s"]);
                session_start();
            }
            
            JarisCMS\Security\ProtectPage(array("rate_content"));
            
            if(isset($_REQUEST["point"]) && isset($_REQUEST["page"]) && isset($_REQUEST["type"]))
            {
                $type_settings = JarisCMS\Module\Rating\GetSettings($_REQUEST["type"]);
                
                if($type_settings["enabled"])
                { 
                    //Ensure rate point is between a valid range
                    if($_REQUEST["point"] >= 1 && $_REQUEST["point"] <= $type_settings["number_of_points"])
                    {
                        JarisCMS\Module\Rating\Add($_REQUEST["point"], $_REQUEST["page"]);
                    }
                    
                    $rating_data = JarisCMS\Module\Rating\Get($_REQUEST["page"]);
                    
                    print JarisCMS\Module\Rating\TotalPoints($rating_data, $type_settings["number_of_points"]);
                }
            }
        ?>
    field;

    field: is_system
        1
    field;
row;
