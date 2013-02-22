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

namespace JarisCMS\Module\StrippedLayout\Theme
{
    use JarisCMS\URI;
    use JarisCMS\Page;
    
    function GetPageTemplateFile(&$page, &$template_path)
    {
        $page_data = Page\GetData(URI\Get());

        if(isset($_REQUEST["strip"]) && !$page_data["is_system"])
        {
            $template_path = "modules/stripped_layout/templates/page-empty.php";
        }
    }
    
    function GetContentTemplateFile(&$page, &$type, &$template_path)
    {
        global $theme;
        
        $default_template = "themes/" . $theme . "/content.php";

        $page_data = Page\GetData(URI\Get());

        if($template_path == $default_template)
        {
            if(isset($_REQUEST["strip"]) && !$page_data["is_system"])
            {
                $template_path = "modules/stripped_layout/templates/content-empty.php";
            }
        }
    }
}

?>
