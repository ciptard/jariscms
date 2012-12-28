<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the edit subcategory page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Subcategory") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_subcategories", "edit_subcategories"));

            $current_subcategory_data = JarisCMS\Category\GetChildData($_REQUEST["category"], $_REQUEST["id"]);

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("edit-subcategories"))
            {
                $fields = $current_subcategory_data;
                
                $fields["title"] = $_REQUEST["title"];
                $fields["description"] = $_REQUEST["description"];
                $fields["order"] = $current_subcategory_data["order"];
                
                //Checks if client is trying to move a root parent subcategory to its own subcategory and makes subs category root
                if($fields["parent"] == "root" && $_REQUEST["parent"] != "root")
                {
                    $new_parent_subcategory = JarisCMS\Category\GetChildData($_REQUEST["category"], $_REQUEST["id"]);
                    
                    if("" . $new_parent_subcategory["parent"] . "" == "" . $_REQUEST["id"] . "")
                    {
                        $new_parent_subcategory["parent"] = "root";
                        JarisCMS\Category\EditChild($_REQUEST["category"], $new_parent_subcategory, $_REQUEST["id"]);
                    }
                }
                
                $fields["parent"] = $_REQUEST["parent"];

                if(JarisCMS\Category\EditChild($_REQUEST["category"], $fields, $_REQUEST["id"]))
                {
                    JarisCMS\System\AddMessage(t("The subcategory was successfully edited."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/categories/subcategories", array("category"=>$_REQUEST["category"]));
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/categories/subcategories", array("category"=>$_REQUEST["category"]));
            }

            $subcategories["&lt;root&gt;"] = "root";

            $subcategories_array = JarisCMS\Category\GetChildrenList($_REQUEST["category"]);

            foreach($subcategories_array as $id=>$items)
            {
                if($id != $_REQUEST["id"])
                {
                    $subcategories[$items["title"]] = "$id";
                }
            }

            $parameters["name"] = "edit-subcategories";
            $parameters["class"] = "edit-subcategories";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/categories/subcategories/edit");
            $parameters["method"] = "post";

            $fields[] = array("type"=>"hidden", "name"=>"id", "value"=>$_REQUEST["id"]);
            $fields[] = array("type"=>"hidden", "name"=>"category", "value"=>$_REQUEST["category"]);
            $fields[] = array("type"=>"text", "name"=>"title", "label"=>t("Title:"), "id"=>"title", "value"=>$current_subcategory_data["title"], "required"=>true);
            $fields[] = array("type"=>"text", "name"=>"description", "label"=>t("Description:"), "id"=>"description", "value"=>$current_subcategory_data["description"]);
            $fields[] = array("type"=>"select", "name"=>"parent", "selected"=>trim($current_subcategory_data["parent"]), "label"=>t("Parent:"), "id"=>"parent", "value"=>$subcategories);
            
            $fieldset[] = array("fields"=>$fields);

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
