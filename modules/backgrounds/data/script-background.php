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
        Background script
    field;
    
    field: content
        <?php if(isset($_REQUEST["id"])){ ?>
        
        <?php
            $backgrounds_settings = JarisCMS\Setting\GetAll("backgrounds");
            $backgrounds = unserialize($backgrounds_settings["backgrounds"]);
            
            $background = $backgrounds[intval($_REQUEST["id"])];
            
            $images = array();
            $stretch = "false";
            $centerx = "false";
            $centery = "false";
            
            if($background["multi"])
            {
                $images = unserialize($background["images"]);
                foreach($images as $index=>$image)
                {
                    //Get full url
                    $images[$index] = '"'. JarisCMS\URI\PrintURL("files/backgrounds/".$image) . '"';
                }
                
                $images = rtrim(implode(",", $images), ",");
                
                $stretch = $background["stretch"] ? "true" : "false";
                $centerx = $background["centerx"] ? "true" : "false";
                $centery = $background["centery"] ? "true" : "false";
            }
        ?>
        
        <?php if($background["multi"]){ ?>
        $(document).ready(function(){
            $.backstretch(
            [<?php print $images; ?>], 
            {
                fade: <?php print $background["fade_speed"]; ?>, 
                duration: <?php print $background["rotation_speed"]; ?>, 
                stretch: <?php print $stretch; ?>,
                centeredX: <?php print $centerx; ?>, 
                centeredY: <?php print $centery; ?>
            });
        });
        <?php } else { ?>
        $(document).ready(function(){
            backgroundContainer = $('<div class="background background-<?php print intval($_REQUEST["id"]); ?>" />');
            backgroundContainer.css("background", "transparent url(<?php print JarisCMS\URI\PrintURL("files/backgrounds/" . $background["image"]); ?>) <?php print $background["mode"]; ?> <?php print $background["position"]; ?> <?php print intval($background["top"]); ?>px");
            backgroundContainer.css("background-attachment", "<?php print $background["attachment"]; ?>");
            backgroundContainer.css("min-height", $(window).height()+"px");
            $("body").css("background-color", "#<?php print $background["background_color"]; ?>");
            $("body > *").appendTo(backgroundContainer);
            backgroundContainer.appendTo("body");
        });
        <?php } ?>
        
        <?php } ?>
    field;

    field: is_system
        1
    field;
row;
