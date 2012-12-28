<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module install file
 *
 *Stores the installation script for whizzywig module.
 */

namespace JarisCMS\Module\Whizzywig;

function Install()
{
    JarisCMS\System\AddMessage(t("Remember to set the whizzywig configurations to work properly.") . " <a href=\"" . JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/whizzywig", "whizzywig")) . "\">" . t("Configure Now") . "</a>");
}

?>