<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module install file
 *
 *Stores the installation script for blog module.
 */

namespace JarisCMS\Module\ControlMenu;

function Install()
{   
    \JarisCMS\System\AddMessage(
        t("Remember to customize your control menu.") . 
        " <a href=\"" . 
        \JarisCMS\URI\PrintURL(
            \JarisCMS\Module\GetPageURI(
                "admin/settings/control-menu", 
                "control_menu"
        )) . "\">" . 
        t("Configure Now") . 
        "</a>"
    );
}

?>