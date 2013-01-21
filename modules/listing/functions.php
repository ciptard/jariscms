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

namespace JarisCMS\Module\ContentListing\System
{
    use JarisCMS\URI;
    use JarisCMS\Page;
    use JarisCMS\Block;
    use JarisCMS\Group;
    use JarisCMS\Module;
    use JarisCMS\System;
    use JarisCMS\Security;
    
    function Initialization()
    {
        $uri = $_REQUEST["uri"];

        if($uri && URI\Get() != "admin/pages/add")
        {
            $page_data = Page\GetData($uri);
            
            if($page_data["type"] == "listing")
            {
                switch(URI\Get())
                {
                    case "admin/pages/edit":
                        System\GoToPage(Module\GetPageURI("admin/pages/listing/edit", "listing"), array("uri"=>$uri));
                    default:
                        break;
                }
            }
        }
        else if($_REQUEST["type"])
        {    
            $page = URI\Get();
            
            if($page == "admin/pages/add" && $_REQUEST["type"] == "listing")
            {
                System\GoToPage(Module\GetPageURI("admin/pages/listing/add", "listing"), array("type"=>"listing", "uri"=>$uri));
            }
        }

        if(isset($_REQUEST["id"], $_REQUEST["position"]))
        {    
            if(URI\Get() == "admin/blocks/edit")
            {
                $block_data = Block\GetData($_REQUEST["id"], $_REQUEST["position"]);

                if($block_data["is_listing_block"])
                {
                    System\GoToPage(Module\GetPageURI("admin/blocks/listing/edit", "listing"), array("id"=>$_REQUEST["id"], "position"=>$_REQUEST["position"]));
                }
            }
        }
    }
    
    function GenerateAdminPage(&$sections)
    {
        if(Group\GetPermission("add_blocks", Security\GetCurrentUserGroup()))
        {
            $content = array("title"=>t("Add Listing Block"), "url"=>URI\PrintURL(Module\GetPageURI("admin/blocks/listing/add", "listing")), "description"=>t("Create blocks to display a list a content by a given criteria."));
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

namespace JarisCMS\Module\ContentListing\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Page;
    use JarisCMS\Group;
    use JarisCMS\Module;
    use JarisCMS\System;
    use JarisCMS\Security;
    use JarisCMS\Module\ContentListing;

    
    function MakeContent(&$content, &$content_title, &$content_data)
    {
        if($content_data["type"] == "listing")
        {
            System\AddStyle("modules/listing/styles/lists.css");

            $content_data["filter_types"] = unserialize($content_data["filter_types"]);
            $content_data["filter_categories"] = unserialize($content_data["filter_categories"]);

            $listing_content = ContentListing\Results(URI\Get(), $content_data);

            $content .= $listing_content;

            $content_data["listing_content"] = $listing_content;
        }
    }
    
    function MakeBlocks(&$position, &$page, &$field)
    {
        if($field["is_listing_block"])
        {
            System\AddStyle("modules/listing/styles/lists.css");

            $field["filter_types"] = unserialize($field["filter_types"]);
            $field["filter_categories"] = unserialize($field["filter_categories"]);

            $field["content"] = System\PHPEval($field["pre_content"]);
            $field["content"] .= ContentListing\BlockResults($page, $field);
            $field["content"] .= System\PHPEval($field["sub_content"]);

            $field["is_system"] = true;
        }
    }
    
    function MakeTabsCode(&$tabs_array)
    {
        if(!System\IsSystemPage())
        {
            $page_data = Page\GetData(URI\Get());
            if($page_data["type"] == "listing")
            {     
                if($page_data["author"] == Security\GetCurrentUser() || Security\IsAdminLogged() || Group\GetPermission("edit_all_user_content", Security\GetCurrentUserGroup()))
                {
                    unset($tabs_array[0][t("Edit")]);

                    $new_tabs_array = array();
                    $new_tabs_array[0][t("Edit Listing")] = array("uri"=>Module\GetPageURI("admin/pages/listing/edit", "listing"), "arguments"=>array("uri"=>URI\Get()));
                    $new_tabs_array[0] = array_merge($new_tabs_array[0], $tabs_array[0]);

                    $tabs_array[0] = $new_tabs_array[0];
                }
            }
        }
        else 
        {
            if(URI\Get() == "admin/blocks")
            {
                $tabs_array[0][t("Create Listing Block")] = array("uri"=>Module\GetPageURI("admin/blocks/listing/add", "listing"), "arguments"=>null);
            }
        }
    }
}
?>
