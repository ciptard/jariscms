<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Has all the theming functions needed to display a comment.
 */

namespace JarisCMS\Module\Comments;

/**
 *Prepares the comment that is going to be displayed
 *
 *@param $comment_data Array that contains all the comment details
 *@param $page The uri of page where the comment is going to be displayed
 *@param $type The type of page.
 *
 *@return String with the content preformatted.
 */
function Theme($comment_data, $page, $type)
{
    global $theme, $theme_path;
    
    $id = $comment_data["id"]; 
    $user = $comment_data["user"];
    $content = $comment_data["comment_text"];
    $created_timestamp = $comment_data["created_timestamp"];
    $edited_timestamp = $comment_data["edited_timestamp"];
    $reply_to = $comment_data["reply_to"];
    $flags = $comment_data["flags"];
    if($reply_to)
    {
        $reply_to_data = JarisCMS\Module\Comments\GetData($reply_to, $page);
        $reply_to_user = $reply_to_data["user"];
    }
    
    $flag_url = "";
    if(JarisCMS\Group\GetPermission("flag_comments", JarisCMS\Security\GetCurrentUserGroup()))
    {
        $flag_url = "<a id=\"comment-flag-$id-$user\" class=\"comment-flag-link\">" . t("flag") . "</a>";
    }
    
    $reply_url = "";
    if(JarisCMS\Group\GetPermission("add_comments", JarisCMS\Security\GetCurrentUserGroup()))
    {
        $reply_url = "<a id=\"comment-reply-$id-$user\" class=\"comment-reply-link\">" . t("reply") . "</a>";
    }
    
    $delete_url = "";
    if(JarisCMS\Group\GetPermission("delete_comments", JarisCMS\Security\GetCurrentUserGroup()))
    {
        if(JarisCMS\Security\IsAdminLogged() || JarisCMS\Security\GetCurrentUser() == $user)
        {
            $delete_url = "<a id=\"comment-delete-$id-$user\" class=\"comment-delete-link\">" . t("delete") . "</a>";
        }
    }
    
    $comment = "";

    ob_start();
        include(TemplatePath($page, $type));
        $comment .= ob_get_contents();
    ob_end_clean();

    return $comment;
}

/**
 *Search for the best comments template match
 *
 *@param $page The page uri that is going to be displayed.
 *@param $type The type machine name used.
 *
 *@return The page file to be used.
 *    It could be one of the followings in the same precedence:
 *        themes/theme/comments-uri.php
 *        themes/theme/comments.php
 */
function TemplatePath($page, $type)
{
    global $theme;
    $page = str_replace("/", "-", $page);

    $current_page = "themes/" . $theme . "/comments-" . $page . ".php";
    $content_type = "themes/" . $theme . "/comments-" . $type . ".php";
    $default_page = "themes/" . $theme . "/comments.php";
    
    $template_path = "";

    if(file_exists($current_page))
    {
        $template_path = $current_page;
    }
    elseif(file_exists($content_type))
    {
        $template_path = $content_type;
    }
    elseif(file_exists($default_page))
    {
        $template_path = $default_page;
    }
    else
    {
        $template_path = "modules/comments/templates/comments.php";
    }
    
    return $template_path;
}

function PrintAll($page, $type, $page_number=1)
{   
    $comments_content = "<div id=\"comments\">";
    
    $comments = JarisCMS\Module\Comments\GetList($page_number-1, 10, $page);
    
    foreach($comments as $comment_data)
    {
        $comments_content .= Theme($comment_data, $page, $type);
    }
    
    $comments_count = JarisCMS\SQLite\CountColumn("comments", "comments", "id", "", JarisCMS\Module\Comments\GetPagePath($page));
    
    ob_start();
        PrintNavigation($comments_count, $page_number, $page, 10);
        $comments_content .= ob_get_contents(); 
    ob_end_clean();
    
    $comments_content .= "</div>";
    
    return $comments_content; 
}

function PrintPost()
{
    if(JarisCMS\Group\GetPermission("add_comments", JarisCMS\Security\GetCurrentUserGroup()))
    {
        $parameters["name"] = "add-comment";
        $parameters["class"] = "add-comment";
        
        $fields[] = array("type"=>"textarea", "code"=>"style=\"height: 60px\"", "name"=>"comment", "label"=>t("Post a comment:"), "id"=>"comment", "description"=>t("<span id=\"add-comment-left\"></span>&nbsp;characters left"), "required"=>true);
    
        $fields[] = array("type"=>"other", "html_code"=>'<input id="add-comment-submit" value="' . t("Post") . '" type="button" />');
        
        $fields[] = array("type"=>"other", "html_code"=>' <input id="add-comment-reset" value="' . t("Reset") . '" type="button" />');
    
        $fieldset[] = array("fields"=>$fields);
    
        return JarisCMS\Form\Generate($parameters, $fieldset);
    }
    elseif(!JarisCMS\Security\IsUserLogged())
    {
        return "<div class=\"comment-login\">
        <a href=\"" . JarisCMS\URI\PrintURL("admin/user", array("return"=>JarisCMS\URI\Get())) . "\">" . 
        t("Login") . "</a> " . t("or") . " " .
        "<a href=\"" . JarisCMS\URI\PrintURL("register", array("return"=>JarisCMS\URI\Get())) . "\">" . 
        t("Register") . "</a> " . t("to post a comment.") . 
        "</div>";
    }
    
    return "";
}

/**
 *Prints a generaic navigation bar for any kind of results
 * 
 *@param $total_count The total amount of results
 *@param $page The actual page number displaying results
 *@param $uri The uri used on navigation bar links
 *@param $module Optional module name to generate uri
 *@param $amount Optional amount of results to display per page, Default: 30
 */
function PrintNavigation($total_count, $page, $uri, $amount=30)
{
    $page_count = 0;
    $remainder_pages = 0;

    if($total_count <= $amount)
    {
        $page_count = 1;
    }
    else
    {
        $page_count = floor($total_count / $amount);
        $remainder_pages = $total_count % $amount;

        if($remainder_pages > 0)
        {
            $page_count++;
        }
    }

    //In case someone is trying a page out of range or not print if only one page
    if($page > $page_count || $page < 0 || $page_count == 1)
    {
        return false;
    }
    
    print "<div class=\"search-results\">\n";
    print "<div class=\"navigation\">\n";
    if($page != 1)
    {
        $previous_page = JarisCMS\URI\PrintURL($uri, array("page"=>$page - 1));
        $previous_text = t("Previous");
        print "<a class=\"previous\" href=\"javascript:comments_page(" . ($page - 1) . ")\">$previous_text</a>";
    }

    $start_page = $page;
    $end_page = $page + 10;

    for($start_page; $start_page < $end_page && $start_page <= $page_count; $start_page++)
    {
        $text = t($start_page);

        if($start_page > $page || $start_page < $page)
        {
            $url = JarisCMS\URI\PrintURL($uri, array("page"=>$start_page));
            print "<a class=\"page\" href=\"javascript:comments_page(" . $start_page . ")\">$text</a>";
        }
        else
        {
            print "<a class=\"current-page page\">$text</a>";
        }
    }

    if($page < $page_count)
    {
        $next_page = JarisCMS\URI\PrintURL($uri, array("page"=>$page + 1));
        $next_text = t("Next");
        print "<a class=\"next\" href=\"javascript:comments_page(" . ($page + 1) . ")\">$next_text</a>";
    }
    print "</div>\n";
    print "</div>\n";
}
?>
