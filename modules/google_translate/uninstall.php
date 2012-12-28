<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module uninstall file
 *
 *Stores the uninstall script for ecommerce module.
 */

namespace JarisCMS\Module\GoogleTranslate;

function Uninstall()
{	
	//Remove shopping cart block
	JarisCMS\Block\DeleteByField("block_name", "google_translate_block");
}

?>