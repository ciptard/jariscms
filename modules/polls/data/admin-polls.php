<?php
/**
 *Copyright 2008, Jefferson González (JegoYalu.com)
 *This file is part of Jaris CMS and licensed under the GPL,
 *check the license.txt file for version and details or visit
 *http://www.gnu.org/licenses/gpl.html.
 *
 *@file Database file that stores the content add page.
 */

//For security the file content is skipped from the world eyes :)
exit;
?>

row: 0
    field: title
        <?php print t("Polls") ?>
    field;

    field: content      
        <?php
            JarisCMS\Security\ProtectPage();
            
            $page = 1;
            
            if(isset($_REQUEST["page"]))
            {
                $page = $_REQUEST["page"];
            }
            
            $polls_count = JarisCMS\Module\Polls\Core\SQLite\Count();
            
            print "<b>" . t("Total polls:") ."</b> " .  $polls_count . "<br />";
            
            $polls = JarisCMS\Module\Polls\Core\SQLite\Get($page - 1);
            
            JarisCMS\System\PrintGenericNavigation($polls_count, $page, "admin/polls", "polls", 30);
            
            print "<table class=\"navigation-list\">";
            print "<thead>";
            print "<tr>";
            print "<td>" . t("Date") . "</td>";
            print "<td>" . t("Title") . "</td>";
            print "<td>" . t("Actions") . "</td>";
            print "</tr>";
            print "</thead>";
            
            foreach($polls as $uri)
            {
                $polls_data = JarisCMS\Page\GetData($uri);
                
                print "<tr>";
                
                print "<td>" . date("n/j/o", $polls_data["created_date"]) . "</td>";
                
                print "<td>" . 
                $polls_data["title"] .
                "</td>";
                
                $edit_url = JarisCMS\URI\PrintURL(JarisCMS\Module\GetPageURI("admin/polls/edit", "polls"), array("uri"=>$uri));
                $delete_url = JarisCMS\URI\PrintURL("admin/pages/delete", array("uri"=>$uri));
                
                print "<td>" . 
                "<a href=\"$edit_url\">" . t("Edit") . "</a> " .
                "<a href=\"$delete_url\">" . t("Delete") . "</a>" .                    
                 "</td>";
                
                print "</tr>";
            }
            
            print "</table>";
            
            JarisCMS\System\PrintGenericNavigation($polls_count, $page, "admin/polls", "polls", 30);
        ?>
    field;

    field: is_system
        1
    field;
row;
