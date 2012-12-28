<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content delete page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Delete Flag") ?>
    field;
    field: content
        <?php
            JarisCMS\Security\ProtectPage("manage_comments_flags");

            $id = $_REQUEST["id"];
            $page = $_REQUEST["page"];
            $user = $_REQUEST["user"];
            
            JarisCMS\Module\Comments\Delete($id, $page, $user);
            JarisCMS\System\AddMessage(t("Comment successfully deleted."));
            JarisCMS\System\GoToPage(JarisCMS\Module\GetPageURI("admin/comments/flags", "comments"));
        ?>
    field;

    field: is_system
        1
    field;
row;
