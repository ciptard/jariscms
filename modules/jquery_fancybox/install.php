<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module install file
 *
 *Stores the installation script for jquery fancybox module.
 */

namespace JarisCMS\Module\JQueryFancyBox;

use JarisCMS\URI;
use JarisCMS\System;
use JarisCMS\Module;

function Install()
{
    System\AddMessage(
        t("Remember to set the jquery fancybox configurations to work properly.") . 
        " <a href=\"" . URI\PrintURL(
            Module\GetPageURI(
                "admin/settings/jquery/fancybox", 
                "jquery_fancybox"
            )
        ) . "\">" . t("Configure Now") . "</a>");
}

?>
