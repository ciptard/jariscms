<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the site settings management page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        Rating script
    field;

    field: content
        <?php 
            $settings = JarisCMS\Module\Rating\GetSettings($_REQUEST["type"]);
            $rating_data = JarisCMS\Module\Rating\Get($_REQUEST["page"]); 
            $db_user = JarisCMS\User\GeneratePath(JarisCMS\Security\GetCurrentUser(), JarisCMS\Security\GetCurrentUserGroup());
            $db_user = str_replace("data.php", "", $db_user);
        ?>
        //<script>
        <?php if(JarisCMS\Group\GetPermission("rate_content", JarisCMS\Security\GetCurrentUserGroup()) && !is_array(JarisCMS\Module\Rating\Get($_REQUEST["page"], $db_user))) { ?>
        function rating_submit(score)
        {
            $.post(
                "<?php print JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("add/rating", "rating")) ?>", 
                {
                    "point": score,
                    "page": "<?php print $_REQUEST["page"] ?>",
                    "type": "<?php print $_REQUEST["type"] ?>"
                },
                add_rating_message
            );
            
            $("#rating-select").raty.readOnly(true);
        }
        
        function add_rating_message(score)
        {
            var data = "<div class=\"display\"><?php print t("Your rating has been submitted, thank you!") ?></div>";
            
            $("#rating .message").prepend($(data).hide().fadeIn().fadeOut(6000));
            
            $("#rating .content .label").html("Rating:")
            
            $("#rating-select").raty.start(score);
        }
        <?php } ?>
        
        $(document).ready(function(){
           $("#rating-select").raty({
                number: <?php print $settings["number_of_points"] ?>,
                path: '<?php print JarisCMS\URI\PrintURL("modules/rating/scripts/raty/img/") ?>',
                showHalf: true,
                starHalf: '<?php print $settings["half_icon"] ?>',
                start: <?php print JarisCMS\Module\Rating\TotalPoints($rating_data, $settings["number_of_points"]) ?>,
                starOff: '<?php print $settings["off_icon"] ?>',
                starOn: '<?php print $settings["on_icon"] ?>',
                <?php if($settings["hints"]) { ?>
                hintList: <?php print JarisCMS\Module\PrintHints($settings["hints"]) ?>,
                <?php } ?>
                <?php if(JarisCMS\Group\GetPermission("rate_content", JarisCMS\Security\GetCurrentUserGroup()) && !is_array(JarisCMS\Module\Rating\Get($_REQUEST["page"], $db_user))) { ?>
                onClick: rating_submit
                <?php } else{ ?>
                readOnly: true
                <?php } ?>
           });
            
        });
        //</script>
    field;

    field: is_system
        1
    field;
row;
