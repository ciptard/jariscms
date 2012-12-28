<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the blocks configurations page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0

    field: title
        <?php print t("Blocks") ?>
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
            JarisCMS\Security\ProtectPage(array("view_blocks"));
            
            JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.js");
            JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.touch-punch.min.js");

            global $base_url, $clean_url;

            JarisCMS\System\AddTab(t("Create Block"), "admin/blocks/add");
        ?>

        <form class="blocks" action="<?php print JarisCMS\URI\PrintURL("admin/blocks"); ?>" method="post">

        <?php
            if(isset($_REQUEST["btnSave"]))
            {
                $saved = true;

                for($i=0; $i<count($_REQUEST["id"]); $i++)
                {
                    $new_block_data = JarisCMS\Block\GetData($_REQUEST["id"][$i], $_REQUEST["previous_position"][$i]);
                    $new_block_data["order"] = $i;

                    if(!JarisCMS\Block\Edit($_REQUEST["id"][$i], $_REQUEST["previous_position"][$i], $new_block_data))
                    {
                        $saved = false;
                        break;
                    }

                    if($_REQUEST["previous_position"][$i] != $_REQUEST["position"][$i])
                    {
                        JarisCMS\Block\Move($_REQUEST["id"][$i], $_REQUEST["previous_position"][$i], $_REQUEST["position"][$i]);
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

                JarisCMS\System\GoToPage("admin/blocks");
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

                $blocks_list = JarisCMS\PHPDB\Sort(JarisCMS\Block\GetList($block_name), "order");
                if(count($blocks_list) > 0)
                {
                    print "<tbody>\n";
                    print "<tr class=\"head\">\n";

                    print "<td>" . t("Description") . "</td>\n";
                    print "<td>" . t("Position") . "</td>\n";
                    print "<td>" . t("Order") . "</td>\n";
                    print "<td>" . t("Operation") . "</td>\n";

                    print  "</tr>\n";
                    print "</tbody>\n";
                    
                    print "<tbody class=\"$block_name blocks\">\n";

                    foreach($blocks_list as $id => $fields)
                    {
                        print "<tr>\n";
                        
                        print "<td>\n
                        <a class=\"sort-handle\"></a>\n
                        <input type=\"hidden\" name=\"previous_position[]\" value=\"$block_name\" />\n
                        <input type=\"hidden\" name=\"id[]\" value=\"$id\" />\n
                        <input size=\"3\" class=\"form-text\" type=\"hidden\" name=\"order[]\" value=\"" . $fields["order"] . "\" />\n
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

                        $url_arguments["id"] = $id;
                        $url_arguments["position"] = $block_name;

                        print "<td>
                        <a href=\"" . JarisCMS\URI\PrintURL("admin/blocks/edit", $url_arguments) . "\">" . t("Edit") . "</a>
                        ";
                        
                        if(!$fields["is_system"])
                        {
                            print "&nbsp;
                            <a href=\"" . JarisCMS\URI\PrintURL("admin/blocks/delete", $url_arguments) . "\">" . t("Delete") . "</a>";
                        }
                        
                        print "</td>";

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
