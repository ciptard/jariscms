<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the deactivated blocks of the page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>


row: 0

    field: description
        primary menu
    field;

    field: title
        Primary menu
    field;

    field: content
        <?php
            print JarisCMS\Theme\MakeLinks(JarisCMS\PHPDB\Sort(JarisCMS\Menu\GetSubItems("primary"),"order"), "primary");
        ?>
    field;

    field: order
        0
    field;
    
    field: display_rule
        all_except_listed
    field;

    field: pages
    field;

    field: return
    field;
    
    field: is_system
        1
    field;
    
    field: menu_name
        primary
    field;

row;


row: 1
    
    field: description
        secondary menu
    field;
    
    field: title
        Secondary menu
    field;

    field: content
        <?php
            print JarisCMS\Theme\MakeLinks(JarisCMS\PHPDB\Sort(JarisCMS\Menu\GetSubItems("secondary"),"order"), "secondary");
           ?>
    field;

    field: display_rule
        all_except_listed
    field;
    
    field: pages
    field;

    field: return
    field;

    field: order
        1
    field;
    
    field: is_system
        1
    field;
    
    field: menu_name
        secondary
    field;

row;
