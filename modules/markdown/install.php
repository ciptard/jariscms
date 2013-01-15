<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the LICENSE.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module install file
 *
 *Stores the installation script for module.
 */

namespace JarisCMS\Module\Markdown;

function Install()
{
    if(!\JarisCMS\InputFormat\GetData("markdown"))
    {
        $fields = array();
        
        $fields["name"] = "Markdown";
        $fields["description"] = "Automatically generates html code for markdown syntax.";
        $fields["allowed_tags"] = "";
        $fields["parse_url"] = false;
        $fields["parse_email"] = false;
        $fields["parse_line_breaks"] = false;
        $fields["is_system"] = true;
        
        \JarisCMS\InputFormat\Add("markdown", $fields);
    }
}

?>
