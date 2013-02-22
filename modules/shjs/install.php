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

namespace JarisCMS\Module\SHJS;

use JarisCMS\URI;
use JarisCMS\Module;
use JarisCMS\System;

function Install()
{
    System\AddMessage(
        t("Remember to set the syntax highlighting configurations to work properly.") . 
        " <a href=\"" . URI\PrintURL(
            Module\GetPageURI(
                "admin/settings/shjs", 
                "shjs"
            )
        ) . "\">" . 
        t("Configure Now") . 
        "</a>"
    );
}

?>
