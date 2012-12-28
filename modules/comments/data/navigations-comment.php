<?php
/**
 *Copyright 2008, Jefferson Gonzàlez (JegoYalu.com)
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
        Navigations comment
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_comments"));
            
            if(isset($_REQUEST["uri"]) && isset($_REQUEST["page"]) && isset($_REQUEST["type"]))
            {
                $page_data = JarisCMS\Page\GetData($_REQUEST["uri"]);
                
                $comment_settings = JarisCMS\Module\Comments\GetSettings($page_data["type"]);
        
                if($comment_settings["enabled"])
                {    
                    print JarisCMS\Module\Comments\PrintAll($_REQUEST["uri"], $page_data[0]["type"], $_REQUEST["page"]);
                }
            }
        ?>
    field;

    field: is_system
        1
    field;
row;
