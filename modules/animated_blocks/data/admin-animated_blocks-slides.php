<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the languages management section.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Block Slides") ?>
    field;

    field: content
        <?php
            JarisCMS\Security\ProtectPage(array("edit_blocks"));
        ?>
        
        <style>
        .slides-list thead td
        {
            font-weight: bold;
        }
        
        .slides-list td
        {
            padding: 5px;
            border-bottom: dashed 1px #d3d3d3;
        }
        </style>
        
        <?php
            
            $block_data = JarisCMS\Block\GetData($_REQUEST["id"], $_REQUEST["position"]);
            $block_data["content"] = unserialize($block_data["content"]);
            
            JarisCMS\System\AddTab(t("Edit"), JarisCMS\Module\GetPageURI("admin/animated-blocks/edit", "animated_blocks"), array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
            JarisCMS\System\AddTab(t("Settings"), JarisCMS\Module\GetPageURI("admin/animated-blocks/settings", "animated_blocks"), array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
            JarisCMS\System\AddTab(t("Slides"), JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"), array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
            JarisCMS\System\AddTab(t("Delete"), "admin/blocks/delete", array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
            JarisCMS\System\AddTab(t("Blocks"), "admin/blocks");
            
            if($_REQUEST["action"] == "add")
            {
                if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("add-slide"))
                {
                    $block_data["content"][] = array(
                        "uri"=>$_REQUEST["slide_uri"],
                        "title"=>$_REQUEST["title"],
                        "description"=>$_REQUEST["description"],
                        "order"=> "0"
                    );
                    
                    $block_data["content"] = serialize($block_data["content"]);
                    
                    JarisCMS\Block\Edit($_REQUEST["id"], $_REQUEST["position"], $block_data);
                    
                    JarisCMS\System\AddMessage(t("Slide added."));
                    
                    JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"), array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
                }
                else
                {
                    JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"), array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
                }
            }
            if($_REQUEST["action"] == "edit")
            {
                if(isset($_REQUEST["btnSave"]) && !JarisCMS\Form\CheckFields("edit-slide"))
                {
                    $block_data["content"][$_REQUEST["slide_id"]] = array(
                        "uri"=>$_REQUEST["slide_uri"],
                        "title"=>$_REQUEST["title"],
                        "description"=>$_REQUEST["description"],
                        "order"=>$_REQUEST["order"]
                    );
                    
                    $block_data["content"] = serialize($block_data["content"]);
                    
                    JarisCMS\Block\Edit($_REQUEST["id"], $_REQUEST["position"], $block_data);
                    
                    JarisCMS\System\AddMessage(t("Slide edited."));
                    
                    JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"), array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
                }
                else
                {
                    JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"), array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
                }
            }
            else if($_REQUEST["action"] == "delete")
            {
                if(isset($_REQUEST["btnYes"]))
                {
                    unset($block_data["content"][$_REQUEST["slide_id"]]);
                    
                    $block_data["content"] = serialize($block_data["content"]);
                    
                    JarisCMS\Block\Edit($_REQUEST["id"], $_REQUEST["position"], $block_data);
                    
                    JarisCMS\System\AddMessage(t("Slide removed."));
                    
                    JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"), array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
                }
                else
                {
                    JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"), array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
                }                
            }
            else if($_REQUEST["action"] == "update")
            {
                if(isset($_REQUEST["btnSave"]))
                {
                    foreach($_REQUEST["slide"] as $index=>$slide)
                    {
                        $block_data["content"][$slide]["uri"] = $_REQUEST["slide_uri"][$index];
                        $block_data["content"][$slide]["order"] = $_REQUEST["order"][$index];
                    }
                    
                    $block_data["content"] = serialize($block_data["content"]);
                    
                    JarisCMS\Block\Edit($_REQUEST["id"], $_REQUEST["position"], $block_data);
                    
                    JarisCMS\System\AddMessage(t("Slide details updated."));
                    
                    JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"), array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
                }
                else
                {
                    JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"), array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
                }
            }
            
            if($_REQUEST["view"] == "add")
            {
                $parameters["name"] = "add-slide";
                $parameters["class"] = "add-slide";
                $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"));
                $parameters["method"] = "post";

                $fields[] = array("type"=>"hidden", "name"=>"action", "value"=>"add");
                $fields[] = array("type"=>"hidden", "name"=>"id", "value"=>$_REQUEST["id"]);
                $fields[] = array("type"=>"hidden", "name"=>"position", "value"=>$_REQUEST["position"]);
                $fields[] = array("type"=>"text", "name"=>"slide_uri", "label"=>t("Uri:"), "id"=>"slide_uri", "required"=>true, "description"=>t("The uri of the slide, image or content."));
                $fields[] = array("type"=>"text", "name"=>"title", "label"=>t("Title:"), "id"=>"title", "description"=>t("Optional title for the slide."));
                $fields[] = array("type"=>"textarea", "name"=>"description", "label"=>t("Description:"), "id"=>"slide_description", "description"=>t("Optional description for the slide."));

                $fields[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
                $fields[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

                $fieldset[] = array("fields"=>$fields);

                print JarisCMS\Form\Generate($parameters, $fieldset);
            }
            else if($_REQUEST["view"] == "edit")
            {
                $parameters["name"] = "edit-slide";
                $parameters["class"] = "edit-slide";
                $parameters["action"] = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"));
                $parameters["method"] = "post";

                $fields[] = array("type"=>"hidden", "name"=>"action", "value"=>"edit");
                $fields[] = array("type"=>"hidden", "name"=>"id", "value"=>$_REQUEST["id"]);
                $fields[] = array("type"=>"hidden", "name"=>"position", "value"=>$_REQUEST["position"]);
                $fields[] = array("type"=>"hidden", "name"=>"slide_id", "value"=>$_REQUEST["slide_id"]);
                $fields[] = array("type"=>"text", "name"=>"slide_uri", "label"=>t("Uri:"), "id"=>"slide_uri", "required"=>true, "value"=>$block_data["content"][$_REQUEST["slide_id"]]["uri"], "description"=>t("The uri of the slide, image or content."));
                $fields[] = array("type"=>"text", "name"=>"title", "label"=>t("Title:"), "id"=>"title", "value"=>$block_data["content"][$_REQUEST["slide_id"]]["title"], "description"=>t("Optional title for the slide."));
                $fields[] = array("type"=>"textarea", "name"=>"description", "label"=>t("Description:"), "value"=>$block_data["content"][$_REQUEST["slide_id"]]["description"], "id"=>"slide_description", "description"=>t("Optional description for the slide."));
                $fields[] = array("type"=>"text", "name"=>"order", "label"=>t("Order:"), "id"=>"order", "value"=>$block_data["content"][$_REQUEST["slide_id"]]["order"], "description"=>t("Numerical value to indicate the order in which the slide is displayed."));

                $fields[] = array("type"=>"submit", "name"=>"btnSave", "value"=>t("Save"));
                $fields[] = array("type"=>"submit", "name"=>"btnCancel", "value"=>t("Cancel"));

                $fieldset[] = array("fields"=>$fields);

                print JarisCMS\Form\Generate($parameters, $fieldset);
            }
            else if($_REQUEST["view"] == "delete")
            {
                print "<form class=\"group-delete\" method=\"post\" action=\"" . JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks")) . "\">
                <input type=\"hidden\" name=\"id\" value=\"" . $_REQUEST["id"] . "\" />
                <input type=\"hidden\" name=\"position\" value=\"" . $_REQUEST["position"] . "\" />
                <input type=\"hidden\" name=\"slide_id\" value=\"" . $_REQUEST["slide_id"] . "\" />
                <input type=\"hidden\" name=\"action\" value=\"delete\" />
                <br />
                <div>" . t("Are you sure you want to delete the slide?") . "
                <div><b>" . t("Uri:") . "</b> " . $block_data["content"][$_REQUEST["slide_id"]]["uri"] . "</div>
                </div>
                <input class=\"form-submit\" type=\"submit\" name=\"btnYes\" value=\"" . t("Yes") . "\" />
                <input class=\"form-submit\" type=\"submit\" name=\"btnNo\" value=\"" . t("No") . "\" />
                </form>";
            }
            else
            {
                JarisCMS\System\AddTab(t("Add Slide"), JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"), array("view"=>"add", "id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]), 1);
                
                if(is_array($block_data["content"]) && count($block_data["content"]) > 0)
                {
                    $block_data["content"] = JarisCMS\PHPDB\Sort($block_data["content"], "order");
                    
                    print "<form class=\"slides\" method=\"post\" action=\"" . JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"), array("action"=>"update", "id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"])) . "\" >";
                    print "<input type=\"hidden\" name=\"id\" value=\"" . $_REQUEST["id"] . "\" />";
                    print "<input type=\"hidden\" name=\"position\" value=\"" . $_REQUEST["position"] . "\" />";
                    print "<table class=\"slides-list\">\n";

                    print "<thead><tr>\n";

                    print "<td>" . t("Uri") . "</td>\n";
                    print "<td>" . t("Order") . "</td>\n";
                    print "<td>" . t("Operation") . "</td>\n";

                    print  "</tr></thead>\n";

                    foreach($block_data["content"] as $id=>$fields)
                    {
                        print "<tr>\n";

                        print "<td><input type=\"text\" name=\"slide_uri[]\" value=\"{$fields['uri']}\" /></td>\n";
                        
                        print "<td>" . "
                        <input type=\"hidden\" name=\"slide[]\" value=\"$id\" />
                        <input type=\"text\" name=\"order[]\" value=\"{$fields['order']}\" />" . 
                        "</td>\n";
                        
                        $edit_url = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"), array("view"=>"edit", "id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"], "slide_id"=>$id));
                        $edit_text = t("Edit");

                        $delete_url = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/animated-blocks/slides", "animated_blocks"), array("view"=>"delete", "id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"], "slide_id"=>$id));
                        $delete_text = t("Delete");

                        print "<td>
                                <a href=\"$edit_url\">$edit_text</a> <br />
                                <a href=\"$delete_url\">$delete_text</a>
                               </td>\n";

                        print "</tr>\n";
                    }

                    print "</table>\n";
                    
                    print "<input type=\"submit\" name=\"btnSave\" value=\"" . t("Save") . "\" /> &nbsp";
                    print "<input type=\"submit\" name=\"btnCancel\" value=\"" . t("Cancel") . "\" />";
                    print "</form>";
                }
                else
                {
                    print t("No slides available");
                }
            }
        ?>
    field;

    field: is_system
        1
    field;
row;
