<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the LICENSE.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module functions file
 *
 *@note File that stores all hook functions.
 */

namespace JarisCMS\Module\Markdown\Theme
{
    use \JarisCMS\Module\Markdown;
    
    function MakeContent(&$content, &$content_title, &$content_data)
    {
        if($content_data["input_format"] == "markdown")
        {
            require_once("modules/markdown/phpmarkdown/markdown.php");

            $content = Markdown($content);
        }
    }
}

?>
