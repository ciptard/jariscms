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
        <?php print t("Blog Settings") ?>
    field;

    field: content
        <?php

            JarisCMS\Security\ProtectPage(array("edit_settings"));

            //Get exsiting settings or defualt ones if main settings table doesn't exist
            $blog_settings = JarisCMS\Module\Blog\GetSettings();

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("edit-blog-settings"))
            {
                //Check if write is possible and continue to write settings
                if(JarisCMS\Setting\Save("main_category", $_REQUEST["main_category"], "blogs"))
                {
                    JarisCMS\System\AddMessage(t("Your settings have been successfully saved."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/settings");
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/settings");
            }

            $parameters["name"] = "edit-blog-settings";
            $parameters["class"] = "edit-blog-settings";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/blog", "blog"));
            $parameters["method"] = "post";
            
            $categories[t("-None Selected-")] = "";
            $category_data = JarisCMS\Category\GetList();
            if($category_data)
            {
                foreach($category_data as $machine_name=>$data)
                {
                    $categories[t($data["name"])] = $machine_name;
                }
            }
            
            $fields[] = array("type"=>"select", "name"=>"main_category", "label"=>t("Blog categories:"), "id"=>"main_category", "value"=>$categories, "selected"=>$blog_settings["main_category"], "description"=>t("The main category where users can select a sub category that represent its blog content."));

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
