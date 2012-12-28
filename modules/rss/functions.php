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

namespace JarisCMS\Module\Rss\System
{
    use JarisCMS\URI;
    use JarisCMS\Module;
    use JarisCMS\Setting;
    
    function GetPageMetaTags(&$meta_tags)
    {
        $title = t(Setting\Get("title", "main"));
        $meta_tags .= "<link rel=\"alternate\" title=\"RSS - $title\" href=\"" . URI\PrintURL(Module\GetPageURI("rss", "rss")) . "\" type=\"application/rss+xml\">\n";
    }
}

namespace JarisCMS\Module\Rss\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Module;
    
    function GetPageTemplateFile(&$page, &$template_path)
    {
        $uri = URI\Get();

        if($uri == Module\GetPageURI("rss", "rss"))
        {
            $template_path = "modules/rss/templates/page-empty.php";
        }
    }
    
    function GetContentTemplateFile(&$page, &$type, &$template_path)
    {
        $uri = URI\Get();

        if($uri == Module\GetPageURI("rss", "rss"))
        {
            $template_path = "modules/rss/templates/content-empty.php";
        }
    }
}

?>
