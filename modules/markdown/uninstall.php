<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the LICENSE.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module install file
 *
 *Stores the uninstallation script for module.
 */

namespace JarisCMS\Module\Markdown;

function Uninstall()
{
    \JarisCMS\InputFormat\Delete("markdown");
}

?>
