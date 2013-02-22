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
        <?php print t("Add Background"); ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("edit_settings"));
            
            if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("backgrounds-add"))
            {
                if($_FILES["image"]["type"] == "image/png" ||
                   $_FILES["image"]["type"] == "image/jpeg" ||
                   $_FILES["image"]["type"] == "image/pjpeg" ||
                   $_FILES["image"]["type"] == "image/gif"
                )
                {
                    $fields["description"] = $_REQUEST["description"];
                    $fields["image"] = JarisCMS\FileSystem\MoveFile($_FILES["image"]["tmp_name"], "files/backgrounds/" . $_FILES["image"]["name"]);
                    $fields["top"] = intval($_REQUEST["top"]);
                    $fields["position"] = $_REQUEST["position"];
                    $fields["mode"] = $_REQUEST["mode"];
                    $fields["attachment"] = $_REQUEST["attachment"];
                    $fields["background_color"] = $_REQUEST["background_color"];
                    $fields["display_rule"] = $_REQUEST["display_rule"];
                    $fields["pages"] = $_REQUEST["pages"];
                    
                    if($fields["image"])
                    {
                        chmod("files/backgrounds/" . $fields["image"], 0755);
                        
                        $backgrounds_settings = JarisCMS\Setting\GetAll("backgrounds");
                        $backgrounds = unserialize($backgrounds_settings["backgrounds"]);
                        
                        if(!is_array($backgrounds))
                        {
                            $backgrounds = array();
                        }
                        
                        $backgrounds[] = $fields;
                        
                        if(JarisCMS\Setting\Save("backgrounds", serialize($backgrounds), "backgrounds"))
                        {
                            JarisCMS\System\AddMessage(t("Background successfully added."));
                            JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/settings/backgrounds", "backgrounds"));
                        }
                        else
                        {
                            JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("write_error_data"), "error");
                        }
                    }
                    else
                    {
                        JarisCMS\System\AddMessage("The image could not be moved to files/backgrounds directory.", "error");
                    }
                }
                else 
                {
                    JarisCMS\System\AddMessage(JarisCMS\System\GetErrorMessage("image_file_type"), "error");
                }
                
                $fields = array(); //Uninitialize fields variable to not conflict with form generation below
            }
            elseif(isset($_REQUEST["btnCancel"])) 
            {
                JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/settings/backgrounds", "backgrounds"));
            }

            $parameters["name"] = "backgrounds-add";
            $parameters["class"] = "backgrounds-add";
            $parameters["enctype"] = "multipart/form-data";
            $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/backgrounds/add", "backgrounds"));
            $parameters["method"] = "post";
            
            $fields_main[] = array("type"=>"text", "label"=>t("Description:"), "value"=>$_REQUEST["description"], "name"=>"description", "id"=>"description", "description"=>t("Description of the background image like for example: January 2011 special product promotion."), "required"=>true);
            $fields_main[] = array("type"=>"file", "name"=>"image", "label"=>t("Background image file:"), "id"=>"image", "required"=>true);
            $fields_main[] = array("type"=>"text", "label"=>t("Top position:"), "value"=>$_REQUEST["top"], "name"=>"top", "id"=>"top", "description"=>t("The top position of the background in pixels, for example 200. Default is 0"));
            $fields_main[] = array("type"=>"color", "label"=>t("Background color:"), "value"=>$_REQUEST["background_color"]?$_REQUEST["background_color"]:"FFFFFF", "name"=>"background_color", "id"=>"background_color", "description"=>t("The overall background color of the body."));
            
            $fieldset[] = array("fields"=>$fields_main);
            
            $position[t("Left")] = "left";
            $position[t("Center")] = "center";
            $position[t("Right")] = "right";

            $position_fields[] = array("type"=>"radio", "name"=>"position", "id"=>"position", "value"=>$position, "checked"=>$_REQUEST["position"]?$_REQUEST["position"]:"center");
            
            $fieldset[] = array("name"=>t("Position"), "fields"=>$position_fields, "collapsible"=>true, "collapsed"=>true);
            
            
            $mode[t("Repeat")] = "repeat";
            $mode[t("No Repeat")] = "no-repeat";

            $mode_fields[] = array("type"=>"radio", "name"=>"mode", "id"=>"mode", "value"=>$mode, "checked"=>$_REQUEST["mode"]?$_REQUEST["mode"]:"no-repeat");

            $fieldset[] = array("name"=>t("Mode"), "fields"=>$mode_fields, "collapsible"=>true, "collapsed"=>true);
            
            
            $attachment[t("Scroll")] = "scroll";
            $attachment[t("Fixed")] = "fixed";

            $attachment_fields[] = array("type"=>"radio", "name"=>"attachment", "id"=>"attachment", "value"=>$attachment, "checked"=>$_REQUEST["attachment"]?$_REQUEST["attachment"]:"fixed");

            $fieldset[] = array("name"=>t("Attachment"), "fields"=>$attachment_fields, "collapsible"=>true, "collapsed"=>true);
            
            $display_rules[t("Display in all pages except the listed ones.")] = "all_except_listed";
            $display_rules[t("Just display on the listed pages.")] = "just_listed";
            
            $fields_pages[] = array("type"=>"radio", "checked"=>"all_except_listed", "name"=>"display_rule", "id"=>"display_rule", "value"=>$display_rules);
            $fields_pages[] = array("type"=>"textarea", "name"=>"pages", "label"=>t("Pages:"), "id"=>"pages", "value"=>$_REQUEST["pages"]);
            
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


