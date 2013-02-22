<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Edit Multiple Background"); ?>
    field;

    field: content
        <script>
            $(document).ready(function(){
                var fixHelper = function(e, ui) {
                    ui.children().each(function() {
                        $(this).width($(this).width());
                    });
                    return ui;
                };
            
                $(".navigation-list tbody").sortable({ 
                    cursor: 'crosshair', 
                    helper: fixHelper,
                    handle: "a.sort-handle"
                });
                
                $(".navigation-list tbody tr td a.delete").click(function(){
                    $(this).parent().parent().fadeOut(1000, function(){
                        $(this).remove();
                    });
                });
            });
        </script>
        
        <style>
            .navigation-list tbody tr:hover
            {
                background-color: #d3d3d3;
            }
        </style>    
        <?php
            JarisCMS\Security\ProtectPage(array("edit_settings"));
            
            JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.js");
            JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.touch-punch.min.js");
            
            $backgrounds_settings = JarisCMS\Setting\GetAll("backgrounds");
            $backgrounds = unserialize($backgrounds_settings["backgrounds"]);
            
            $background = $backgrounds[intval($_REQUEST["id"])];
            $background["images"] = unserialize($background["images"]);
            
            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("backgrounds-multi-edit"))
            {
                $fields = $background;
                
                //Delete removed images
                foreach($fields["images"] as $image)
                {
                    if(!in_array($image, $_REQUEST["images_list"]))
                    {
                        unlink("files/backgrounds/".$image);
                    }
                }
                
                $fields["images"] = $_REQUEST["images_list"];
                
                //Add new images
                if(is_array($_FILES["images"]["name"]))
                {
                    foreach($_FILES["images"]["name"] as $file_index=>$file_name)
                    {
                        if($_FILES["images"]["type"][$file_index] == "image/png" ||
                            $_FILES["images"]["type"][$file_index] == "image/jpeg" ||
                            $_FILES["images"]["type"][$file_index] == "image/pjpeg" ||
                            $_FILES["images"]["type"][$file_index] == "image/gif"
                         )
                            $fields["images"][] = JarisCMS\FileSystem\MoveFile($_FILES["images"]["tmp_name"][$file_index], "files/backgrounds/" . $file_name);
                    }
                }

                $fields["multi"] = true;
                $fields["description"] = $_REQUEST["description"];
                $fields["fade_speed"] = intval($_REQUEST["fade_speed"]);
                $fields["rotation_speed"] = intval($_REQUEST["rotation_speed"]);
                $fields["images"] = is_array($fields["images"]) ? serialize($fields["images"]) : false;
                $fields["stretch"] = intval($_REQUEST["stretch"]);
                $fields["centerx"] = intval($_REQUEST["centerx"]);
                $fields["centery"] = intval($_REQUEST["centery"]);
                $fields["display_rule"] = $_REQUEST["display_rule"];
                $fields["pages"] = $_REQUEST["pages"];

                $backgrounds[$_REQUEST["id"]] = $fields;

                if(JarisCMS\Setting\Save("backgrounds", serialize($backgrounds), "backgrounds"))
                {
                    JarisCMS\System\AddMessage(t("Changes successfully saved."));
                    JarisCMS\System\GoToPage(JarisCMS\Module\GetPageUri("admin/settings/backgrounds/multi/edit", "backgrounds"), array("id"=>$_REQUEST["id"]));
                }
                else
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                }
                
                $fields = array(); //Uninitialize fields variable to not conflict with form generation below
            }
            elseif(isset($_REQUEST["btnCancel"])) 
            {
                JarisCMS\System\GoToPage(JarisCMS\Module\GetPageUri("admin/settings/backgrounds", "backgrounds"));
            }

            $parameters["name"] = "backgrounds-multi-edit";
            $parameters["class"] = "backgrounds-multi-edit";
            $parameters["enctype"] = "multipart/form-data";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageUri("admin/settings/backgrounds/multi/edit", "backgrounds"));
            $parameters["method"] = "post";
            
            $fields_main[] = array("type"=>"hidden", "name"=>"id", "value"=>$_REQUEST["id"]);
            $fields_main[] = array("type"=>"text", "label"=>t("Description:"), "value"=>$_REQUEST["description"]?$_REQUEST["description"]:$background["description"], "name"=>"description", "id"=>"description", "description"=>t("Description of the background image like for example: January 2011 special product promotion."), "required"=>true);
            
            $images = "<table class=\"navigation-list\">";
            $images .= "<thead>";
            $images .= "<tr>";
            $images .= "<td>".t("Order")."</td>";
            $images .= "<td>".t("Image")."</td>";
            $images .= "<td>".t("Action")."</td>";
            $images .= "</tr>";
            $images .= "</thead>";
            
            $images .= "<tbody>";
            if(is_array($background["images"]))
            {
                foreach($background["images"] as $image)
                {
                    $images .= "<tr>";
                    
                    $images .= "<td><a class=\"sort-handle\"></a></td>";
                    
                    $images .= "<td>
                        <input type=\"hidden\" name=\"images_list[]\" value=\"$image\"  />
                        <img width=\"150px\" src=\"".JarisCMS\URI\PrintURL("files/backgrounds/".$image)."\" />
                    </td>";
                    
                    $images .= "<td><a class=\"delete\" style=\"cursor: pointer\">".t("Delete")."</a></td>";
                    
                    $images .= "</tr>";
                }
            }
            $images .= "</tbody>";
            
            $images .= "</table>";
            
            $fields_main[] = array("type"=>"file", "name"=>"images", "multiple"=>true, "valid_types"=>"gif,jpg,jpeg,png", "label"=>t("Background images:"), "id"=>"images");
            $fields_main[] = array("type"=>"other", "html_code"=>"<div style=\"margin-top: 10px;\"><strong>".t("Current images:")."</strong><hr />$images</div>");
            
            $fields_main[] = array("type"=>"text", "name"=>"fade_speed", "value"=>$_REQUEST["fade_speed"]?$_REQUEST["fade_speed"]:$background["fade_speed"], "label"=>t("Fade speed:"), "id"=>"fade_speed", "required"=>true, "description"=>t("The speed of the fade effect in milliseconds."));
            $fields_main[] = array("type"=>"text", "name"=>"rotation_speed", "value"=>$_REQUEST["rotation_speed"]?$_REQUEST["rotation_speed"]:$background["rotation_speed"], "label"=>t("Rotation speed:"), "id"=>"rotation_speed", "required"=>true, "description"=>t("The time in milliseconds an image is displayed before changing to the next one."));
            
            $fieldset[] = array("fields"=>$fields_main);
            
            $stretch[t("No")] = 0;
            $stretch[t("Yes")] = 1;

            $stretch_fields[] = array("type"=>"radio", "name"=>"stretch", "id"=>"stretch", "value"=>$stretch, "checked"=>$_REQUEST["stretch"]?$_REQUEST["stretch"]:$background["stretch"]);
            
            $fieldset[] = array("name"=>t("Stretch Image"), "fields"=>$stretch_fields, "collapsible"=>true, "collapsed"=>true);
            
            $center_horizontally[t("No")] = 0;
            $center_horizontally[t("Yes")] = 1;

            $center_horizontally_fields[] = array("type"=>"radio", "name"=>"centerx", "id"=>"centerx", "value"=>$center_horizontally, "checked"=>$_REQUEST["centerx"]?$_REQUEST["centerx"]:$background["centerx"]);
            
            $fieldset[] = array("name"=>t("Center Image Horizontally"), "fields"=>$center_horizontally_fields, "collapsible"=>true, "collapsed"=>true);
            
            $center_vertically[t("No")] = 0;
            $center_vertically[t("Yes")] = 1;

            $center_vertically_fields[] = array("type"=>"radio", "name"=>"centery", "id"=>"centery", "value"=>$center_vertically, "checked"=>$_REQUEST["centery"]?$_REQUEST["centery"]:$background["centery"]);
            
            $fieldset[] = array("name"=>t("Center Image Vertically"), "fields"=>$center_vertically_fields, "collapsible"=>true, "collapsed"=>true);            
            
            $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
            $display_rules[t("Just display on the listed pages.")] = "just_listed";
            
            $display_rule = isset($_REQUEST["display_rule"]) ? $_REQUEST["display_rule"] : $background["display_rule"];
            
            $fields_pages[] = array("type"=>"radio", "checked"=>$display_rule, "name"=>"display_rule", "id"=>"display_rule", "value"=>$display_rules);
            $fields_pages[] = array("type"=>"uriarea", "name"=>"pages", "label"=>t("Pages:"), "id"=>"pages", "value"=>$_REQUEST["pages"]?$_REQUEST["pages"]:$background["pages"]);
            
            $fieldset[] = array("fields"=>$fields_pages, "name"=>t("Pages to display"), "description"=>t("List of uri's seperated by comma (,). Also supports the wildcard (*), for example: my-section/*"));

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


