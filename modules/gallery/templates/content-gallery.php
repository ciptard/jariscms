<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
*/
?>

<div class="content">

<?php if($header){?><div class="content-header"><?php print $header ?></div><?php } ?>

    <table>
        <tr>
            <?php if($left){?><td class="content-left"><?php print $left ?></td><?php } ?>
            <td class="content">
                <?php if($center){?>
                <div class="content-center">
                    <?php print $center ?>
                </div>
                <?php } ?>
                <?php print $content; ?>
                
                <?php
                    $position = $_REQUEST["position"];
                
                    $images = JarisCMS\PHPDB\Sort($images, "order");
                    $images_per_row = $content_data["images_per_row"];
                    $images_per_page = $content_data["images_per_page"];
                    
                    $image_count = count($images);
                    $pages = ceil($image_count / $images_per_page);
                
                    $images_to_show = array();
                    if(isset($position))
                    {
                        $index = ($position-1) * $images_per_page;
                        $index_max = $index + $images_per_page;
                        $temp_image_array = array();
                        foreach($images as $id=>$fields)
                        {
                            $temp_image_array[] = $id;
                        }
                
                        for($index; $index<$index_max && $index<$image_count; $index++)
                        {
                            $images_to_show[] = $temp_image_array[$index];
                        }
                    }
                    else
                    {
                        $position = 1;
                        $index = 0;
                
                        if(count($images) > 0)
                        {
                            foreach($images as $id=>$fields)
                            {
                                if($index < $images_per_page)
                                {
                                    $images_to_show[] = $id;
                                }
                                else
                                {
                                    break;
                                }
                
                
                            $index++;
                            }
                        }
                        else
                        {
                            print "<h3>" . t("This gallery has no images") . "</h3>";
                        }
                    }
                
                    print "<table id=\"gallery\" width=\"100%\">\n";
                
                    $columna = 1;
                    for($i=0; $i<count($images_to_show); $i++)
                    {
                        if($columna == 1)
                        {
                            print "<tr>\n";
                        }
                        
                        $image_title = "<div class=\"gallery-title\"><a class=\"lightbox\" href=\"" . print_url("image/" . get_uri() . "/" . $images_to_show[$i]) . "\">" . t($images[$images_to_show[$i]]["description"]) . "</a></div>";
                
                        print "<td align=\"center\" style=\"padding-bottom: 10px;\">\n";
                        if($content_data["show_title"] && $content_data["title_position"] == "top")
                        {
                            print $image_title;
                        }
                        print "<a class=\"lightbox\" rel=\"gallery\" title=\" " . t($images[$images_to_show[$i]]["description"]) . "\" href=\"" . print_url("image/" . get_uri() . "/" . $images[$images_to_show[$i]]["name"]) . "\"><img style=\"border: solid #666666 2px\" src=\"" . print_url("image/" . get_uri() . "/" . $images[$images_to_show[$i]]["name"], array("w"=>$content_data["thumbnails_width"],"h"=>$content_data["thumbnails_height"], "ar"=>$content_data["aspect_ratio"], "bg"=>$content_data["background_color"])) . "\" /></a>\n";
                        if($content_data["show_title"] && $content_data["title_position"] == "bottom")
                        {
                            print $image_title;
                        }
                        print "</td>";
                
                        if(($columna == $images_per_row) || (($i+1) == count($images_to_show)))
                        {
                            print "</tr>\n";
                            $columna = 0;
                        }
                
                        $columna++;
                    }
                
                    print "</table>";
                
                    $anterior_enlace = ($position > 1)?print_url(get_uri(), array("position"=>$position-1)):"javascript:void(0)";
                    $despues_enlace = ($pages > 1 && $position != $pages)?print_url(get_uri(), array("position"=>$position+1)):"javascript:void(0)";
                
                    print "<div style=\"text-align: center\">\n";
                    if($anterior_enlace == "javascript:void(0)")
                    {
                        print "&nbsp;";
                    }
                    else
                    {
                        print "<a title=\"" . t("previous") . "\" style=\"float: left;\" href=\"" . $anterior_enlace ."\"><img alt=\"" . t("Previous") . "\" src=\"" . print_url("modules/gallery/images/btn-previous.png") . "\" /></a>";
                    }
                
                    if($despues_enlace == "javascript:void(0)")
                    {
                        print "&nbsp;";
                    }
                    else
                    {
                        print "<a title=\"" . t("next") . "\" style=\"float: right;\" href=\"" . $despues_enlace ."\"><img alt=\"" . t("Next") . "\" src=\"" . print_url("modules/gallery/images/btn-next.png") . "\" /></a>";
                    }
                    print "</div>\n";
                ?>
                
            </td>
            <?php if($right){?><td class="content-right"><?php print $right ?></td><?php } ?>
        </tr>
    </table>

<?php if($footer){?><div class="content-footer"><?php print $footer ?></div><?php } ?>

</div>
