<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the video settings management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Comment Settings") ?>
    field;

    field: content
        <?php

            JarisCMS\Security\ProtectPage(array("view_types", "edit_types"));

            //Get exsiting settings or defualt ones if main settings table doesn't exist
            $comment_settings = JarisCMS\Module\Comments\GetSettings($_REQUEST["type"]);

            if(isset($_REQUEST["btnSave"]))
            {
                 $data["enabled"] = $_REQUEST["enabled"];
                 $data["maximun_characters"] = $_REQUEST["maximun_characters"];
                 
                //Check if write is possible and continue to write settings
                if(JarisCMS\Setting\Save($_REQUEST["type"], serialize($data), "comments"))
                {
                    JarisCMS\System\AddMessage(t("Your comment settings have been successfully saved."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/types/edit", array("type"=>$_REQUEST["type"]));
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/types/edit", array("type"=>$_REQUEST["type"]));
            }
            
            JarisCMS\System\AddTab(t("Edit Type"), "admin/types/edit", array("type"=>$_REQUEST["type"]));

            $parameters["name"] = "edit-comments-settings";
            $parameters["class"] = "edit-comments-settings";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/types/comments", "comments"));
            $parameters["method"] = "post";
            
            $fields[] = array("type"=>"hidden", "name"=>"type", "value"=>$_REQUEST["type"]);
            
            $enabled[t("Enable")] = true;
            $enabled[t("Disable")] = false;

            $fields[] = array("type"=>"radio", "name"=>"enabled", "id"=>"enabled", "value"=>$enabled, "checked"=>$comment_settings["enabled"]);
            
            $fields[] = array("type"=>"text", "name"=>"maximun_characters", "label"=>t("Maximun characters:"), "id"=>"maximun_characters", "value"=>$comment_settings["maximun_characters"], "description"=>t("The maximun characters allowed per user post."));

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
