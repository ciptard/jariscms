<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module install file
 *
 *Stores the installation script for rss module.
 */

namespace JarisCMS\Module\Rss;

function Install()
{
	JarisCMS\System\AddMessage(t("You can use the rss selecter tool to generate rss by content type.") . " <a href=\"" . JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("rss/selector", "rss")) . "\">" . t("Goto the Rss Selector Page") . "</a>");
}

?>
