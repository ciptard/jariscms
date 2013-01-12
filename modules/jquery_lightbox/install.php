<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module install file
 *
 *Stores the installation script for jquery lightbox module.
 */

namespace JarisCMS\Module\JQueryLightBox;

function Install()
{
    \JarisCMS\System\AddMessage(t("Remember to set the jquery lightbox configurations to work properly.") . " <a href=\"" . \JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/settings/jquery/lightbox", "jquery_lightbox")) . "\">" . t("Configure Now") . "</a>");
}

?>