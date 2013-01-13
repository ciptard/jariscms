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

$module["name"] = "Comments";
$module["description"] = "Add comments functionality to Jaris CMS";
$module["namespace"] = "Comments";
$module["version"] = "1.3";
$module["author"] = "Jefferson González";
$module["email"] = "jgonzalez@jegoyalu.com";
$module["website"] = "http://www.jegoyalu.com";

/**

=Change Log=

Version 1.3 - Date 12/01/2013

    * Renamed user/comments to commments/user due to conflicts with new profiles sytem.

Version 1.2 - Date 20/05/2011

    * Added notifications of new comments to content publishers by activating the Notifications permission.

Version 1.1 - Date 20/09/2010

    * Check if text was sended for the comment before adding it to fix adding of empty comments

Version 1.0 - Date 01/06/2010

    * Initial module development
    
**/

?>
