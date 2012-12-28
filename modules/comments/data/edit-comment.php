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
        Edit comment
    field;

    field: content
        <?php
            if(isset($_REQUEST["s"]) && JarisCMS\Security\GetCurrentUser() == "Guest")
            {
                session_destroy();
                session_id($_REQUEST["s"]);
                session_start();
            }
            
            JarisCMS\Security\ProtectPage(array("edit_comments"));
            
            if(!JarisCMS\Security\IsAdminLogged())
            {
                if(!JarisCMS\Module\Comments\IsFromCurrentUser($_REQUEST["id"], $_REQUEST["page"]))
                {
                    JarisCMS\Security\ProtectPage();
                }
            }
            
            if(isset($_REQUEST["comment"]) && isset($_REQUEST["id"]) && isset($_REQUEST["page"]) && isset($_REQUEST["type"]) && isset($_REQUEST["user"]))
            {
                $type_settings = JarisCMS\Module\Comments\GetSettings($_REQUEST["type"]);
                
                if($type_settings["enabled"])
                {
                    $comment = substr(JarisCMS\Search\StripHTMLTags($_REQUEST["comment"]), 0, $type_settings["maximun_characters"]);
                    JarisCMS\Module\Comments\Edit($comment, $_REQUEST["id"], $_REQUEST["page"], $_REQUEST["user"]);
                    
                    print "0";
                }
            }
        ?>
    field;

    field: is_system
        1
    field;
row;
