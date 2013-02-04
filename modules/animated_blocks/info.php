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

$module["name"] = "Animated Blocks";
$module["description"] = "Various java script animations to post content into blocks in a more interactive way.";
$module["namespace"] = "AnimatedBlocks";
$module["version"] = "1.3";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/**

=Change Log=

Version 1.3 - Date 27/01/2013

    * Enabled support for positioning blocks per theme.

Version 1.2.4 - Date 15/10/2012

    * Fix to animated-blocks/script declaring variables as global instead of local.
  
Version 1.2.3 - Date 22/09/2012

    * Changed effects terminology by settings on various parts of the module.

Version 1.2.2 - Date 21/03/2012

    * Added transparent option to main area options

Version 1.2.1 - Date 20/03/2012

    * Fixed bug on next and previous symbols size not working.

Version 1.2 - Date 08/03/2012

    * Added optional next and previous buttons to traverse the slides.
    * Added "Add Animated Block" link to blocks section on control center
    * Added Pre-content and Sub-content
    * Now the slide description supports php code

Version 1.1 - Date 25/08/2010

    * Fixed theme_block hook function to comply with new jaris cms 4.3.4 changes
    
**/

?>
