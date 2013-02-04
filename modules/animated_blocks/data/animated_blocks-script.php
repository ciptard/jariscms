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
        Animated Blocks Script
    field;

    field: content
        <?php 
            $block_data = JarisCMS\Block\GetData($_REQUEST["id"], $_REQUEST["position"]);
            $settings = JarisCMS\Module\AnimatedBlocks\GetSettings($block_data);
            $id = "animated-block-{$_REQUEST['position']}-{$_REQUEST['id']}";
            $container = "animated-block-container-{$_REQUEST['position']}-{$_REQUEST['id']}";
            $prev_id = $id . "-prev";
            $next_id = $id . "-next";
            $pager_id = $id . "-pager";
        ?>
        //<script>
            $(document).ready(function(){
                $("#<?php print $id ?>").cycle({
                    fx: '<?php print $settings["effect_name"] ?>',
                    timeout: '<?php print $settings["transition_speed"] ?>',
                    speed: '<?php print $settings["effect_speed"] ?>',
                    pause: '<?php print $settings["hover_pause"] ?>'
                    <?php if($settings["display_navigation"]) { ?>
                    ,prev: '#<?php print $prev_id ?>', 
                    next: '#<?php print $next_id ?>' 
                    <?php } ?>
                    <?php if($settings["display_pager"]) { ?>
                    ,pager: '#<?php print $pager_id ?>'
                    <?php } ?>
                });
                
                var animated_block_container = $("#<?php print $container ?>");
                var animated_block = $("#<?php print $id ?>");
                var animated_pager = $("#<?php print $pager_id ?>");
                var animated_slide = $("#<?php print $id ?> .animated-block-slide");
                var animated_image = $("#<?php print $id ?> .animated-block-image");
                var animated_content = $("#<?php print $id ?> .animated-block-content");
                var animated_container = $("#<?php print $id ?> .animated-block-content-container");
                
                animated_pager.width(animated_block.width());
                animated_content.css("opacity", "<?php print $settings["content_opacity"] ?>");
                
                <?php if($settings["content_position"] == "top"){ ?>
            
                animated_content.css("height", animated_container.height() + "px");
                
                animated_container.css("position", "absolute");
                animated_container.css("top", animated_slide.css("top"));
                
                <?php } else if($settings["content_position"] == "bottom"){ ?>
                
                animated_content.css("position", "absolute");
                animated_content.css("width", animated_slide.width() + "px");
                animated_content.css("height", animated_container.height() + "px");
                animated_content.css("top", (animated_slide.height() - animated_container.height()) + "px");
                
                animated_container.css("position", "absolute");
                animated_container.css("top", animated_content.css("top"));
                
                <?php } else if($settings["content_position"] == "right"){ ?>
                 
                animated_content.css("width", "<?php print $settings["content_width"] ?>");
                animated_content.css("height", animated_slide.height() + "px");
                animated_content.css("position", "absolute");
                animated_content.css("top", animated_slide.css("top"));
                animated_content.css("left", animated_slide.width() - parseInt(animated_content.css("width")) + "px");
                
                animated_container.css("width", animated_content.css("width"));
                animated_container.css("height", animated_content.css("height"));
                animated_container.css("position", animated_content.css("position"));
                animated_container.css("top", animated_content.css("top"));
                animated_container.css("left", animated_content.css("left"));
                
                <?php } else if($settings["content_position"] == "left"){ ?>
                
                animated_content.css("width", "<?php print $settings["content_width"] ?>");
                animated_content.css("height", animated_slide.height() + "px");
                animated_content.css("position", "absolute");
                animated_content.css("top", animated_slide.css("top"));
                
                animated_container.css("width", animated_content.css("width"));
                animated_container.css("height", animated_content.css("height"));
                animated_container.css("position", animated_content.css("position"));
                animated_container.css("top", animated_content.css("top"));
                
                <?php } ?>
                
                
                <?php if(!$settings["image_as_background"]) { ?>
                
                
                <?php if($settings["image_position"] == "top left"){ ?>
            
                animated_image.css("position", "absolute");
                animated_image.css("z-index", "-2");
                animated_image.css("top", "0px");
                
                <?php } else if($settings["image_position"] == "top right"){ ?>
                
                animated_image.css("position", "absolute");
                animated_image.css("z-index", "-2");
                animated_image.css("top", "0px");
                animated_image.css("left", (animated_slide.width() - animated_image.width()) + "px");
                
                <?php } else if($settings["image_position"] == "bottom left"){ ?>
                 
                animated_image.css("position", "absolute");
                animated_image.css("z-index", "-2");
                animated_image.css("top", (animated_slide.height() - animated_image.height()) + "px");
                
                <?php } else if($settings["image_position"] == "bottom right"){ ?>
                
                animated_image.css("position", "absolute");
                animated_image.css("z-index", "-2");
                animated_image.css("top", (animated_slide.height() - animated_image.height()) + "px");
                animated_image.css("left", (animated_slide.width() - animated_image.width()) + "px");
                
                <?php } ?>        
                
                <?php } ?>
                
                
                <?php if($settings["display_navigation"]){ ?>
                animated_block_container.hover(
                    function(){
                        $("#<?php print $prev_id ?>").css("left", animated_block.position().left);
                        $("#<?php print $prev_id ?>").css("top", animated_block.position().top+(animated_block.height()/2)-($("#<?php print $prev_id ?>").height()/2));
                        $("#<?php print $prev_id ?>").css("z-index", 1000);
                        $("#<?php print $prev_id ?>").fadeIn("fast");
                        
                        $("#<?php print $next_id ?>").css("left", animated_block.position().left+(animated_block.width()-$("#<?php print $next_id ?>").width()));
                        $("#<?php print $next_id ?>").css("top", animated_block.position().top+(animated_block.height()/2)-($("#<?php print $next_id ?>").height()/2));
                        $("#<?php print $next_id ?>").css("z-index", 1000);
                        $("#<?php print $next_id ?>").fadeIn("fast");
                    },
                    function(){
                        $("#<?php print $prev_id ?>").fadeOut("fast");
                        $("#<?php print $next_id ?>").fadeOut("fast");
                    }
                );
                <?php } ?>
            });
        //</script>
    field;

    field: is_system
        1
    field;
row;
