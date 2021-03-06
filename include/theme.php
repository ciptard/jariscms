<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Has all the theming functions needed to process a page.
 */

namespace JarisCMS\Theme;

/**
 * Prepares the content that is going to be displayed
 *
 * @param array $content All the page data content.
 * @param string $page The uri of the page that is going to be displayed.
 *
 * @return string Html content preformatted.
 */
function MakeContent($content, $page)
{
    global $theme, $theme_path, $content_title;

    $formatted_page = "";

    foreach($content as $field)
    {

        $header_data = \JarisCMS\PHPDB\Sort(\JarisCMS\Block\GetList("header", $page), "order");
        $footer_data = \JarisCMS\PHPDB\Sort(\JarisCMS\Block\GetList("footer", $page), "order");
        $left_data = \JarisCMS\PHPDB\Sort(\JarisCMS\Block\GetList("left", $page), "order");
        $right_data = \JarisCMS\PHPDB\Sort(\JarisCMS\Block\GetList("right", $page), "order");
        $center_data = \JarisCMS\PHPDB\Sort(\JarisCMS\Block\GetList("center", $page), "order");

        $header = MakeContentBlocks($header_data, "header", $page, $field["type"]);
        $footer = MakeContentBlocks($footer_data, "footer", $page, $field["type"]);
        $left = MakeContentBlocks($left_data, "left", $page, $field["type"]);
        $right = MakeContentBlocks($right_data, "right", $page, $field["type"]);
        $center = MakeContentBlocks($center_data, "center", $page, $field["type"]);

        $images = \JarisCMS\Image\GetList($page);
        $files = \JarisCMS\File\GetList($page);
        $title = \JarisCMS\Search\StripHTMLTags($field["is_system"]?\JarisCMS\System\PHPEval($field["title"]):$field["title"]);
        $content_title = $title;
        $content_data = $field;
        $views = \JarisCMS\Page\CountView($page);
        $content_data["views"] = $views;
        
        $content = "";
        if($field["is_system"])
        {
            $content =  \JarisCMS\System\PHPEval($field['content']);
        }
        else
        {
            $content = \JarisCMS\InputFormat\FilterData($field['content'], $field["input_format"]);
        }
        
        $content_data["filtered_content"] = $content;
        
        \JarisCMS\Module\Hook("Theme", "MakeContent", $content, $content_title, $content_data);

        ob_start();
            include(GetContentTemplateFile($page, trim($field["type"])));

            $formatted_page .= ob_get_contents();
        ob_end_clean();
    }

    return $formatted_page;
}

/**
 * Prepares the blocks that are going to be displayed.
 *
 * @param array $arrData An array of blocks generated by \JarisCMS\PHPDB\Parse function.
 * @param string $position The position of the block: left, right, center, header or footer.
 * @param string $page The uri of the page that is going to be displayed.
 *
 * @return String with all the data preformatted based on the corresponding
 *         block template.
 */
function MakeBlocks($arrData, $position , $page)
{
    global $theme;

    $block = "";

    if($arrData)
    {
        foreach($arrData as $id=>$field)
        {
            if(trim($field["content"]) != "")
            {
                //Unserialize groups string to array
                $field["groups"] = unserialize($field["groups"]);
                
                if($field["return"])
                {
                    //Execute the code on the block return field to know if the block should be displayed or not
                    $return = \JarisCMS\System\PHPEval($field["return"]);
    
                    //Skip the block on "false" string
                    if($return == "false")
                    {
                        continue;
                    }
                }
    
                if(\JarisCMS\Block\UserGroupHasAccessTo($field))
                {
                    if(\JarisCMS\Block\PageHasAccess($field, $page))
                    {
                        \JarisCMS\Module\Hook("Theme", "MakeBlocks", $position, $page, $field);
                        
                        $content = "";
                        
                        if(\JarisCMS\Group\GetPermission("view_blocks", \JarisCMS\Security\GetCurrentUserGroup()) && \JarisCMS\Group\GetPermission("edit_blocks", \JarisCMS\Security\GetCurrentUserGroup()))
                        {
                            \JarisCMS\System\AddScript("scripts/admin/blocks.js");
                            
                            $url = \JarisCMS\URI\PrintURL(
                                "admin/blocks/edit", array(
                                    "id"=>$field["original_id"]?$field["original_id"]:$id, 
                                    "position"=>$field["original_position"]?$field["original_position"]:$position
                                )
                            );
                            
                            $content = "<a class=\"instant-block-edit\" href=\"$url\">" . t("edit") . "</a>";
                            $content .= "<div style=\"clear: both\"></div>";
                        }
                        
                        if($field["is_system"])
                        {
                            $content .=  \JarisCMS\System\PHPEval($field['content']);
                        }
                        else
                        {
                            $content .= \JarisCMS\InputFormat\FilterData($field['content'], $field["input_format"]);
                        }
                        
                        //Dont show block if content is empty
                        if(trim($content) == "")
                        {
                            continue;
                        }
    
                        ob_start();
                            $title = t($field["title"]);
                            include(GetBlockTemplateFile($position, $page, $id));
                            $block .= ob_get_contents();
                        ob_end_clean();
                    }
                }
            }
        }
    }

    return $block;
}

/**
 * Prepares the content blocks that are going to be displayed.
 *
 * @param array $arrData An array of blocks generated by \JarisCMS\PHPDB\Parse function.
 * @param string $position The position of the block: left, right, center, header or footer.
 * @param string $page The page uri where the block is going to be displayed.
 * @param string $page_type The page type to retrieve appropiate template.
 *
 * @return String with all the data preformatted based on the corresponding
 *         block template.
 */
function MakeContentBlocks($arrData, $position, $page, $page_type)
{
    global $theme;

    $block = "";

    if($arrData)
    {
        foreach($arrData as $id=>$field)
        {
            //Unserialize groups string to array
            $field["groups"] = unserialize($field["groups"]);
            
            if($field["return"])
            {
                //Execute the code on the block return field to know if the block should be displayed or not
                $return =  \JarisCMS\System\PHPEval($field["return"]);

                //Skip the block on "false" string
                if($return == "false")
                {
                    continue;
                }
            }

            if(\JarisCMS\Block\UserGroupHasAccessTo($field))
            {
                if(\JarisCMS\Block\PageHasAccess($field, $page))
                {
                    $post = false;
                    $content = "";
                    $image = "";
                    $image_path = "";
                    $post_title = "";
                    $post_title_plain = "";
                    $view_more = "";
                    $view_url = "";
                    
                    if(\JarisCMS\Group\GetPermission("view_content_blocks", \JarisCMS\Security\GetCurrentUserGroup()) && \JarisCMS\Group\GetPermission("edit_content_blocks", \JarisCMS\Security\GetCurrentUserGroup()))
                    {
                        \JarisCMS\System\AddScript("scripts/admin/blocks.js");
                        
                        $url = \JarisCMS\URI\PrintURL("admin/pages/blocks/edit", array("uri"=>$page, "id"=>$id, "position"=>$position));
                        $content = "<a class=\"instant-content-block-edit\" href=\"$url\">" . t("edit") . "</a>";
                        $content .= "<div style=\"clear: both\"></div>";
                    }
                    
                    if($field["post_block"] && $field["uri"])
                    {
                        $post_fields = \JarisCMS\Block\GeneratePostContent($field["uri"], $page);
                        
                        $post = true;
                        $content .= $post_fields["content"];
                        $image = $post_fields["image"];
                        $image_path = $post_fields["image_path"];
                        $post_title = $post_fields["post_title"];
                        $post_title_plain = $post_fields["post_title_plain"];
                        $view_more = $post_fields["view_more"];
                        $view_url = $post_fields["view_url"];
                    }
                    else if($field["is_system"])
                    {
                        $content .=  \JarisCMS\System\PHPEval($field['content']);
                    }
                    else
                    {
                        $content .= \JarisCMS\InputFormat\FilterData($field['content'], $field["input_format"]);
                    }
                    
                    //Dont show block if content is empty
                    if(trim($content) == "" && !$field["post_block"])
                    {
                        continue;
                    }

                    ob_start();
                        $title = t($field["title"]);
                        include(GetContentBlockTemplateFile($position, $page, $page_type, $id));
                        $block .= ob_get_contents();
                    ob_end_clean();
                }
            }
        }
    }

    return $block;
}


/**
 * Prepares the primary links that are going to be displayed on the page.
 *
 * @param array $arrLinks An array of links generated by \JarisCMS\PHPDB\Parse function.
 * @param string $menu_name The machine name of a menu used for css class.
 *
 * @return string All the links preformatted.
 */
function MakeLinks($arrLinks, $menu_name)
{
    $position = 1;
    $count_links = count($arrLinks);

    if($count_links > 0)
    {
        $links = "<ul class=\"menu $menu_name\">";
    
        foreach($arrLinks as $link)
        {
            $list_class = "";
    
            if($position == 1)
            {
                $list_class = " class=\"first l$position\"";
            }
            elseif($position == $count_links)
            {
                $list_class = " class=\"last l$position\"";
            }
            else
            {
                $list_class = " class=\"l$position\"";
            }
    
            //Translate the title and description using the strings.php file if available.
            $link['title'] = t($link['title']);
            $link['description'] = t($link['description']);
            
            $active = \JarisCMS\URI\Get() == $link["url"]?"class=\"active\"":"";
            
            if(isset($link["target"]))
            {
                $target = "target=\"{$link['target']}\"";
            }
    
            $links .= "<li{$list_class}><span><a $active $target title=\"{$link['description']}\" href=\"" . \JarisCMS\URI\PrintURL($link['url']) . "\">" . $link['title'] . "</a></span>";
    
            if($link["expanded"] || $link['url'] == \JarisCMS\URI\Get())
            {
                $links .= MakeLinks($link["sub_items"], $menu_name . "-sub-menu");
            }
            
            $links .= "</li>\n";
    
            $position++;
        }
    
        $links .= "</ul>";
    }
    
    return $links;
}

/**
 * Generate the html code to insert system styles on pages.
 *
 * @param array $styles An array of style files.
 * 
 * @return string Html code for the head section of document.
 */
function MakeCSSLinks($styles)
{
    global $theme;
    
    $styles_code = "";
    $theme_dir = "themes/" . $theme;
    $style_dir = $theme_dir . "/css";
    $style_files = array();

    $exclude_list = array(".", "..");
    
    $theme_files  = array_diff( scandir($theme_dir), $exclude_list );
    
    if(is_dir($style_dir))
        $style_files  = array_diff( scandir($style_dir), $exclude_list );
    
    $files = array_merge($theme_files, $style_files);

    foreach($files as $file)
    {
        $file_path = "";

        if( is_file("$theme_dir/$file") )
        {
            $file_path = "$theme_dir/$file";
        }
        elseif( is_file("$style_dir/$file") )
        {
            $file_path = "$style_dir/$file";
        }

        $file_array = explode( ".", $file_path );
        $extension  = $file_array[count($file_array)-1];

        if( $extension == "css" )
        {
            $styles[] = \JarisCMS\URI\PrintURL("$file_path");
        }
    }
    
    if(\JarisCMS\System\GetUserBrowser() == "ie")
    {
        if(file_exists($theme_dir."/"."ie"))
        {
            if(file_exists("$theme_dir/ie/all.css"))
            {
                $styles[] = "$theme_dir/ie/all.css";
            }
            
            // Load specific css file for current ie version if available
            preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $matches);
            $version = floor($matches[1]);
            
            if(file_exists("$theme_dir/ie/$version.css"))
            {
                $styles[] = "$theme_dir/ie/$version.css";
            }

        }
    }

    if(count($styles) > 0)
    {
        foreach($styles as $file)
        {
            $styles_code .= "<link href=\"$file\" rel=\"stylesheet\" type=\"text/css\" media=\"all\" />\n";
        }
    }
    
    \JarisCMS\Module\Hook("Theme", "MakeCSSLinks", $styles, $styles_code);

    return $styles_code;
}

/**
 * Generate the html code to insert system java scripts on pages.
 *
 * @param array $scripts An array of scripts files.
 *
 * @return string Html code for the head section of document.
 */
function MakeJSLinks($scripts)
{
    global $theme;
    
    $scripts_code = "";
    $theme_dir = "themes/" . $theme;
    $js_dir = $theme_dir . "/js";
    $js_files = array();
    
    $exclude_list = array(".", "..");
    
    if(is_dir($js_dir))
        $js_files  = array_diff( scandir($js_dir), $exclude_list );
    
    foreach($js_files as $file)
    {
        $file_path = "";

        if( is_file("$js_dir/$file") )
        {
            $file_path = "$js_dir/$file";
        }

        $file_array = explode( ".", $file_path );
        $extension  = $file_array[count($file_array)-1];

        if( $extension == "js" )
        {
            $scripts[] = \JarisCMS\URI\PrintURL("$file_path");
        }
    }

    if(count($scripts) > 0)
    {
        foreach($scripts as $file)
        {
            $scripts_code .= "<script type=\"text/javascript\" src=\"$file\"></script>\n";
        }
    }
    
    \JarisCMS\Module\Hook("Theme", "MakeJSLinks", $scripts, $scripts_code);

    return $scripts_code;
}

/**
 * Generate the html code for the tabs.
 *
 * @param array $tabs_array Tabs in the format: array["tab_name"] = "url"
 *
 * @return Html code ready to render or empty string.
 */
function MakeTabsCode($tabs_array)
{
    //Call MakeTabsCode hook before proccessing the array
    \JarisCMS\Module\Hook("Theme", "MakeTabsCode", $tabs_array);
    
    $tabs = "";

    if(count($tabs_array) > 0)
    {
        foreach($tabs_array as $position=>$fields)
        {
            $tabs .= "<ul class=\"tabs tabs-$position\">\n";
            
            $total_tabs = count($fields);
            $index = 0;
    
            if(is_array($fields))
            {
                foreach($fields as $name=>$uri)
                {
                    $list_class = "";
                    if($index == 0)
                    {
                        $list_class = " class=\"first\" ";
                    }
                    else if($index+1 == $total_tabs)
                    {
                        $list_class = " class=\"last\" ";
                    }
                    
                    $url = \JarisCMS\URI\PrintURL($uri['uri'], $uri['arguments']);
                    
                    if($uri["uri"] == \JarisCMS\URI\Get())
                    {
                        $tabs .= "\t<li{$list_class}><span><a class=\"selected\" href=\"$url\">$name</a></span></li>\n";
                    }
                    else
                    {
                        $tabs .= "\t<li{$list_class}><span><a href=\"$url\">$name</a></span></li>\n";
                    }
                }
            }
    
            $tabs .= "</ul>\n";
            
            $tabs .= "<div class=\"clear tabs-clear\"></div>\n";
        }
    }

    return $tabs;
}

/**
 * Generates the html code for the messages.
 *
 * @return string html code ready to render or empty string.
 */
function MakeMessagesCode()
{
    if(isset($_SESSION["messages"]))
    {
        $messages_array = $_SESSION["messages"];
        unset($_SESSION["messages"]);
    }
    else
    {
        $messages_array = array();
    }

    $messages = "";

    $marker = "";
    $separator = "";
    if(count($messages_array) > 1)
    {
        $marker = "* ";
        $separator = "<br />\n";
    }

    foreach($messages_array as $message)
    {

        $messages .= $marker;

        if($message["type"] == "error")
        {
            $messages .= "<span class=\"error\">\n" . t("error:") . " ";
        }

        $messages .= $message["text"] . $separator . "\n";

        if($message["type"] == "error")
        {
            $messages .= "</span>\n";
        }
    }

    return $messages;
}

/**
 * Final function on the theme system that procceses all the data and displays the page.
 *
 * @param string $page The page uri that is going to be displayed.
 * @param string $content The html content output by MakeContent.
 * @param string $left The left block of the page proccesed by get_block.
 * @param string $center The center block of the page proccesed by get_block.
 * @param string $right The right block of the page proccesed by get_block.
 * @param string $header The header block of the page proccesed by get_block.
 * @param string $footer The footer block of the page proccesed by get_block.
 *
 * @return string The whole html output of the page.
 */
function Display($page, $content, $left, $center, $right, $header, $footer)
{
    global $title, $primary_links, $secondary_links, $base_url, $theme,
           $theme_path, $slogan, $footer_message, $content_title, $tabs_list;

    $site_title = \JarisCMS\Setting\Get("title", "main");
    $footer_message = \JarisCMS\System\PHPEval($footer_message);
    $slogan = \JarisCMS\System\PHPEval($slogan);
    $meta = \JarisCMS\System\GetPageMetaTags();
    //$breadcrumb = \JarisCMS\System\PrintBreadcrumb();
    $tabs = MakeTabsCode($tabs_list);
    $messages = MakeMessagesCode();
    $styles = MakeCSSLinks(\JarisCMS\System\GetStyles());
    $scripts = MakeJSLinks(\JarisCMS\System\GetScripts());
    $header_info = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
    
    //Call Display hook before printing the page
    \JarisCMS\Module\Hook("Theme", "Display", $page);

    $html = "";
    
    ob_start();
        //This is a file that where user can create custom code for the template
        if(file_exists("themes/" . $theme . "/functions.php"))
        {
            include("themes/" . $theme . "/functions.php");
        }

        include(GetPageTemplateFile($page));
        
        $html = ob_get_contents();
    ob_end_clean();
    
    return $html;
}

/**
 * Search for the best block template match
 *
 * @param string $position The position of the block: left, right, center, header or footer
 * @param string $page The page uri where the block is going to be displayed.
 * @param integer $id The current id of the block
 *
 * @return string The block file to be used.
 *     It could be one of the followings in the same precedence:
 *        themes/theme/block-page.php
 *        themes/theme/block-position.php
 *        themes/theme/block.php
 */
function GetBlockTemplateFile($position, $page, $id)
{
    global $theme;
    $page = str_replace("/", "-", $page);

    $current_id = "themes/" . $theme . "/block-" . $position . "-" . $id . ".php";
    $current_page = "themes/" . $theme . "/block-" . $page . ".php";
    $position_page = "themes/" . $theme . "/block-" . $position . ".php";
    $default_block = "themes/" . $theme . "/block.php";
    
    $template_path = "";

    if(file_exists($current_id))
    {
        $template_path = $current_id;
    }
    else if(file_exists($current_page))
    {
        $template_path = $current_page;
    }
    else if(file_exists($position_page))
    {
        $template_path = $position_page;
    }
    else
    {
        $template_path = $default_block;
    }
    
    if($id == "")
    {
        $id = "0";
    }
    
    //Call GetBlockTemplateFile hook before returning the template to use
    \JarisCMS\Module\Hook("Theme", "GetBlockTemplateFile", $position, $page, $id, $template_path);
    
    return $template_path;
}

/**
 * Search for the best content block template match
 *
 * @param string $position The position of the block: left, right, center, header or footer
 * @param string $page The page uri where the block is going to be displayed.
 * @param string $page_type The page type.
 * @param integer $id The current id of the block
 *
 * @return string The block file to be used.
 *    It could be one of the followings in the same precedence:
 *        themes/theme/content-block-page.php
 *        themes/theme/content-block-position.php
 *        themes/theme/content-block.php
 */
function GetContentBlockTemplateFile($position, $page, $page_type, $id)
{
    global $theme;
    $page = str_replace("/", "-", $page);

    $current_id = "themes/" . $theme . "/block-content-" . $position . "-" . $id . ".php";
    $current_page_position = "themes/" . $theme . "/block-content-" . $page . "-" . $position . ".php";
    $current_page = "themes/" . $theme . "/block-content-" . $page . ".php";
    $current_page_type = "themes/" . $theme . "/block-content-" . $page_type . ".php";
    $position_page = "themes/" . $theme . "/block-content-" . $position . ".php";
    $default_block = "themes/" . $theme . "/block-content.php";

    $template_path = "";

    if(file_exists($current_id))
    {
        $template_path = $current_id;
    }
    else if(file_exists($current_page_position))
    {
        $template_path = $current_page_position;
    }
    elseif(file_exists($current_page))
    {
        $template_path = $current_page;
    }
    elseif(file_exists($current_page_type))
    {
        $template_path = $current_page_type;
    }
    else if(file_exists($position_page))
    {
        $template_path = $position_page;
    }
    else
    {
        $template_path = $default_block;
    }
    
    //Call GetContentBlockTemplateFile hook before returning the template to use
    \JarisCMS\Module\Hook("Theme", "GetContentBlockTemplateFile", $position, $page, $template_path);
    
    return $template_path;
}

/**
 * Search for the best page template match.
 *
 * @param string $page The page uri.
 *
 * @return string The page file to be used.
 *    It could be one of the followings in the same precedence:
 *        themes/theme/page-uri.php
 *        themes/theme/page.php
 */
function GetPageTemplateFile($page)
{
    global $theme;
    $page = str_replace("/", "-", $page);
    $segments = explode("-", $page);
    
    $one_less_section = "";
    
    if(count($segments) > 1)
    {
        for($i=0; $i<(count($segments)-1); $i++)
        {
            $one_less_section .= $segments[$i] . "-";
        }
    }

    $globa_sections_page = "themes/" . $theme . "/page-" . $one_less_section . ".php";
    $current_page = "themes/" . $theme . "/page-" . $page . ".php";
    $default_page = "themes/" . $theme . "/page.php";
    
    $template_path = "";

    if(file_exists($current_page))
    {
        $template_path = $current_page;
    }
    else if($one_less_section && file_exists($globa_sections_page))
    {
        $template_path = $globa_sections_page;
    }
    else
    {
        $template_path = $default_page;
    }
    
    //Call GetPageTemplateFile hook before returning the template to use
    \JarisCMS\Module\Hook("Theme", "GetPageTemplateFile", $page, $template_path);
    
    return $template_path;
}

/**
 * Search for the best content template match
 *
 * @param string $page The page uri that is going to be displayed.
 * @param string $type The page type machine name.
 *
 * @return string The page file to be used.
 *    It could be one of the followings in the same precedence:
 *        themes/theme/content-uri.php
 *        themes/theme/content-type.php
 *        themes/theme/content.php
 */
function GetContentTemplateFile($page, $type)
{
    global $theme;
    $page = str_replace("/", "-", $page);

    $current_page = "themes/" . $theme . "/content-" . $page . ".php";
    $content_type = "themes/" . $theme . "/content-" . $type . ".php";
    $default_page = "themes/" . $theme . "/content.php";
    
    $template_path = "";

    if(file_exists($current_page))
    {
        $template_path = $current_page;
    }
    elseif(file_exists($content_type))
    {
        $template_path = $content_type;
    }
    else
    {
        $template_path = $default_page;
    }
    
    //Call GetContentTemplateFile hook before returning the template to use
    \JarisCMS\Module\Hook("Theme", "GetContentTemplateFile", $page, $type, $template_path);
    
    return $template_path;
}

/**
 * Search for the best user profile template match
 *
 * @param string $group The users group.
 * @param string $username The users system username.
 *
 * @return string The user profile template file to be used.
 *    It could be one of the followings in the same precedence:
 *        themes/theme/user-profile-username-username.php
 *        themes/theme/user-profile-group.php
 *        themes/theme/user-profile.php
 */
function GetUserProfileTemplateFile($group, $username)
{
    global $theme;

    $username_profile = "themes/" . $theme . "/user-profile-username-" . $username . ".php";
    $group_profile = "themes/" . $theme . "/user-profile-" . $group . ".php";
    $default_template = "themes/" . $theme . "/user-profile.php";
    
    $template_path = "";

    if(file_exists($username_profile))
    {
        $template_path = $username_profile;
    }
    elseif(file_exists($group_profile))
    {
        $template_path = $group_profile;
    }
    else
    {
        $template_path = $default_template;
    }
    
    //Call GetContentTemplateFile hook before returning the template to use
    \JarisCMS\Module\Hook("Theme", "GetUserProfileTemplateFile", $group, $username, $template_path);
    
    return $template_path;
}

/**
 * Search for the best search template match
 *
 * @param string $page The uri of the search page.
 * @param string $results_type The type of results displayed.
 * @param string $template_type The type of template to get, can be: result, header, footer.
 *
 * @return string The block file to be used or false if no template was found.
 *    It could be one of the followings in the same precedence:
 *        themes/theme/search-result-page.php
 *        themes/theme/search-result-type.php
 *        themes/theme/content-block.php
 */
function GetSearchTemplateFile($page, $results_type="all", $template_type="result")
{
    global $theme;
    $page = str_replace("/", "-", $page);

    $current_template = "themes/" . $theme . "/search-$template_type.php";
    $current_page = "themes/" . $theme . "/search-$template_type-" . $page . ".php";
    $current_results_type = "themes/" . $theme . "/search-$template_type-" . $results_type . ".php";

    $template_path = "";

    if(file_exists($current_template))
    {
        $template_path = $current_template;
    }
    elseif(file_exists($current_page))
    {
        $template_path = $current_page;
    }
    elseif(file_exists($current_results_type))
    {
        $template_path = $current_results_type;
    }
    else
    {
        $template_path = false;
    }
    
    //Call GetSearchTemplateFile hook before returning the template to use
    \JarisCMS\Module\Hook("Theme", "GetSearchTemplateFile", $page, $results_type, $template_type, $template_path);
    
    return $template_path;
}
?>
