<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module install file
 *
 *Stores the installation script for jaris realty module.
 */

namespace JarisCMS\Module\ImageGallery;

function Install()
{    
    $string = t("Gallery");
    $string = t("For creating image galleries using lightbox.");
    
    //Create new properties type
    $new_type["name"] = "Gallery";
    $new_type["description"] = "For creating image galleries using lightbox.";
    
    \JarisCMS\Type\Add("gallery", $new_type);
}

?>