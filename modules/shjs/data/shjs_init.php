<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the administration page for whizzywig.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        SHJS Initialization Script
    field;

    field: content
        $(document).ready(function(){
            sh_highlightDocument('<?php print JarisCMS\URI\PrintURL("modules/shjs/shjs/lang/") ?>', '.min.js');
        });
    field;

    field: is_system
        1
    field;
row;
