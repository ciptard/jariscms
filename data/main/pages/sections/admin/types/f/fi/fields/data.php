<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content types fields listing page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0

    field: title
        <?php print t("Content Type Fields") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("view_types_fields"));

            JarisCMS\System\AddTab(t("Add Field"), "admin/types/fields/add", array("type_name"=>$_REQUEST["type"]));
            JarisCMS\System\AddTab(t("Manage Types"), "admin/types");
            
        ?>
        
        <form class="categories" action="<?php print JarisCMS\URI\PrintURL("admin/types/fields"); ?>" method="post">
        <input type="hidden" name="type" value="<?php print $_REQUEST["type"] ?>" />
        
        <?php
            
            if(isset($_REQUEST["btnSave"]))
            {
                $saved = true;

                for($i=0; $i<count($_REQUEST["id"]); $i++)
                {
                    $new_field_data = JarisCMS\Field\GetTypeData($_REQUEST["id"][$i], $_REQUEST["type"]);
                    $new_field_data["position"] = $_REQUEST["position"][$i];

                    if(!JarisCMS\Field\EditType($_REQUEST["id"][$i], $new_field_data, $_REQUEST["type"]))
                    {
                        $saved = false;
                        break;
                    }
                }

                if($saved)
                {
                    JarisCMS\System\AddMessage(t("Your changes have been saved."));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }

                JarisCMS\System\GoToPage("admin/types/fields", array("type"=>$_REQUEST["type"]));
            }

            $fields_array = JarisCMS\Field\GetFieldsFromType($_REQUEST["type"]);
            
            if(!$fields_array)
            {
                print "<h3>" . t("No fields available click on Add Field to create one.") . "</h3>";
            }
            else
            {
                print "<table class=\"types-list\">\n";
    
                print "<thead><tr>\n";
    
                print "<td>" . t("Name") . "</td>\n";
                print "<td>" . t("Description") . "</td>\n";
                print "<td>" . t("Order") . "</td>\n";
                print "<td>" . t("Operation") . "</td>\n";
    
                print  "</tr></thead>\n";
    
                foreach($fields_array as $id=>$fields)
                {
                    print "<tr>\n";
    
                    print "<td>" . t($fields["name"]) . "</td>\n";
                    print "<td>" . t($fields["description"]) . "</td>\n";
                    
                    print "<td>" . 
                    "<input type=\"hidden\" name=\"id[]\" value=\"$id\" />" .
                    "<input type=\"text\" style=\"width: 30px;\" name=\"position[]\" value=\"{$fields['position']}\" />" .
                    "</td>\n";
    
                    $edit_url = JarisCMS\URI\PrintURL("admin/types/fields/edit",array("id"=>$id, "type_name"=>$_REQUEST["type"]));
                    $delete_url = JarisCMS\URI\PrintURL("admin/types/fields/delete", array("id"=>$id, "type_name"=>$_REQUEST["type"]));
                    
                    $edit_text = t("Edit");
                    $delete_text = t("Delete");
    
                    print "<td>
                            <a href=\"$edit_url\">$edit_text</a>&nbsp;
                            <a href=\"$delete_url\">$delete_text</a>
                           </td>\n";
    
                    print "</tr>\n";
                }
                
                print "</table>\n";
            }
        ?>
        
        <div>
        <br />
        <input class="form-submit" type="submit" name="btnSave" value="<?php print t("Save") ?>" />
        &nbsp;
        <input class="form-submit" type="submit" name="btnCancel" value="<?php print t("Cancel") ?>" />
        </div>
        </form>
    field;

    field: is_system
        1
    field;
row;
