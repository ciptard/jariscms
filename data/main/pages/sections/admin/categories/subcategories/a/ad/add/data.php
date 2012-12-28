<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the subcategories add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Add Subcategory") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_subcategories", "add_subcategories"));

            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("add-subcategory"))
            {
                $fields["title"] = $_REQUEST["title"];
                $fields["description"] = $_REQUEST["description"];
                $fields["parent"] = $_REQUEST["parent"];
                $fields["order"] = 0;

                if(JarisCMS\Category\AddChild($_REQUEST["category"], $fields))
                {
                    JarisCMS\System\AddMessage(t("The subcategory was successfully created."));
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

            $parameters["name"] = "add-subcategory";
            $parameters["class"] = "add-subcategory";
            $parameters["action"] = JarisCMS\URI\PrintURL("admin/categories/subcategories/add");
            $parameters["method"] = "post";

            $fields[] = array("type"=>"hidden", "name"=>"category", "value"=>$_REQUEST["category"]);
            $fields[] = array("type"=>"text", "name"=>"title", "value"=>$_REQUEST["title"], "label"=>t("Title:"), "id"=>"title", "required"=>true);
            $fields[] = array("type"=>"text", "name"=>"description", "value"=>$_REQUEST["description"], "label"=>t("Description:"), "id"=>"description");
            
            $subcategories["&lt;root&gt;"] = "root";

            $subcategories_array = JarisCMS\Category\GetChildrenList($_REQUEST["category"]);
            if($subcategories_array)
            {
                foreach($subcategories_array as $id=>$items)
                {
                    $subcategories[$items["title"]] = "$id";
                }
            }
            
            $fields[] = array("type"=>"select", "name"=>"parent", "selected"=>"root", "label"=>t("Parent:"), "id"=>"parent", "value"=>$subcategories);

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
