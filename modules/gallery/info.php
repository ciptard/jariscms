<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 * 
 *@file Jaris CMS module info file
 *
 *@note You always need to create an info.php file for your modules.
 */

$module["name"] = "Image Gallery";
$module["description"] = "To create pages that display uploaded images as a gallery.";
$module["namespace"] = "ImageGallery";
$module["version"] = "1.8.1";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

$module["dependencies"][] = "jquery_lightbox";

/**

=Change Log=

Version 1.8.1 - Date 30/03/2012

    * Applied meta title change.

Version 1.8 - Date 23/11/2010

    * Added files tab to edit gallery page

Version 1.7 - Date 22/08/2010

    * Now using image name instead of id
   
**/

?>
