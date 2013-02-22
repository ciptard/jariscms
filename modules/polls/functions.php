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

namespace JarisCMS\Module\Polls\System
{
    use JarisCMS\URI;
    use JarisCMS\Page;
    use JarisCMS\Module;
    use JarisCMS\System;
    use JarisCMS\Security;
    
    function Initialization()
    {
        $uri = $_REQUEST["uri"];

        if($uri && URI\Get() != "admin/pages/add")
        {
            $page_data = Page\GetData($uri);
            if($page_data["type"] == "poll")
            {
                switch(URI\Get())
                {
                    case "admin/pages/edit":
                        System\GoToPage(Module\GetPageURI("admin/polls/edit", "polls"), array("uri"=>$uri));
                    default:
                        break;
                }
            }
        }
        elseif($_REQUEST["type"])
        {    
            $page = URI\Get();
            if($page == "admin/pages/add" && $_REQUEST["type"] == "poll")
            {
                System\GoToPage(Module\GetPageURI("admin/polls/add", "polls"), array("type"=>"poll", "uri"=>$uri));
            }
        }
    }
    
    function GenerateAdminPage(&$sections)
    {
        if(Security\IsAdminLogged())
        {
            $content[] = array("title"=>t("Add Poll"), "url"=>URI\PrintURL(Module\GetPageURI("admin/polls/add", "polls")), "description"=>t("Create a poll where users can vote."));
            $content[] = array("title"=>t("View All Polls"), "url"=>URI\PrintURL(Module\GetPageURI("admin/polls", "polls")), "description"=>t("View created polls on the system."));

            $new_section[] = array("class"=>"polls", "title"=>t("Polls"), "sub_sections"=>$content);

            $original_sections = $sections;

            $sections = array_merge($new_section, $original_sections);
        }
    }
}

namespace JarisCMS\Module\Polls\Page
{
    use JarisCMS\Page;
    use JarisCMS\Module\Polls;
    
    function Delete(&$page, &$page_path)
    {
        if(Page\GetType($page) == "poll")
        {
            Polls\Core\SQLite\Delete($page);
            Polls\Core\Recent\Delete($page);
        }
    }
}

namespace JarisCMS\Module\Polls\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Page;
    use JarisCMS\Group;
    use JarisCMS\Module;
    use JarisCMS\System;
    use JarisCMS\Security;
    
    function MakeTabsCode(&$tabs_array)
    {
        if(!System\IsSystemPage())
        {
            $page_data = Page\GetData(URI\Get());
            if($page_data["type"] == "poll")
            {
                $tabs_array = array();

                if($page_data["author"] == Security\GetCurrentUser() || Security\IsAdminLogged() || Group\GetPermission("edit_all_user_content", Security\GetCurrentUserGroup()))
                {
                    $tabs_array[0][t("Edit Poll")] = array("uri"=>Module\GetPageURI("admin/polls/edit", "polls"), "arguments"=>array("uri"=>URI\Get()));
                }
            }
        }
    }
    
    function GetContentTemplateFile(&$page, &$type, &$template_path)
    {
        global $theme;

        $default_template = "themes/" . $theme . "/content.php";

        if($type == "poll" && $template_path == $default_template)
        {
            $template_path = "modules/polls/templates/content-poll.php";
        }
    }
}
?>
