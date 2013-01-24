<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content blocks add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Add Page Block") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("add_content_blocks"));
            
            if(!JarisCMS\Page\IsOwner($_REQUEST["uri"]))
            {
                JarisCMS\Security\ProtectPage();
            }

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("add-page-block"))
            {
                $fields["description"] = $_REQUEST["description"];
                $fields["title"] = $_REQUEST["title"];
                $fields["content"] = $_REQUEST["content"];
                $fields["groups"] = $_REQUEST["groups"];
                $fields["post_block"] = $_REQUEST["post_block"];
                $fields["uri"] = $_REQUEST["page_uri"];
                if(JarisCMS\Group\GetPermission("input_format_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    $fields["input_format"] = $_REQUEST["input_format"];
                }
                $fields["order"] = 0;
                $fields["display_rule"] = "all_except_listed";
                if(JarisCMS\Group\GetPermission("return_code_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    $fields["return"] = $_REQUEST["return"];
                }

                if(JarisCMS\Block\Add($fields, $_REQUEST["position"], $_REQUEST["uri"]))
                {
                    JarisCMS\System\AddMessage(t("The blocks was successfully created."));
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

            $parameters["name"] = "add-page-block";
            $parameters["class"] = "add-page-block";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/pages/blocks/add");
            $parameters["method"] = "post";

            $positions[t("Header")] = "header";
            $positions[t("Left")] = "left";
            $positions[t("Right")] = "right";
            $positions[t("Center")] = "center";
            $positions[t("Footer")] = "footer";
            $positions[t("None")] = "none";

            $fields[] = array("type"=>"hidden", "name"=>"uri", "value"=>$_REQUEST["uri"]);
            $fields[] = array("type"=>"select", "name"=>"position", "label"=>t("Position:"), "id"=>"position", "value"=>$positions, "selected"=>$_REQUEST["position"]);
            $fields[] = array("type"=>"text", "name"=>"description", "value"=>$_REQUEST["description"], "label"=>t("Description:"), "id"=>"description", "required"=>true);
            $fields[] = array("type"=>"text", "name"=>"title", "value"=>$_REQUEST["title"], "label"=>t("Title:"), "id"=>"title");
            $fields[] = array("type"=>"textarea", "name"=>"content", "value"=>$_REQUEST["content"], "label"=>t("Content:"), "id"=>"content");
            
            $fieldset[] = array("fields"=>$fields);
            
            if(JarisCMS\Group\GetPermission("input_format_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $fields_inputformats = array();
                foreach(JarisCMS\InputFormat\GetAll() as $machine_name=>$fields_formats)
                {
                    
                    $fields_inputformats[] = array("type"=>"radio", "checked"=>$machine_name=="full_html"?true:false, "name"=>"input_format", "description"=>$fields_formats["description"], "value"=>array($fields_formats["title"]=>$machine_name));
                }            
                $fieldset[] = array("fields"=>$fields_inputformats, "name"=>t("Input Format"));
            }
            
            $fieldset[] = array("fields"=>JarisCMS\Group\GetListForFields(), "name"=>t("Users Access"), "collapsed"=>true, "collapsible"=>true, "description"=>t("Select the groups that can see the block. Don't select anything to display block to everyone."));
            
            $post_block[t("Enable")] = true;
            $post_block[t("Disable")] = false;

            $postblock_fields[] = array("type"=>"radio", "name"=>"post_block", "id"=>"post_block", "value"=>$post_block, "checked"=>$_REQUEST["post_block"]);
            $postblock_fields[] = array("type"=>"uri", "name"=>"page_uri", "value"=>$_REQUEST["page_uri"], "label"=>t("Uri:"), "id"=>"page_uri", "required"=>false, "description"=>t("The uri of the page to display a summary."));
            
            $fieldset[] = array("name"=>t("Post Settings"), "fields"=>$postblock_fields, "collapsible"=>true, "collapsed"=>true);
            
            if(JarisCMS\Group\GetPermission("return_code_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $fields_other[] = array("type"=>"textarea", "name"=>"return", "value"=>$_REQUEST["return"], "label"=>t("Return Code:"), "id"=>"return", "description"=>t("PHP code enclosed with &lt;?php code ?&gt; to evaluate if block should display by printing true or false. for example: &lt;?php if(JarisCMS\\Security\\IsUserLogged()) print \"true\"; else print \"false\"; ?&gt;"));
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
