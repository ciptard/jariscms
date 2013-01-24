﻿<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the global edit block page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Block") ?>
    field;

    field: content

        <?php
            JarisCMS\Security\ProtectPage(array("view_blocks", "edit_blocks"));

            $block_data = JarisCMS\Block\GetData($_REQUEST["id"], $_REQUEST["position"]);

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("block-edit"))
            {
                $block_data["description"] = $_REQUEST["description"];
                $block_data["title"] = $_REQUEST["title"];
                $block_data["display_rule"] = $_REQUEST["display_rule"];
                $block_data["pages"] = $_REQUEST["pages"];
                $block_data["groups"] = $_REQUEST["groups"];
                if(JarisCMS\Group\GetPermission("return_code_blocks", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
                {
                    $block_data["return"] = $_REQUEST["return"];
                }
                if(!$block_data["is_system"])
                {
                    $block_data["content"] = $_REQUEST["content"];
                    
                    if(JarisCMS\Group\GetPermission("input_format_blocks", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged() && !$block_data["is_system"])
                    {
                        $block_data["input_format"] = $_REQUEST["input_format"];
                    }
                }

                if(JarisCMS\Block\Edit($_REQUEST["id"], $_REQUEST["position"], $block_data))
                {
                    if($_REQUEST["position"] != $_REQUEST["new_position"])
                    {
                        JarisCMS\Block\Move($_REQUEST["id"], $_REQUEST["position"], $_REQUEST["new_position"]);
                    }
                    
                    JarisCMS\System\AddMessage(t("Your changes have been saved to the block."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/blocks");
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/blocks");
            }
        ?>

        <?php

            JarisCMS\System\AddTab(t("Delete"), "admin/blocks/delete", array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
            JarisCMS\System\AddTab(t("Blocks"), "admin/blocks");

            //Print block edit form

            $parameters["name"] = "block-edit";
            $parameters["class"] = "block-edit";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/blocks/edit");
            $parameters["method"] = "post";

            $fields[] = array("type"=>"hidden", "name"=>"id", "value"=>$_REQUEST["id"]);
            $fields[] = array("type"=>"hidden", "name"=>"position", "value"=>$_REQUEST["position"]);
            
            $positions[t("Header")] = "header";
            $positions[t("Left")] = "left";
            $positions[t("Right")] = "right";
            $positions[t("Center")] = "center";
            $positions[t("Footer")] = "footer";
            $positions[t("None")] = "none";
            
            $fields[] = array("type"=>"select", "name"=>"new_position", "label"=>t("Position:"), "id"=>"new_position", "value"=>$positions, "selected"=>$_REQUEST["new_position"]?$_REQUEST["new_position"]:$_REQUEST["position"]);
            $fields[] = array("type"=>"text", "name"=>"description", "label"=>t("Description:"), "id"=>"description", "value"=>$block_data["description"], "required"=>true);
            $fields[] = array("type"=>"text", "name"=>"title", "label"=>t("Title:"), "id"=>"title", "value"=>$block_data["title"]);
            
            if(!$block_data["is_system"])
            {
                $fields[] = array("type"=>"textarea", "name"=>"content", "label"=>t("Content:"), "id"=>"content", "value"=>$block_data["content"]);
            }
            
            $fieldset[] = array("fields"=>$fields);
            
            if(!$block_data["is_system"])
            {
                if(JarisCMS\Group\GetPermission("input_format_blocks", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged() && !$block_data["is_system"])
                {
                    $fields_inputformats = array();
                    foreach(JarisCMS\InputFormat\GetAll() as $machine_name=>$fields_formats)
                    {
                        
                        $fields_inputformats[] = array("type"=>"radio", "checked"=>$machine_name==$block_data["input_format"]?true:false, "name"=>"input_format", "description"=>$fields_formats["description"], "value"=>array($fields_formats["title"]=>$machine_name));
                    }            
                    $fieldset[] = array("fields"=>$fields_inputformats, "name"=>t("Input Format"));
                }
            }
            
            $fieldset[] = array("fields"=>JarisCMS\Group\GetListForFields($block_data["groups"]), "name"=>t("Users Access"), "collapsed"=>true, "collapsible"=>true, "description"=>t("Select the groups that can see the block. Don't select anything to display block to everyone."));
            
            $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
            $display_rules[t("Just display on the listed pages.")] = "just_listed";
            
            $fields_pages[] = array("type"=>"radio", "checked"=>$block_data["display_rule"], "name"=>"display_rule", "id"=>"display_rule", "value"=>$display_rules);
            $fields_pages[] = array("type"=>"uriarea", "name"=>"pages", "label"=>t("Pages:"), "id"=>"pages", "value"=>$block_data["pages"]);
            
            $fieldset[] = array("fields"=>$fields_pages, "name"=>"Pages to display", "description"=>t("List of uri's seperated by comma (,). Also supports the wildcard (*), for example: my-section/*"));
            
            if(JarisCMS\Group\GetPermission("return_code_blocks", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
            {
                $fields_other[] = array("type"=>"textarea", "name"=>"return", "label"=>t("Return Code:"), "id"=>"return", "value"=>$block_data["return"], "description"=>t("PHP code enclosed with &lt;?php code ?&gt; to evaluate if block should display by printing true or false. for example: &lt;?php if(JarisCMS\\Security\\IsUserLogged()) print \"true\"; else print \"false\"; ?&gt;"));
            }

            $fields_other[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
            $fields_other[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

            $fieldset[] = array("fields"=>$fields_other);

            print JarisCMS\Form\Generate($parameters, $fieldset);
        ?>

    field;
    
    field: is_system
        1
    field;
row;
