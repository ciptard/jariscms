<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the logout page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("User Logout") ?>
    field;

    field: content

        <?php
            JarisCMS\Security\LogoutUser();
        ?>

        <?php print t("Successfully logged out!") ?>
        <a href="<?php print JarisCMS\URI\PrintURL(""); ?>">
        <?php print t("Click Here") ?>
        </a>
        <?php print t("to go back to home page.") ?>
    field;
    
    field: is_system
        1
    field;
row;
