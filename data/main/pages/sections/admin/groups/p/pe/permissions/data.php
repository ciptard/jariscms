<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the user add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Group Permissions") ?>
    field;

    field: content
        <?php

            JarisCMS\Security\ProtectPage(array("view_groups", "edit_groups"));
            
            JarisCMS\System\AddTab(t("Groups"), "admin/groups");

            $permissions = JarisCMS\Group\GetPermissions($_REQUEST["group"]);

            if(isset($_REQUEST["btnSave"]))
            {
                //Save new permissions value
                $permissions_saved = true;
                foreach($permissions as $group=>$permissions_list)
                {
                    foreach($permissions_list as $machine_name=>$human_name)
                    {
                        if(!JarisCMS\Group\SetPermission($machine_name, $_REQUEST[$machine_name], $_REQUEST["group"]))
                        {
                            $permissions_saved = false;
                            break 2;
                        }
                    }
                }

                if($permissions_saved)
                {
                    JarisCMS\System\AddMessage(t("The changes have been successfully saved."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/groups/permissions", array("group"=>$_REQUEST["group"]));
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/groups");
            }

            $parameters["name"] = "group-permissions";
            $parameters["class"] = "group-permissions";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/groups/permissions");
            $parameters["method"] = "post";

            foreach($permissions as $group=>$permissions_list)
            {
                $fields = array();

                foreach($permissions_list as $machine_name=>$human_name)
                {
                    $fields[] = array("type"=>"checkbox", "checked"=>JarisCMS\Group\GetPermission($machine_name, $_REQUEST["group"]), "name"=>$machine_name, "label"=>$human_name, "id"=>$machine_name);
                }

                $fieldset[] = array("name"=>$group, "fields"=>$fields, "collapsible"=>true, "collapsed"=>true);
            }

            $fields_submit[] = array("type"=>"hidden", "name"=>"group", "value"=>$_REQUEST["group"]);
            $fields_submit[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
            $fields_submit[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

            $fieldset[] = array("fields"=>$fields_submit);

            print JarisCMS\Form\Generate($parameters, $fieldset);
        ?>
    field;
    
    field: is_system
        1
    field;
row;
