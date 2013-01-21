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

namespace JarisCMS\Module\ContentListing;

function Install()
{    
    $string = t("Content Listing");
    $string = t("Page that display a list of content by a given criteria.");
    
    //Create new properties type
    $new_type["name"] = "Content Listing";
    $new_type["description"] = "Page that display a list of content by a given criteria.";
    
    \JarisCMS\Type\Add("listing", $new_type);
}

?>