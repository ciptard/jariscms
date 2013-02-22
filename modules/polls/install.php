<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module install file
 *
 *Stores the installation script for module.
 */

namespace JarisCMS\Module\Polls;

use JarisCMS\Type;
use JarisCMS\Block;

function Install()
{
    //To help translation tools
    $string = t("Poll");
    $string = t("A poll where users can vote.");
    $string = t("More Details");
    
    //Create new catalog type
    $new_type["name"] = "Poll";
    $new_type["description"] = "A poll where users can vote.";
    
    Type\Add("poll", $new_type);
    
    //Add polls block
    $block["description"] = "display the most recent poll";
    $block["title"] = "";
    $block["content"] = '';
    $block["order"] = "0";
    $block["display_rule"] = "all_except_listed";
    $block["pages"] = "";
    $block["return"] = "";
    $block["is_system"] = true;
    $block["return"] = "";
    $block["poll_block"] = "1";
    $block["poll_page"] = "";
    
    Block\Add($block, "none");
}

?>