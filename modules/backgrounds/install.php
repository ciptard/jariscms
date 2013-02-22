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

namespace JarisCMS\Module\Backgrounds;

use JarisCMS\URI;
use JarisCMS\System;
use JarisCMS\Module;
use JarisCMS\FileSystem;

function Install()
{
    if(!file_exists("files/backgrounds"))
    {
        FileSystem\MakeDir("files/backgrounds", 0755, true);
    }
    
    System\AddMessage(
        t("Remember to add backgrounds on the Settings section of the Control Center.") . 
        " <a href=\"" . URI\PrintURL(
            Module\GetPageURI(
                "admin/settings/backgrounds", 
                "backgrounds"
            )
        ) . "\">" . 
        t("Add Backgrounds Now") . 
        "</a>"
    );
}

?>
