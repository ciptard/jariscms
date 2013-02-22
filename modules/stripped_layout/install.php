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

namespace JarisCMS\Module\StrippedLayout;

use JarisCMS\System;

function Install()
{
    System\AddMessage(
        t("To start using the functionality this module offers just pass strip=1 to any section. Example http://domain.com/section?strip=1")
    );
}

?>
