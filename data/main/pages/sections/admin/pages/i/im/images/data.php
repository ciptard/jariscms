<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content images management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Images") ?>
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
            
                $(".images-list tbody").sortable({ 
                    cursor: 'crosshair', 
                    helper: fixHelper,
                    handle: "a.sort-handle"
                });
            });
        </script>
        
        <?php
            JarisCMS\Security\ProtectPage(array("view_images"));
            
            if(!JarisCMS\Page\IsOwner($_REQUEST["uri"]))
            {
                JarisCMS\Security\ProtectPage();
            }
            
            JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.js");
            JarisCMS\System\AddScript("scripts/jquery-ui/jquery.ui.touch-punch.min.js");
            
            //Check maximum permitted image upload have not exceed
            $type_settings = JarisCMS\Type\GetData(JarisCMS\Page\GetType($_REQUEST["uri"]));
            $maximum_images = $type_settings["uploads"][JarisCMS\Security\GetCurrentUserGroup()]["maximum_images"]!=""?$type_settings["uploads"][JarisCMS\Security\GetCurrentUserGroup()]["maximum_images"]:"-1";
            $image_count = count(JarisCMS\Image\GetList($_REQUEST["uri"]));
            
            if($maximum_images == "0")
            {
               JarisCMS\System\AddMessage(t("Image uploads not permitted for this content type."));
            }
            elseif($image_count >= $maximum_images && $maximum_images != "-1")
            {
                JarisCMS\System\AddMessage(t("Maximum image uploads reached."));
            }

            $arguments["uri"] = $_REQUEST["uri"];

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
            
            if($maximum_images == "-1" || $image_count < $maximum_images)
            {
                if(JarisCMS\Group\GetPermission("add_images", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    JarisCMS\System\AddTab(t("Add Image"), "admin/pages/images/add", $arguments, 1);
                }
            }

            if(isset($_REQUEST["btnSave"]) && JarisCMS\Group\GetPermission("edit_images", JarisCMS\Security\GetCurrentUserGroup()))
            {
                $image_count = count($_REQUEST["id"]);

                $saved = true;

                for($i=0; $i<$image_count; $i++)
                {
                    $image_data = JarisCMS\Image\GetData($_REQUEST["id"][$i], $arguments["uri"]);

                    $image_data["description"] = $_REQUEST["description"][$i];
                    $image_data["order"] = $i;

                    if(!JarisCMS\Image\Edit($_REQUEST["id"][$i], $image_data, $arguments["uri"]))
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

                JarisCMS\System\GoToPage("admin/pages/images", $arguments);
            }
            elseif(isset($_REQUEST["btnCancel"]))
            {
                JarisCMS\System\GoToPage("admin/pages/images", $arguments);
            }


            if($images = JarisCMS\Image\GetList($arguments["uri"]))
            {
                $images = JarisCMS\PHPDB\Sort($images, "order");
                
                print "<form class=\"images\" method=\"post\" action=\"" . JarisCMS\URI\PrintURL("admin/pages/images", $arguments) . "\" >";
                print "<input type=\"hidden\" name=\"uri\" value=\"{$arguments['uri']}\" />\n";
                print "<table class=\"images-list\">\n";
                print "<thead>\n";
                print "<tr>\n";
                print "<td>" . t("Order") . "</td>\n";
                print "<td>" . t("Thumbnail") . "</td>\n";
                print "<td>" . t("Description") . "</td>\n";
                
                if(JarisCMS\Group\GetPermission("delete_images", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    print "<td>" . t("Operation") . "</td>\n";
                }
                print "</tr>";
                print "</thead>\n";
                
                print "<tbody>\n";

                foreach($images as $id=>$fields)
                {
                    if($fields['order'] == "")
                    {
                        $fields['order'] = 0;
                    }
                    
                    print "<tr>";
                    
                    print "<td>
                        <a class=\"sort-handle\"></a>
                        <input type=\"hidden\" name=\"id[]\" value=\"$id\" />\n
                        <input type=\"hidden\" name=\"order[]\" value=\"{$fields['order']}\" />
                    </td>";
                    
                    $image_size = array("w"=>"100");
                    
                    print "<td><a title=\"" . t("Click to enlarge") . "\" href=\"" . JarisCMS\URI\PrintURL("image/" . $arguments["uri"] . "/" . $fields["name"]) . "\">" . "<img src=\"" . JarisCMS\URI\PrintURL("image/" . $arguments["uri"] . "/" . $fields["name"], $image_size) . "\" /></a></td>";
                    
                    print "<td><input type=\"text\" name=\"description[]\" value=\"{$fields['description']}\" /></td>";
                    
                    if(JarisCMS\Group\GetPermission("delete_images", JarisCMS\Security\GetCurrentUserGroup()))
                    {
                        print "<td><a href=\"" . JarisCMS\URI\PrintURL("admin/pages/images/delete", array("uri"=>$_REQUEST["uri"], "id"=>$id)) . "\">" . t("Delete") . "</a></td>";
                    }
                    print "</tr>";
                }
                
                print "</tbody>\n";

                print "</table>";
                if(JarisCMS\Group\GetPermission("edit_images", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    print "<input type=\"submit\" name=\"btnSave\" value=\"" . t("Save") . "\" /> &nbsp";
                    print "<input type=\"submit\" name=\"btnCancel\" value=\"" . t("Cancel") . "\" />";
                }
                print "</form>";
            }
            else
            {
                if(JarisCMS\Group\GetPermission("add_images", JarisCMS\Security\GetCurrentUserGroup()))
                {
                    JarisCMS\System\AddMessage(t("No images available click Add Image to create one."));
                }
                else
                {
                    JarisCMS\System\AddMessage(t("No images available."));
                }
            }
        ?>
    field;

    field: is_system
        1
    field;
row;
