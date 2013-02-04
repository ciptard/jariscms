<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module functions file
 *
 *@note File that stores all hook functions.
 */

namespace JarisCMS\Module\AnimatedBlocks\System
{
    use JarisCMS\URI;
    use JarisCMS\Block;
    use JarisCMS\Group;
    use JarisCMS\Module;
    use JarisCMS\System;
    use JarisCMS\Security;
    
    function Initialization()
    {
        if(isset($_REQUEST["id"], $_REQUEST["position"]))
        {    
            if(URI\Get() == "admin/blocks/edit")
            {
                $block_data = Block\GetData($_REQUEST["id"], $_REQUEST["position"]);

                if($block_data["is_animated_block"])
                {
                    System\GoToPage(Module\GetPageURI("admin/animated-blocks/edit", "animated_blocks"), array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
                }
            }
        }
    }
    
    function GenerateAdminPage(&$sections)
    {
        if(Group\GetPermission("add_blocks", Security\GetCurrentUserGroup()))
        {
            $content = array("title"=>t("Add Animated Block"), "url"=>URI\PrintURL(Module\GetPageURI("admin/animated-blocks/add", "animated_blocks")), "description"=>t("Create blocks with a transition of slides of images or content."));
        }

        if(isset($content))
        {
            foreach($sections as $section_index=>$section_data)
            {
                if($section_data["class"] == "blocks")
                {
                    $sections[$section_index]["sub_sections"][] = $content;
                    break;
                }
            }
        }
    }
}

namespace JarisCMS\Module\AnimatedBlocks\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Block;
    use JarisCMS\Module;
    use JarisCMS\System;
    
    function MakeBlocks(&$position, &$page, &$field)
    {
        if($field["is_animated_block"])
        {
            $field["content"] = "<div></div>";
            $field["is_system"] = true;
        }
    }
    
    function MakeTabsCode(&$tabs_array)
    {
        if(URI\Get() == "admin/blocks")
        {
            $tabs_array[0][t("Create Animated Block")] = array("uri"=>Module\GetPageURI("admin/animated-blocks/add", "animated_blocks"), "arguments"=>null);
        }
    }
    
    function GetBlockTemplateFile(&$position, &$page, &$id, &$template_path)
    {
        global $theme;

        $default_block = "themes/" . $theme . "/block.php";

        if($template_path == $default_block)
        {
            $block_data = Block\GetData($id, $position);

            if($block_data["is_animated_block"])
            {
                System\AddScript("modules/animated_blocks/scripts/cycle/jquery.cycle.all.min.js");
                $template_path = "modules/animated_blocks/templates/block-animated.php";
            }
        }
    }
    
    function GetPageTemplateFile(&$page, &$template_path)
    {
        global $theme;
        
        $default_template = "themes/" . $theme . "/page.php";

        if($template_path == $default_template)
        {
            if(URI\Get() == Module\GetPageURI("animated-blocks/script", "animated_blocks"))
            {
                header("Content-Type: text/css", true);
                $template_path = "modules/animated_blocks/templates/page-empty.php";
            }
            else if(URI\Get() == Module\GetPageURI("animated-blocks/style", "animated_blocks"))
            {
                header("Content-Type: text/css", true);
                $template_path = "modules/animated_blocks/templates/page-empty.php";
            }
        }
    }
    
    function GetContentTemplateFile(&$page, &$type, &$template_path)
    {
        global $theme;
        
        $default_template = "themes/" . $theme . "/content.php";

        if($template_path == $default_template)
        {
            if(URI\Get() == Module\GetPageURI("animated-blocks/script", "animated_blocks"))
            {
                $template_path = "modules/animated_blocks/templates/content-empty.php";
            }
            else if(URI\Get() == Module\GetPageURI("animated-blocks/style", "animated_blocks"))
            {
                $template_path = "modules/animated_blocks/templates/content-empty.php";
            }
        }
    }
}

?>
