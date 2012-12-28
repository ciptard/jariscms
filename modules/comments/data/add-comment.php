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
        Add comment
    field;

    field: content
        <?php
            if(isset($_REQUEST["s"]) && JarisCMS\Security\GetCurrentUser() == "Guest")
            {
                session_destroy();
                session_id($_REQUEST["s"]);
                session_start();
            }
            
            JarisCMS\Security\ProtectPage(array("add_comments"));
            
            if(isset($_REQUEST["comment"]) && isset($_REQUEST["page"]) && isset($_REQUEST["type"]))
            {
                $type_settings = JarisCMS\Module\Comments\GetSettings($_REQUEST["type"]);
                
                if($type_settings["enabled"])
                {
                    $comment = substr(JarisCMS\Search\StripHTMLTags($_REQUEST["comment"]), 0, $type_settings["maximun_characters"]);
                    
                    $page_data = JarisCMS\Page\GetData($_REQUEST["page"]);
                    
                    if(trim($comment) != "" && $page_data)
                    {
                        $user_data = JarisCMS\User\GetData($page_data["author"]);
                        
                        $id = JarisCMS\Module\Comments\Add($comment, $_REQUEST["page"], $_REQUEST["rid"]);
                        
                        //Send poster a new comment notification if has this permission
                        if(JarisCMS\Group\GetPermission("notifications_comments", $user_data["group"]) && JarisCMS\Security\GetCurrentUser() != $page_data["author"])
                        {
                            $to[$user_data["name"]] = $user_data["email"];
                            $subject = t("You have a new comment on") . " " . JarisCMS\Setting\Get("title", "main");
                            $html_message = t("A user posted the following comment on your post:") . "<br /><br />";
                            $html_message .= "<i>" . JarisCMS\Search\StripHTMLTags($comment) . "</i><br /><br />";
                            $html_message .= t("To reply or view your original post click on the following link:") . "<br />";
                            $html_message .= "<a target=\"_blank\" href=\"" . JarisCMS\URI\PrintURL($_REQUEST["page"]) . "\">" . JarisCMS\URI\PrintURL($_REQUEST["page"]) . "</a>";
                            
                            JarisCMS\Email\Send($to, $subject, $html_message);
                        }
                        
                        $data = JarisCMS\Module\Comments\GetData($id, $_REQUEST["page"]);
                        
                        print JarisCMS\Module\Comments\Theme($data, $_REQUEST["page"], $_REQUEST["type"]);
                    }
                }
            }
        ?>
    field;

    field: is_system
        1
    field;
row;
