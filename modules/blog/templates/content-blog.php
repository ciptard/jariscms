<?php
/**
 *Copyright 2008, Jefferson Gonzàlez (JegoYalu.com)
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
                <?php 
                    JarisCMS\System\AddStyle("modules/blog/styles/post.css");
                    
                    $user_data = JarisCMS\User\GetData($content_data["author"]);
                
                    if($user_data["picture"])
                    {
                        $picture = JarisCMS\URI\PrintURL("image/user/" . $content_data["author"]);
                    }
                    else
                    {
                        $picture = JarisCMS\URI\PrintURL("modules/blog/images/no-picture.png");
                    }
                    
                    $user_url = JarisCMS\URI\PrintURL("blog/user/" . $content_data["author"]);
                    
                    print "<div class=\"blog-post-full\">";
                    print "<div class=\"thumbnail\">
                    <a title=\"{$content_data["author"]}\" href=\"" . $user_url . "\"><img alt=\"{$content_data["author"]}\" src=\"$picture\" /></a>
                    </div>\n";
                    
                    print "<div class=\"details\">";
                    print "<div class=\"user\"><span class=\"label\">" . t("Created by:") . "</span> <a href=\"$user_url\">" . $content_data["author"] . "</a></div>\n";
                    print "<div class=\"views\"><span class=\"label\">" . t("Date:") . "</span> " . date("m/d/Y @ g:i:s a", $content_data["created_date"]) . "</div>\n";
                    print "<div class=\"date\"><span class=\"label\">" . t("Views:") . "</span> " . $views . "</div>\n";
                    print "<div style=\"clear: both\"></div>";
                    print "</div>";
                    
                    print "</div>";
                    
                    print $content; 
                ?>
            </td>
            <?php if($right){?><td class="content-right"><?php print $right ?></td><?php } ?>
        </tr>
    </table>

<?php if($footer){?><div class="content-footer"><?php print $footer ?></div><?php } ?>

</div>
