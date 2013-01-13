<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content blocks page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0

    field: title
        <?php print t("Page Blocks") ?>
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
            
                $(".blocks-list tbody.header").sortable({ 
                    cursor: 'crosshair', 
                    helper: fixHelper,
                    handle: "a.sort-handle"
                });
                
                $(".blocks-list tbody.left").sortable({ 
                    cursor: 'crosshair', 
                    helper: fixHelper,
                    handle: "a.sort-handle"
                });
                
                $(".blocks-list tbody.right").sortable({ 
                    cursor: 'crosshair', 
                    helper: fixHelper,
                    handle: "a.sort-handle"
                });
                
                $(".blocks-list tbody.center").sortable({ 
                    cursor: 'crosshair', 
                    helper: fixHelper,
                    handle: "a.sort-handle"
                });
                
                $(".blocks-list tbody.footer").sortable({ 
                    cursor: 'crosshair', 
                    helper: fixHelper,
                    handle: "a.sort-handle"
                });
                
                $(".blocks-list tbody.none").sortable({ 
                    cursor: 'crosshair', 
                    helper: fixHelper,
                    handle: "a.sort-handle"
                });
            });
        </script>
        
        <?php
            JarisCMS\Security\ProtectPage(array("view_content_blocks"));
            
            if(!JarisCMS\Page\IsOwner($_REQUEST["uri"]))
            {
                JarisCMS\Security\ProtectPage();
            }
            
            JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.js");
            JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.touch-punch.min.js");

            global $base_url;

            $page_uri = $_REQUEST["uri"];
            $arguments["uri"] = $page_uri;

            //Tabs
            if(JarisCMS\Group\GetPermission("edit_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Edit"), "admin/pages/edit", $arguments);
            }
            JarisCMS\System\AddTab(t("View"), $_REQUEST["uri"]);
            if(JarisCMS\Group\GetPermission("view_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Blocks"), "admin/pages/blocks", $arguments);
            }
            if(JarisCMS\Group\GetPermission("view_images", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Images"), "admin/pages/images", $arguments);
            }
            if(JarisCMS\Group\GetPermission("view_files", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Files"), "admin/pages/files", $arguments);
            }
            if(JarisCMS\Group\GetPermission("translate_languages", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Translate"), "admin/pages/translate", $arguments);
            }
            if(JarisCMS\Group\GetPermission("delete_content", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Delete"), "admin/pages/delete", $arguments);
            }
            
            if(JarisCMS\Group\GetPermission("add_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Create Block"), "admin/pages/blocks/add", $arguments, 1);
            }
            if(JarisCMS\Group\GetPermission("add_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Create Post Block"), "admin/pages/blocks/add/page", $arguments, 1);
            }
            if(JarisCMS\Group\GetPermission("edit_post_settings_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
            {
                JarisCMS\System\AddTab(t("Post Settings"), "admin/pages/blocks/post/settings", $arguments, 1);
            }

        ?>

        <form class="blocks" action="<?php print JarisCMS\URI\PrintURL("admin/pages/blocks"); ?>" method="post">
        <input type="hidden" name="uri" value="<?php print $_REQUEST["uri"] ?>" />
        <?php
            if(isset($_REQUEST["btnSave"]) && JarisCMS\Group\GetPermission("edit_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $saved = true;
                for($i=0; $i<count($_REQUEST["id"]); $i++)
                {
                    $new_block_data = JarisCMS\Block\GetData($_REQUEST["id"][$i], $_REQUEST["previous_position"][$i], $page_uri);
                    $new_block_data["order"] = $i;

                    if(!JarisCMS\Block\Edit($_REQUEST["id"][$i], $_REQUEST["previous_position"][$i], $new_block_data, $page_uri))
                    {
                        $saved = false;
                        break;
                    }

                    if($_REQUEST["previous_position"][$i] != $_REQUEST["position"][$i])
                    {
                        JarisCMS\Block\Move($_REQUEST["id"][$i], $_REQUEST["previous_position"][$i], $_REQUEST["position"][$i], $page_uri);
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

                JarisCMS\System\GoToPage("admin/pages/blocks", $arguments);
            }

            $block_positions[t("Header")] = "header";
            $block_positions[t("Left")] = "left";
            $block_positions[t("Right")] = "right";
            $block_positions[t("Center")] = "center";
            $block_positions[t("Footer")] = "footer";
            $block_positions[t("None")] = "none";
            
            print "<table class=\"blocks-list\">\n";
            
            print "<thead><tr>\n";

            print "<td></td>\n";
            print "<td></td>\n";
            print "<td></td>\n";
            print "<td></td>\n";

            print  "</tr></thead>\n";

            foreach($block_positions as $block_caption=>$block_name)
            {
                print "<tbody><tr><td colspan=\"4\"><h3>" . t($block_caption) . "</h3></td></tr></tbody>\n";

                $blocks_list =  JarisCMS\PHPDB\Sort(JarisCMS\Block\GetList($block_name, $page_uri), "order");

                if(count($blocks_list) > 0)
                {
                    print "<tbody><tr class=\"head\">\n";

                    print "<td>" . t("Order") . "</td>\n";
                    print "<td>" . t("Description") . "</td>\n";
                    print "<td>" . t("Position") . "</td>\n";
                    if(JarisCMS\Group\GetPermission("edit_content_blocks", JarisCMS\Security\GetCurrentUserGroup()) || 
                       JarisCMS\Group\GetPermission("delete_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
                    {
                       print "<td>" . t("Operation") . "</td>\n";
                    }

                    print  "</tr></tbody>\n";

                    print "<tbody class=\"$block_name blocks\">\n";

                    foreach($blocks_list as $id => $fields)
                    {
                        print "<tr>\n";
                        
                        print "<td>\n
                        <a class=\"sort-handle\"></a>\n
                        <input type=\"hidden\" name=\"previous_position[]\" value=\"$block_name\" />\n
                        <input type=\"hidden\" name=\"id[]\" value=\"$id\" />\n
                        <input type=\"hidden\" name=\"order[]\" />\n
                        </td>\n";

                        print "<td>" . t($fields["description"]) . "</td>\n";
                        
                        print "<td>\n" .
                        "<select name=\"position[]\">\n";
                        foreach($block_positions as $caption=>$position)
                        {
                            $selected = $block_name==$position?" selected":"";
                            print "<option $selected value=\"$position\">" . $caption . "</option>\n";
                        }
                        print "</select></td>";

                        $url_arguments["uri"] = $page_uri;
                        $url_arguments["id"] = $id;
                        $url_arguments["position"] = $block_name;

                        if(JarisCMS\Group\GetPermission("edit_content_blocks", JarisCMS\Security\GetCurrentUserGroup()) || 
                           JarisCMS\Group\GetPermission("delete_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
                        {
                            print "<td>";
                            if(JarisCMS\Group\GetPermission("edit_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
                            {
                                print "<a href=\"" . JarisCMS\URI\PrintURL("admin/pages/blocks/edit", $url_arguments) . "\">" . t("Edit") . "</a>
                                &nbsp;";
                            }
                            
                            if(JarisCMS\Group\GetPermission("delete_content_blocks", JarisCMS\Security\GetCurrentUserGroup()))
                            {
                                print "<a href=\"" . JarisCMS\URI\PrintURL("admin/pages/blocks/delete", $url_arguments) . "\">" . t("Delete") . "</a>";
                            }
                            print "</td>";
                        }

                        print "</tr>\n";
                    }

                    print "</tbody>\n";
                }
                else
                {
                    print "<tbody><tr><td colspan=\"4\">" . t("No block available.") . "</td></tr></tbody>\n";
                }
            }
            
            print "</table>\n";
        ?>
        
        <?php if(JarisCMS\Group\GetPermission("edit_content_blocks", JarisCMS\Security\GetCurrentUserGroup())) { ?>
        <div>
        <br />
        <input class="form-submit" type="submit" name="btnSave" value="<?php print t("Save") ?>" />
        &nbsp;
        <input class="form-submit" type="submit" name="btnCancel" value="<?php print t("Cancel") ?>" />
        </div>
        <?php } ?>
        
        </form>
    field;

    field: is_system
        1
    field;
row;
