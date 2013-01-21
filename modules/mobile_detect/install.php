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

namespace JarisCMS\Module\MobileDetect;

function Install()
{
	\JarisCMS\System\AddMessage(
        t("Don't forget to set the mobile and tablet themes.") . " " . 
        "<a href=\"" . 
        \JarisCMS\URI\PrintURL("admin/themes") . 
        "\">" . 
        t("Themes Configuration") . 
        "</a>"
    );
}

?>
