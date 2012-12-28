<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file The page that serve for restricted areas.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php 
            if($title = JarisCMS\Setting\Get("site_status_title", "main"))
            {
                print t($title);
            }
            else
            {
                print t("Under mantainance");
            } 
        ?>
    field;

    field: content
        <?php
            if($description = JarisCMS\Setting\Get("site_status_description", "main"))
            {
                print t($description);
            }
            else
            {
                print t("The site is down for mantainance, sorry for any inconvenience it may cause you. Try again later.");
            } 
        ?>
    field;

    field: is_system
        1
    field;
row;
