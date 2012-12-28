<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content blocks edit page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Page Block") ?>
    field;

    field: content

        <?php
            JarisCMS\Security\ProtectPage(array("edit_content_blocks"));
            
            if(!JarisCMS\Page\IsOwner($_REQUEST["uri"]))
            {
                JarisCMS\Security\ProtectPage();
            }

            $block_data = JarisCMS\Block\GetData($_REQUEST["id"], $_REQUEST["position"], $_REQUEST["uri"]);

            if(isset($_REQUEST["btnSave"]))
            {
                //Trim uri spaces
                $_REQUEST["page_uri"] = trim($_REQUEST["page_uri"]);
                
                $block_data["description"] = $_REQUEST["description"];
                $block_data["title"] = $_REQUEST["title"];
                $block_data["content"] = $_REQUEST["content"];
                $block_data["display_rule"] = "all_except_listed";
                $block_data["groups"] = $_REQUEST["groups"];
                $block_data["post_block"] = $_REQUEST["post_block"];
                $block_data["uri"] = $_REQUEST["page_uri"];
                if(JarisCMS\Group\GetPermission("return_code_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    $block_data["return"] = $_REQUEST["return"];
                }
                if(!$block_data["is_system"])
                {
                    $block_data["content"] = $_REQUEST["content"];
                }
                if(JarisCMS\Group\GetPermission("input_format_content_blocks", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged() && !$block_data["is_system"])
                {
                    $block_data["input_format"] = $_REQUEST["input_format"];
                }

                if(JarisCMS\Block\Edit($_REQUEST["id"], $_REQUEST["position"], $block_data, $_REQUEST["uri"]))
                {
                    if($_REQUEST["position"] != $_REQUEST["new_position"])
                    {
                        JarisCMS\Block\Move($_REQUEST["id"], $_REQUEST["position"], $_REQUEST["new_position"], $_REQUEST["uri"]);
                    }
                    
                    JarisCMS\System\AddMessage(t("Your changes have been saved to the block."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/pages/blocks", array("uri"=>$_REQUEST["uri"]));
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/pages/blocks", array("uri"=>$_REQUEST["uri"]));
            }
        ?>

        <?php
        
            if(JarisCMS\Group\GetPermission("delete_content", JarisCMS\Security\GetCurrentUserGroup()) && JarisCMS\Page\IsOwner($_REQUEST["uri"]))
            {
               JarisCMS\System\AddTab(t("Delete"), "admin/pages/blocks/delete", array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"], "uri"=>$_REQUEST["uri"]));
            }
            JarisCMS\System\AddTab(t("Blocks"), "admin/pages/blocks", array("uri"=>$_REQUEST["uri"]));

            //Print block edit form

            $parameters["name"] = "block-page-edit";
            $parameters["class"] = "block-page-edit";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/pages/blocks/edit");
            $parameters["method"] = "post";

            $fields[] = array("type"=>"hidden", "name"=>"uri", "value"=>$_REQUEST["uri"]);
            $fields[] = array("type"=>"hidden", "name"=>"id", "value"=>$_REQUEST["id"]);
            $fields[] = array("type"=>"hidden", "name"=>"position", "value"=>$_REQUEST["position"]);
            
            $positions[t("Header")] = "header";
            $positions[t("Left")] = "left";
            $positions[t("Right")] = "right";
            $positions[t("Center")] = "center";
            $positions[t("Footer")] = "footer";
            $positions[t("None")] = "none";

            $fields[] = array("type"=>"select", "name"=>"new_position", "label"=>t("Position:"), "id"=>"new_position", "value"=>$positions, "selected"=>$_REQUEST["position"]);

            $fields[] = array("type"=>"text", "name"=>"description", "label"=>t("Description:"), "id"=>"description", "value"=>$block_data["description"], "required"=>true);
            $fields[] = array("type"=>"text", "name"=>"title", "label"=>t("Title:"), "id"=>"title", "value"=>$block_data["title"]);
            
            if(!$block_data["is_system"])
            {
                $fields[] = array("type"=>"textarea", "name"=>"content", "label"=>t("Content:"), "id"=>"content", "value"=>$block_data["content"]);
            }
            
            $fieldset[] = array("fields"=>$fields);
            
            if(JarisCMS\Group\GetPermission("input_format_content_blocks", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged() && !$block_data["is_system"])
            {
                $fields_inputformats = array();
                foreach(JarisCMS\InputFormat\GetAll() as $machine_name=>$fields_formats)
                {
                    
                    $fields_inputformats[] = array("type"=>"radio", "checked"=>$machine_name==$block_data["input_format"]?true:false, "name"=>"input_format", "description"=>$fields_formats["description"], "value"=>array($fields_formats["title"]=>$machine_name));
                }            
                $fieldset[] = array("fields"=>$fields_inputformats, "name"=>t("Input Format"));
            }
            
            $fieldset[] = array("fields"=>JarisCMS\Group\GetListForFields($block_data["groups"]), "name"=>t("Users Access"), "collapsed"=>true, "collapsible"=>true, "description"=>t("Select the groups that can see the block. Don't select anything to display block to everyone."));
            
            $post_block[t("Enable")] = true;
            $post_block[t("Disable")] = false;
            
            $postblock_fields[] = array("type"=>"radio", "name"=>"post_block", "id"=>"post_block", "value"=>$post_block, "checked"=>$block_data["post_block"]);
            $postblock_fields[] = array("type"=>"uri", "name"=>"page_uri", "label"=>t("Uri:"), "id"=>"page_uri", "value"=>$block_data["uri"], "required"=>false, "description"=>t("The uri of the page to display a summary."));
            
            $fieldset[] = array("name"=>t("Post Settings"), "fields"=>$postblock_fields, "collapsible"=>true, "collapsed"=>$block_data["post_block"]?false:true);
            
            if(JarisCMS\Group\GetPermission("return_code_content_blocks", JarisCMS\Security\GetCurrentUserGroup()) || JarisCMS\Security\IsAdminLogged())
            {
                $fields_other[] = array("type"=>"textarea", "name"=>"return", "label"=>t("Return Code:"), "id"=>"return", "value"=>$block_data["return"], "description"=>t("PHP code enclosed with &lt;?php code ?&gt; to evaluate if block should display by printing true or false. for example: &lt;?php if(JarisCMS\Security\IsUserLogged()) print \"true\"; else print \"false\"; ?&gt;"));
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
