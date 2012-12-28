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
        Comments script
    field;

    field: content
        <?php $settings = JarisCMS\Module\Comments\GetSettings($_REQUEST["type"]) ?>
        //<script>
        function comments_page(page)
        {
            $.post(
                "<?php print JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("navigations/comment", "comments")) ?>", 
                {
                    "uri": "<?php print $_REQUEST["page"] ?>",
                    "page": page,
                    "type": "<?php print $_REQUEST["type"] ?>"
                },
                comments_content
            );
        }
        
        function comments_content(data)
        {
            $("#comments").html(data);
            
            $(".comment-flag-link").click(flag_submit);
          
            $(".comment-reply-link").click(reply_show);
          
            $(".comment-delete-link").click(delete_submit);
        }
        
        function comment_submit()
        {
            if($.trim($("#add-comment-comment").val())!= "")
            {
                $.post(
                    "<?php print JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("add/comment", "comments")) ?>", 
                    {
                        "comment": $("#add-comment-comment").val(),
                        "page": "<?php print $_REQUEST["page"] ?>",
                        "type": "<?php print $_REQUEST["type"] ?>"
                    },
                    add_comment
                );
                $(this).attr("disabled", true);
            }
        }
        
        function reply_cancel()
        {
            if($(".reply-input") != undefined)
            {
                $(".reply-input").remove();
            }
        }
        
        function reply_show()
        {
            reply_cancel();
            
            id_elements = $(this).attr(("id")).split("-");
            id = id_elements[2];
            user = id_elements[3];
            
            $id_submit = "reply-" + id + "-" + user;
            
            content_html = '<div class="reply-input">';
            content_html += '<textarea id="reply-comment-text" class="reply-textarea"></textarea>';
            content_html += '<input type="button" id="' + $id_submit + '" class="reply-comment-submit" value="<?php print t("reply") ?>" />';
            content_html += ' <input type="button" class="reply-comment-cancel" value="<?php print t("cancel") ?>" />';
            content_html += ' <span  id="reply-chars-left"></span>&nbsp;<?php print t("characters left") ?>.'
            content_html += '</div>';
            
            $(this).parent().parent().children(".comment-content").prepend($(content_html).hide().fadeIn());
            
            $(".reply-comment-submit").click(reply_submit);
            $(".reply-comment-cancel").click(reply_cancel);
            $("#reply-comment-text").limit('<?php print $settings["maximun_characters"] ?>', '#reply-chars-left');
            $("#reply-comment-text").TextAreaResizer();
        }
        
        function reply_submit()
        {
            if($.trim($("#reply-comment-text").val()) != "")
            {
                id_elements = $(this).attr(("id")).split("-");
                id = id_elements[1];
                user = id_elements[2];
                
                $.post(
                    "<?php print JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("add/comment", "comments")) ?>", 
                    {
                        "comment": $("#reply-comment-text").val(),
                        "page": "<?php print $_REQUEST["page"] ?>",
                        "type": "<?php print $_REQUEST["type"] ?>",
                        "rid": id
                    },
                    add_comment
                );
                
                $(this).attr("disabled", true);
                $(".reply-comment-cancel").attr("disabled", true);
            }
        }
        
        function flag_submit()
        {
            id_elements = $(this).attr(("id")).split("-");
            id = id_elements[2];
            user = id_elements[3];
            
            $.post(
                "<?php print JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("flag/comment", "comments")) ?>", 
                {
                    "id": id,
                    "page": "<?php print $_REQUEST["page"] ?>",
                    "type": "<?php print $_REQUEST["type"] ?>",
                    "user": user
                },
                flag_comment
            );
            
            $(this).unbind("click");
        }
        
        function delete_submit()
        {
            id_elements = $(this).attr(("id")).split("-");
            id = id_elements[2];
            user = id_elements[3];
            
            $.post(
                "<?php print JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("delete/comment", "comments")) ?>", 
                {
                    "id": id,
                    "page": "<?php print $_REQUEST["page"] ?>",
                    "type": "<?php print $_REQUEST["type"] ?>",
                    "user": user
                },
                delete_comment
            );
            
            $(this).unbind("click");
        }
          
        $(document).ready(function(){
          
          $("#add-comment-submit").click(comment_submit);
          
          $("#add-comment-reset").click(function(){$("#add-comment-comment").val("");});
          
          $("#add-comment-comment").limit('<?php print $settings["maximun_characters"] ?>', '#add-comment-left');
          
          $(".comment-flag-link").click(flag_submit);
          
          $(".comment-reply-link").click(reply_show);
          
          $(".comment-delete-link").click(delete_submit);
          
        });
        
        function add_comment(data)
        {
            if($.trim(data) != "")
            {
                reply_cancel();
                
                $("#comments").prepend($(data).hide().fadeIn());
                $("#add-comment-comment").val("");
                $("#add-comment-submit").attr("disabled", false);
                
                $(".comment-flag-link").unbind("click");
                $(".comment-delete-link").unbind("click");
                
                $(".comment-flag-link").click(flag_submit);
                $(".comment-reply-link").click(reply_show);
                $(".comment-delete-link").click(delete_submit);
            }
        }
        
        function flag_comment(data)
        {   
            $("#comment-" + data.toString()).children(".comment-actions").children(".comment-flag-link").fadeOut();
        }
        
        function delete_comment(data)
        {   
            $("#comment-" + data.toString()).fadeOut();
        }
        //</script>
    field;

    field: is_system
        1
    field;
row;
