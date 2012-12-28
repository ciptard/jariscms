<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the group edit page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Group") ?>
    field;

    field: content
        <?php

            JarisCMS\Security\ProtectPage(array("view_groups", "edit_groups"));

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("edit-group"))
            {
                $fields["name"] = $_REQUEST["name"];
                $fields["description"] = $_REQUEST["description"];

                $error = false;

                if($_REQUEST["new_group_name"] == "" || $_REQUEST["name"] == "" || $_REQUEST["description"] == "")
                {
                    $error = true;
                    JarisCMS\System\AddMessage(t("You need to provide all the fields"), "error");
                }

                if(!$error)
                {
                    $message = JarisCMS\Group\Edit($_REQUEST["group"], $fields, $_REQUEST["new_group_name"]);

                    if($message == "true")
                    {
                        JarisCMS\System\AddMessage(t("Your changes have been saved."));
                    }
                    else
                    {
                        JarisCMS\System\AddMessage($message, "error");
                    }

                    JarisCMS\System\GoToPage("admin/groups");
                }
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/groups");
            }

            $group_data = JarisCMS\Group\GetData($_REQUEST["group"]);

            $parameters["name"] = "edit-group";
            $parameters["class"] = "edit-group";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/groups/edit");
            $parameters["method"] = "post";

            $fields[] = array("type"=>"hidden", "value"=>$_REQUEST["group"], "name"=>"group");
            $fields[] = array("type"=>"text", "value"=>$_REQUEST["group"], "name"=>"new_group_name", "label"=>t("Machine name:"), "id"=>"new_group_name", "required"=>true, "description"=>t("A readable machine name, like for example: my-group."));
            $fields[] = array("type"=>"text", "value"=>$group_data["name"], "name"=>"name", "label"=>t("Name:"), "id"=>"name", "required"=>true, "description"=>t("A human readable name like for example: My Group."));
            $fields[] = array("type"=>"text", "value"=>$group_data["description"], "name"=>"description", "label"=>t("Description:"), "id"=>"description", "required"=>true, "description"=>t("A brief description of the group."));

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
